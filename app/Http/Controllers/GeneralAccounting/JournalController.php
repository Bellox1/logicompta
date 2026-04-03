<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\Account;
use App\Models\GeneralAccounting\Journal;
use App\Models\GeneralAccounting\JournalEntry;
use App\Models\GeneralAccounting\JournalEntryLine;
use App\Models\SousCompte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    /**
     * Liste des écritures avec pagination et totaux globaux
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup')
                ->with('warning', 'Veuillez configurer votre entreprise pour accéder au journal.');
        }

        // Grâce au trait BelongsToEntreprise, le scope entreprise est appliqué automatiquement.
        // On utilise withTrashed() pour que les écritures liées à des comptes supprimés restent visibles.
        $query = JournalEntry::query()->with([
            'journal', 
            'lines.sousCompte' => function($q) { $q->withTrashed(); },
            'lines.sousCompte.account'
        ]);
        
        if ($request->query('show_archived') == '1') {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }

        if ($request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }

        $sort = $request->query('sort', 'date');
        $order = $request->query('order', 'desc');
        
        $query->orderBy($sort, $order);
        if ($sort !== 'id') {
            $query->orderBy('id', 'desc');
        }

        $entries = $query->paginate(50)->withQueryString();

        // Totaux globaux (toute la période filtrée, pas seulement la page)
        // Les totaux sont également filtrés par le scope global du modèle JournalEntry (automatiquement appliqué via la jointure)
        $totalsQuery = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id');

        if ($request->query('show_archived') == '1') {
            $totalsQuery->where('journal_entries.is_archived', true);
        } else {
            $totalsQuery->where('journal_entries.is_archived', false);
        }
        if ($request->start_date) {
            $totalsQuery->where('journal_entries.date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $totalsQuery->where('journal_entries.date', '<=', $request->end_date);
        }

        $globalTotalDebit  = $totalsQuery->sum('journal_entry_lines.debit');
        $globalTotalCredit = $totalsQuery->sum('journal_entry_lines.credit');

        return view('accounting.journal.index', compact('entries', 'globalTotalDebit', 'globalTotalCredit'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        
        // On récupère uniquement les journaux de l'entreprise
        $journals = Journal::all();

        $accounts = SousCompte::where('entreprise_id', $user->entreprise_id)
            ->with('account')
            ->orderBy('numero_sous_compte')
            ->get();
        
        $currentYear = date('Y');
        $latestEntry = JournalEntry::where('entreprise_id', $user->entreprise_id)
            ->whereYear('date', $currentYear)
            ->orderBy('id', 'desc')
            ->first();

        $nextNum = $latestEntry ? intval(preg_replace('/[^0-9]/', '', $latestEntry->numero_piece)) + 1 : 1;
        $nextPieceNumber = str_pad($nextNum, 6, '0', STR_PAD_LEFT);

        return view('accounting.journal.create', compact('journals', 'accounts', 'nextPieceNumber'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
             return back()->withErrors(['entreprise' => 'Entreprise non identifiée.'])->withInput();
        }
        $entrepriseId = $user->entreprise_id;

        $minDate = now()->subDays(60)->startOfDay(); 
        $maxDate = now()->addMonths(2)->endOfMonth();

        $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'date' => [
                'required',
                'date',
                'after_or_equal:' . $minDate->format('Y-m-d'),
                'before_or_equal:' . $maxDate->format('Y-m-d'),
            ],
            'libelle' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.sous_compte_id' => 'required|exists:sous_comptes,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        $totalDebit = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.001) {
            return back()->withErrors(['balance' => 'Déséquilibré (D: ' . $totalDebit . ', C: ' . $totalCredit . ')'])->withInput();
        }

        try {
            DB::beginTransaction();

            $latestEntry = JournalEntry::where('entreprise_id', $entrepriseId)->orderBy('id', 'desc')->first();
            $nextNum = $latestEntry ? intval(preg_replace('/[^0-9]/', '', $latestEntry->numero_piece)) + 1 : 1;
            $numeroPiece = str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'journal_id' => $request->journal_id,
                'numero_piece' => $numeroPiece,
                'date' => $request->date,
                'libelle' => $request->libelle,
                'entreprise_id' => $entrepriseId,
            ]);

            foreach ($request->lines as $line) {
                if (($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'sous_compte_id' => $line['sous_compte_id'],
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                        'libelle' => $line['libelle'] ?? $request->libelle,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('accounting.journal.index')->with('success', 'Écriture enregistrée.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::withTrashed()->with([
            'lines.sousCompte' => function($q) { $q->withTrashed(); },
            'lines.sousCompte.account', 
            'journal' => function($q) { $q->withTrashed(); }
        ])
            ->where('entreprise_id', $user->entreprise_id)
            ->findOrFail($id);
        return view('accounting.journal.show', compact('entry'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::withTrashed()->with([
            'lines.sousCompte' => function($q) { $q->withTrashed(); },
            'lines.sousCompte.account'
        ])->findOrFail($id);
        if ($entry->entreprise_id != $user->entreprise_id) abort(403);

        $journals = Journal::where(function($q) use ($user) {
            $q->where('entreprise_id', $user->entreprise_id)
              ->orWhereNull('entreprise_id');
        })->get();

        $accounts = SousCompte::where('entreprise_id', $user->entreprise_id)
            ->with('account')
            ->orderBy('numero_sous_compte')
            ->get();
        
        return view('accounting.journal.edit', compact('entry', 'journals', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $entry = JournalEntry::findOrFail($id);
        if ($entry->entreprise_id != $user->entreprise_id) abort(403);

        $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'date' => 'required|date',
            'libelle' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.sous_compte_id' => 'required|exists:sous_comptes,id',
        ]);

        try {
            DB::beginTransaction();
            
            $entry->update([
                'journal_id' => $request->journal_id,
                'date' => $request->date,
                'libelle' => $request->libelle,
            ]);

            $entry->lines()->delete();

            foreach ($request->lines as $line) {
                if (($line['debit'] ?? 0) > 0 || ($line['credit'] ?? 0) > 0) {
                    $entry->lines()->create([
                        'sous_compte_id' => $line['sous_compte_id'],
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                        'libelle' => $line['libelle'] ?? $request->libelle,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('accounting.journal.index')->with('success', 'Mise à jour effectuée.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::where('entreprise_id', $user->entreprise_id)->findOrFail($id);
        $entry->delete();
        return redirect()->route('accounting.journal.index')->with('success', 'Suppression réussie.');
    }
}

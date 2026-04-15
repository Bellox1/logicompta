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
            // Si on consulte les archives et qu'une année est précisée, on filtre dessus, 
            // sinon on laisse l'utilisateur tout voir ou choisir sa période
            if ($request->query('year')) {
                $query->whereYear('date', $request->query('year'));
            }
        } else {
            $query->where('is_archived', false);
            // Par défaut, on ne montre QUE l'année en cours pour ne pas mélanger
            $currentYear = date('Y');
            $year = $request->query('year', $currentYear);
            $query->whereYear('date', $year);
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

        $currentYear = date('Y');
        $activeYear = $request->query('year', $currentYear);

        // Totaux globaux (filtrés par la même année que la liste)
        $totalsQuery = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id');

        if ($request->query('show_archived') == '1') {
            $totalsQuery->where('journal_entries.is_archived', true);
            if ($request->query('year')) {
                $totalsQuery->whereYear('journal_entries.date', $request->query('year'));
            }
        } else {
            $totalsQuery->where('journal_entries.is_archived', false);
            $totalsQuery->whereYear('journal_entries.date', $activeYear);
        }
        if ($request->start_date) {
            $totalsQuery->where('journal_entries.date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $totalsQuery->where('journal_entries.date', '<=', $request->end_date);
        }

        $globalTotalDebit  = $totalsQuery->sum('journal_entry_lines.debit');
        $globalTotalCredit = $totalsQuery->sum('journal_entry_lines.credit');

        return view('accounting.journal.index', compact('entries', 'globalTotalDebit', 'globalTotalCredit', 'activeYear'));
    }

    public function create(Request $request)
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
        $selectedJournalId = $request->query('journal_id');
        $defaultJournalId = $selectedJournalId ?: $journals->first()?->id;

        // Numéro de pièce : calculé initialement pour le journal sélectionné (ou le 1er par défaut)
        if ($defaultJournalId) {
            $latestEntry = JournalEntry::where('entreprise_id', $user->entreprise_id)
                ->where('journal_id', $defaultJournalId)
                ->whereYear('date', $currentYear)
                ->orderBy('id', 'desc')
                ->first();
            $nextNum = $latestEntry ? intval(preg_replace('/[^0-9]/', '', $latestEntry->numero_piece)) + 1 : 1;
        } else {
            $nextNum = 1;
        }
        $nextPieceNumber = str_pad($nextNum, 6, '0', STR_PAD_LEFT);

        return view('accounting.journal.create', compact('journals', 'accounts', 'nextPieceNumber', 'selectedJournalId'));
    }

    /**
     * AJAX : retourne le prochain numéro de pièce pour un journal + une année donnée
     */
    public function getNextPieceNumber(Request $request)
    {
        $user = Auth::user();
        $journalId = $request->query('journal_id');
        $date = $request->query('date', date('Y-m-d'));
        $year = (int) date('Y', strtotime($date));

        if (!$journalId || !$user) {
            \Illuminate\Support\Facades\Log::warning("getNextPieceNumber: missing journal or user", ['j' => $journalId, 'user' => $user->id ?? null]);
            return response()->json(['next_number' => '000001', 'year' => $year, 'will_be_archived' => false]);
        }

        $entrepriseId = session('active_entreprise_id') ?? $user->entreprises()->first()?->id;
        
        $latestEntry = JournalEntry::where('entreprise_id', $entrepriseId)
            ->where('journal_id', $journalId)
            ->whereYear('date', $year)
            ->orderBy('id', 'desc')
            ->first();

        \Illuminate\Support\Facades\Log::info("getNextPieceNumber resolving", [
            'entreprise_id' => $entrepriseId,
            'journal_id' => $journalId,
            'year' => $year,
            'latest_found' => $latestEntry ? $latestEntry->numero_piece : 'none'
        ]);

        $nextNum = $latestEntry ? intval(preg_replace('/[^0-9]/', '', $latestEntry->numero_piece)) + 1 : 1;

        return response()->json([
            'next_number' => str_pad($nextNum, 6, '0', STR_PAD_LEFT),
            'year' => $year,
            'will_be_archived' => ($year !== (int) date('Y')),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
             return back()->withErrors(['entreprise' => 'Entreprise non identifiée.'])->withInput();
        }
        $entrepriseId = $user->entreprise_id;

        $request->validate([
            'journal_id' => 'required|exists:journals,id',
            'date' => 'required|date',
            'libelle' => 'required|string|max:255',
            'lines' => 'required|array|min:2',
            'lines.*.sous_compte_id' => 'required|exists:sous_comptes,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ], [
            'date.required' => 'La date est obligatoire.',
            'date.date' => 'Le format de la date est incorrect.',
            'libelle.required' => 'Le libellé de l\'écriture est obligatoire.',
            'lines.min' => 'Une écriture doit comporter au moins deux lignes.',
        ]);

        $totalDebit = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.001) {
            return back()->withErrors(['balance' => 'Déséquilibré (D: ' . $totalDebit . ', C: ' . $totalCredit . ')'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Déterminer l'année de l'écriture (basée sur la date comptable, pas created_at)
            $entryYear = (int) date('Y', strtotime($request->date));
            $isArchived = ($entryYear !== (int) date('Y'));

            // Numéro de pièce : séquentiel par journal ET par année comptable
            $latestEntry = JournalEntry::where('entreprise_id', $entrepriseId)
                ->where('journal_id', $request->journal_id)
                ->whereYear('date', $entryYear)
                ->orderBy('id', 'desc')
                ->first();
            $nextNum = $latestEntry ? intval(preg_replace('/[^0-9]/', '', $latestEntry->numero_piece)) + 1 : 1;
            $numeroPiece = str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            $entry = JournalEntry::create([
                'journal_id'    => $request->journal_id,
                'numero_piece'  => $numeroPiece,
                'date'          => $request->date,
                'libelle'       => $request->libelle,
                'entreprise_id' => $entrepriseId,
                'is_archived'   => $isArchived,
            ]);

            if ($isArchived) {
                $successMsg = 'Écriture enregistrée et archivée automatiquement (date en ' . $entryYear . ').';
            } else {
                $successMsg = 'Écriture enregistrée. Vous pouvez continuer la saisie dans ce journal.';
            }

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
            return redirect()->route('accounting.journal.create', ['journal_id' => $request->journal_id])
                ->with('success', $successMsg);
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
            $q->where('entreprise_id', '=', $user->entreprise_id)
              ->orWhereNull('entreprise_id');
        })->get();

        $accounts = SousCompte::where('entreprise_id', '=', $user->entreprise_id)
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
        

        // Seul l'admin (créateur) peut supprimer
        if ($user->role !== 'admin') {
            return back()->with('error', 'Impossible de supprimer, ceci est réservé uniquement au créateur de la société..');
        }
        $entry->delete();
        return redirect()->route('accounting.journal.index')->with('success', 'Suppression réussie.');
    }

}

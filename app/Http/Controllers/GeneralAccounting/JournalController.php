<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\Account;
use App\Models\GeneralAccounting\Journal;
use App\Models\GeneralAccounting\JournalEntry;
use App\Models\GeneralAccounting\JournalEntryLine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup')
                ->with('warning', 'Veuillez configurer votre entreprise pour accéder au journal.');
        }

        $query = JournalEntry::with(['journal', 'lines.account'])
            ->where('entreprise_id', $user->entreprise_id);

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $sort = $request->query('sort', 'date');
        $order = $request->query('order', 'desc');
        
        $query->orderBy($sort, $order);
        if ($sort !== 'id') {
            $query->orderBy('id', 'desc');
        }

        $entries = $query->paginate(50)->withQueryString();
            
        return view('accounting.journal.index', compact('entries'));
    }

    public function exportPdf()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }

        $entries = JournalEntry::with(['journal', 'lines.account'])
            ->where('entreprise_id', $user->entreprise_id)
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();
            
        return view('accounting.journal.pdf', compact('entries', 'user'));
    }


    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        
        $journals = Journal::all();
        $accounts = Account::orderBy('code_compte')->get()->groupBy('classe');
        
        $latestEntry = JournalEntry::where('entreprise_id', $user->entreprise_id)->orderBy('id', 'desc')->first();
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

        $minDate = now()->subDays(30)->startOfDay(); // Élargi un peu pour la tester
        $maxDate = now()->endOfMonth();

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
            'lines.*.account_id' => 'required|exists:accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ]);

        $totalDebit = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.001) {
            return back()->withErrors(['balance' => 'L\'écriture n\'est pas équilibrée (Total Débit: ' . $totalDebit . ', Total Crédit: ' . $totalCredit . ')'])->withInput();
        }

        if ($totalDebit <= 0) {
            return back()->withErrors(['amount' => 'Le montant de l\'écriture doit être supérieur à zéro.'])->withInput();
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
                        'account_id' => $line['account_id'],
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                        'libelle' => $line['libelle'] ?? $request->libelle,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('accounting.journal.create')->with('success', 'Écriture enregistrée avec succès ! (Pièce N° ' . $numeroPiece . ')');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur : ' . $e->getMessage()])->withInput();
        }
    }


    public function show($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::with(['lines.account', 'journal'])
            ->where('entreprise_id', $user->entreprise_id)
            ->findOrFail($id);
        return view('accounting.journal.show', compact('entry'));
    }

    public function showPdf($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::with(['lines.account', 'journal'])
            ->where('entreprise_id', $user->entreprise_id)
            ->findOrFail($id);
        return view('accounting.journal.show-pdf', compact('entry', 'user'));
    }


    /* --- IMPORT METHODS --- */

    public function importForm()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) return redirect()->route('entreprise.setup');
        return view('accounting.journal.import');
    }

    public function importProcess(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;

        // Cas 1 : Validation de la réindexation (Données en session)
        if ($request->has('force_reindex') && session()->has('pending_import')) {
            $rows = session('pending_import');
            $grouped = collect($rows)->groupBy('piece');
            session()->forget('pending_import');
            $forceReindex = true;
        } 
        // Cas 2 : Nouvel import (Fichier CSV)
        else {
            $request->validate(['file' => 'required|file|mimes:csv,txt']);
            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), 'r');
            
            $header = fgetcsv($handle, 1000, ';');
            if (!$header) return back()->with('error', 'En-tête manquant.');

            $map = $this->mapCsvHeaders($header);
            
            $required = [
                'account' => 'COMPTE (ou N° COMPTE)',
                'debit' => 'DÉBIT',
                'credit' => 'CRÉDIT'
            ];
            
            foreach ($required as $key => $label) {
                if (!isset($map[$key])) {
                    return back()->with('error', "La colonne obligatoire [$label] est introuvable. Vérifiez que votre fichier CSV contient bien une ligne d'en-tête avec ces noms.");
                }
            }

            $rows = [];
            $lastDate = now()->format('Y-m-d');
            $lastPiece = 'IMP-' . date('Ymd-His');
            $lastJournal = 'AC';

            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                if (count($data) < count($header)) continue;
                
                $rowDate = isset($map['date']) && !empty($data[$map['date']]) ? $this->formatImportDate($data[$map['date']]) : $lastDate;
                $rowPiece = isset($map['piece']) && !empty($data[$map['piece']]) ? trim($data[$map['piece']]) : $lastPiece;
                $rowJournal = isset($map['journal']) && !empty($data[$map['journal']]) ? trim($data[$map['journal']]) : $lastJournal;

                // Nettoyage agressif des nombres (enlève espaces, NBSP, etc.)
                $parseNumber = function($val) {
                    if (empty($val)) return 0;
                    // Supprimer les espaces standard, insécables (NBSP), etc.
                    $clean = preg_replace('/[\s\x{00A0}\x{202F}]/u', '', $val);
                    // Remplacer la virgule par un point
                    $clean = str_replace(',', '.', $clean);
                    // Garder seulement les chiffres et le point (ou signe moins)
                    $clean = preg_replace('/[^0-9\.-]/', '', $clean);
                    return abs(floatval($clean));
                };

                $rows[] = [
                    'date' => $rowDate,
                    'piece' => $rowPiece,
                    'journal_code' => $rowJournal,
                    'account' => trim($data[$map['account']]),
                    'label' => isset($map['label']) ? trim($data[$map['label']]) : 'Importation',
                    'debit' => $parseNumber($data[$map['debit']]),
                    'credit' => $parseNumber($data[$map['credit']]),
                ];

                $lastDate = $rowDate;
                $lastPiece = $rowPiece;
                $lastJournal = $rowJournal;
            }
            fclose($handle);

            if (empty($rows)) return back()->with('error', 'Le fichier est vide.');
            
            $grouped = collect($rows)->groupBy('piece');
            $forceReindex = false;

            // Vérification des doublons
            $allPiecesInFile = $grouped->keys()->toArray();
            $conflicts = JournalEntry::whereIn('numero_piece', $allPiecesInFile)->pluck('numero_piece')->toArray();

            if (!empty($conflicts)) {
                $count = count($conflicts);
                session(['pending_import' => $rows]); // On stocke pour le prochain clic
                return back()->with('error', "Attention : $count numéro(s) de pièce existent déjà.")
                             ->with('needs_reindex', true);
            }
        }

        try {
            DB::beginTransaction();
            // 2. Traitement (Réindexation si demandé ou si pas de conflits)
            $maxNum = JournalEntry::where('entreprise_id', $entrepriseId)
                ->selectRaw('MAX(CAST(numero_piece AS INTEGER)) as max_val')
                ->value('max_val') ?: 0;
            $nextNum = $maxNum + 1;

            foreach ($grouped as $originalPiece => $lines) {
                // Utiliser une tolérance plus souple pour les arrondis (0.1 -> 1.0)
                $totalDebit = round($lines->sum('debit'), 2);
                $totalCredit = round($lines->sum('credit'), 2);

                if (abs($totalDebit - $totalCredit) > 0.05) {
                    throw new \Exception("Déséquilibre sur la pièce $originalPiece (Débit: $totalDebit, Crédit: $totalCredit)");
                }

                // Attribution du numéro
                if ($request->has('force_reindex')) {
                    $usedPieceNumber = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
                    $nextNum++;
                } else {
                    $usedPieceNumber = $originalPiece;
                }

                $firstLine = $lines->first();
                $journal = Journal::where('code', $firstLine['journal_code'])->first() ?? Journal::first();
                
                $entry = JournalEntry::create([
                    'journal_id' => $journal->id,
                    'numero_piece' => $usedPieceNumber,
                    'date' => $firstLine['date'],
                    'libelle' => $firstLine['label'],
                    'entreprise_id' => $entrepriseId,
                ]);

                foreach ($lines as $line) {
                    $account = Account::where('code_compte', $line['account'])->first();
                    if (!$account) throw new \Exception("Compte non trouvé : " . $line['account']);

                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $account->id,
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                        'libelle' => $line['label'],
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('accounting.journal.index')->with('success', count($grouped) . " écritures importées.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    private function mapCsvHeaders($header)
    {
        $map = [];
        $columns = [
            'date' => ['date', 'dates', 'le', 'jour'],
            'piece' => ['piece', 'num pc', 'num_pc', 'réf', 'ref', 'numero_piece', 'pc', 'n° pièce', 'n pièce'],
            'journal' => ['journalCode', 'journal', 'code_journal', 'code journal', 'jrn'],
            'account' => ['account', 'compte', 'n° de compte', 'code_compte', 'code compte', 'n° compte', 'n compte', 'num compte', 'no compte', 'compte n°', 'n° de compte'],
            'label' => ['label', 'libelles', 'libellés', 'operation', 'libelle', 'libellé', 'intitulé / libellé', 'intitulé', 'désignation'],
            'debit' => ['debit', 'débit', 'montant_debit', 'entrant', 'somme débit'],
            'credit' => ['credit', 'crédit', 'montant_credit', 'sortant', 'somme crédit'],
        ];

        foreach ($header as $index => $colName) {
            // Supprimer uniquement le BOM UTF-8 s'il est présent
            $colName = str_replace("\xEF\xBB\xBF", '', $colName);
            $colName = mb_strtolower(trim($colName));
            
            foreach ($columns as $key => $aliases) {
                if (in_array($colName, $aliases)) {
                    $map[$key] = $index;
                }
            }
        }
        return $map;
    }

    private function formatImportDate($date)
    {
        // Supprime les espaces inutiles
        $date = trim($date);
        
        // List common formats
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d', 'j/n/Y', 'j-n-Y'];
        
        foreach ($formats as $f) {
            try {
                return \Carbon\Carbon::createFromFormat($f, $date)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Final attempt with auto-parsing
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d'); // Default to today if really bad
        }
    }
}

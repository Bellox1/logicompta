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
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup')
                ->with('warning', 'Veuillez configurer votre entreprise pour accéder au journal.');
        }

        $query = JournalEntry::query()->with(['journal', 'lines.sousCompte.account'])
            ->where('entreprise_id', $user->entreprise_id);
        
        if ($request->query('show_archived') == '1') {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }

        if ($request->start_date) {
            $query->where('date', '>=', $request->start_date, 'and');
        }
        if ($request->end_date) {
            $query->where('date', '<=', $request->end_date, 'and');
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

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }

        $query = JournalEntry::query()->with(['journal', 'lines.sousCompte.account'])
            ->where('entreprise_id', '=', $user->entreprise_id, 'and');
        
        if ($request->query('show_archived') == '1') {
            $query->where('is_archived', '=', true, 'and');
        } else {
            $query->where('is_archived', '=', false, 'and');
        }

        if ($request->start_date) {
            $query->where('date', '>=', $request->start_date, 'and');
        }
        if ($request->end_date) {
            $query->where('date', '<=', $request->end_date, 'and');
        }

        $sort = $request->query('sort', 'date');
        $order = $request->query('order', 'asc');
        
        $query->orderBy($sort, $order);
        if ($sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        $entries = $query->get();
            
        return view('accounting.journal.pdf', compact('entries', 'user'));
    }


    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        
        $journals = Journal::where(function($q) use ($user) {
            $q->where('entreprise_id', '=', $user->entreprise_id, 'and')
              ->orWhereNull('entreprise_id');
        }, null, null, 'and')->get();

        $accounts = \App\Models\SousCompte::where('entreprise_id', '=', $user->entreprise_id, 'and')
            ->with('account')
            ->orderBy('numero_sous_compte')
            ->get();
        
        $currentYear = date('Y');
        $latestEntry = JournalEntry::where('entreprise_id', '=', $user->entreprise_id, 'and')
            ->whereYear('date', '=', $currentYear, 'and')
            ->orderBy('id', 'desc')
            ->first(['*']);
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
            'lines.*.sous_compte_id' => 'required|exists:sous_comptes,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ], [
            'lines.min' => 'Une écriture comptable doit comporter au moins 2 lignes (un débit et un crédit).'
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

            $latestEntry = JournalEntry::where('entreprise_id', '=', $entrepriseId, 'and')->orderBy('id', 'desc')->first(['*']);
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
                    $sousCompte = \App\Models\SousCompte::findOrFail($line['sous_compte_id']);
                    
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'sous_compte_id' => $sousCompte->id,
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                        'libelle' => $line['libelle'] ?? $request->libelle,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('accounting.journal.index')->with('success', 'Écriture enregistrée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::with(['lines.sousCompte.account'])->findOrFail($id);
        
        if ($entry->entreprise_id != $user->entreprise_id) {
            abort(403);
        }



        $journals = Journal::where(function($q) use ($user) {
            $q->where('entreprise_id', '=', $user->entreprise_id, 'and')
              ->orWhereNull('entreprise_id');
        }, null, null, 'and')->get();
        $accounts = \App\Models\SousCompte::where('entreprise_id', '=', $user->entreprise_id, 'and')
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
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
        ], [
            'lines.min' => 'Une écriture comptable doit comporter au moins 2 lignes (un débit et un crédit).'
        ]);

        $totalDebit = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.001) {
            return back()->withErrors(['balance' => 'L\'écriture n\'est pas équilibrée.'])->withInput();
        }

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
                    $sousCompte = \App\Models\SousCompte::findOrFail($line['sous_compte_id']);

                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'sous_compte_id' => $sousCompte->id,
                        'debit' => $line['debit'] ?? 0,
                        'credit' => $line['credit'] ?? 0,
                        'libelle' => $line['libelle'] ?? $request->libelle,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('accounting.journal.index')->with('success', 'Écriture mise à jour.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::findOrFail($id);
        if ($entry->entreprise_id != $user->entreprise_id) abort(403);


        $entry->lines()->delete();
        $entry->delete();

        return redirect()->route('accounting.journal.index')->with('success', 'Écriture supprimée.');
    }


    public function show($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::with(['lines.sousCompte.account', 'journal'])
            ->where('entreprise_id', '=', $user->entreprise_id, 'and')
            ->findOrFail($id);
        return view('accounting.journal.show', compact('entry'));
    }

    public function showPdf($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::with(['lines.sousCompte.account', 'journal'])
            ->where('entreprise_id', '=', $user->entreprise_id, 'and')
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

    public function importPreview(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;

        if ($request->isMethod('get')) {
            $rows = session('pending_journal_preview');
            if (!$rows) return redirect()->route('accounting.journal.import');
            
            // On recalcule le statut pour l'affichage (car non envoyé par le formulaire)
            $previewData = [];
            foreach ($rows as $row) {
                $status = 'ok';
                $isMain = Account::where('code_compte', $row['account'])->exists();
                if ($isMain) {
                    $status = 'error_main';
                } else {
                    $sc = SousCompte::where('entreprise_id', $entrepriseId)->where('numero_sous_compte', $row['account'])->exists();
                    if (!$sc) {
                        $parent = Account::whereRaw('? LIKE code_compte || "%"', [$row['account']])->orderByRaw('LENGTH(code_compte) DESC')->first();
                        if (!$parent) $status = 'error_no_parent';
                    }
                }
                $previewData[] = array_merge($row, ['status' => $status]);
            }
            return view('accounting.journal.import-preview', compact('previewData'));
        }

        $request->validate(['file' => 'required|file']);
        $file = $request->file('file');
        
        $fp = fopen($file->getRealPath(), 'r');
        $firstLine = fgets($fp);
        fclose($fp);
        
        $delimiters = [';', ',', "\t"];
        $delimiter = ';';
        $maxCount = 0;
        foreach ($delimiters as $d) {
            $count = substr_count($firstLine, $d);
            if ($count > $maxCount) {
                $maxCount = $count;
                $delimiter = $d;
            }
        }

        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle, 1000, $delimiter);
        if (!$header) {
            if ($handle) fclose($handle);
            return back()->with('error', 'En-tête manquant.');
        }

        $map = $this->mapCsvHeaders($header);
        $required = ['account' => 'COMPTE', 'debit' => 'DÉBIT', 'credit' => 'CRÉDIT'];
        foreach ($required as $key => $label) {
            if (!isset($map[$key])) {
                fclose($handle);
                return back()->with('error', "Colonne obligatoire [$label] manquante.");
            }
        }

        $previewData = [];
        $lineNum = 1;

        $parseNumber = function($val) {
            if (empty($val)) return 0;
            $clean = preg_replace('/[\s\x{00A0}\x{202F}]/u', '', $val);
            $clean = str_replace(',', '.', $clean);
            $clean = preg_replace('/[^0-9\.-]/', '', $clean);
            return abs(floatval($clean));
        };

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $lineNum++;
            if (count($data) < count($header)) continue;
            
            $rowDate = isset($map['date']) && !empty($data[$map['date']]) ? $this->formatImportDate($data[$map['date']]) : date('Y-m-d');
            $rowPiece = isset($map['piece']) && !empty($data[$map['piece']]) ? trim($data[$map['piece']]) : 'AUTO';
            $rowJournal = isset($map['journal']) && !empty($data[$map['journal']]) ? trim($data[$map['journal']]) : 'AC';
            $accountNum = trim($data[$map['account']]);

            // Validation de base pour le statut
            $status = 'ok';
            $isMain = Account::where('code_compte', $accountNum)->exists();
            if ($isMain) {
                $status = 'error_main';
            } else {
                $sc = SousCompte::where('entreprise_id', $entrepriseId)->where('numero_sous_compte', $accountNum)->exists();
                if (!$sc) {
                    $parent = Account::whereRaw('? LIKE code_compte || "%"', [$accountNum])->orderByRaw('LENGTH(code_compte) DESC')->first();
                    if (!$parent) {
                        $status = 'error_no_parent';
                    }
                }
            }

            $previewData[] = [
                'line' => $lineNum,
                'date' => $rowDate,
                'piece' => $rowPiece,
                'journal' => $rowJournal,
                'account' => $accountNum,
                'label' => isset($map['label']) ? trim($data[$map['label']]) : 'Importation',
                'debit' => $parseNumber($data[$map['debit']]),
                'credit' => $parseNumber($data[$map['credit']]),
                'status' => $status
            ];
        }
        fclose($handle);

        if (empty($previewData)) return back()->with('error', 'Le fichier est vide.');

        session(['pending_journal_preview' => $previewData]);
        return view('accounting.journal.import-preview', compact('previewData'));
    }

    public function importProcess(Request $request)
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        $rows = $request->input('rows', []);

        if (empty($rows)) return redirect()->route('accounting.journal.import')->with('error', 'Aucune donnée soumise.');

        // On met à jour la session avec les données actuelles (au cas où on revient en arrière pour erreur)
        session(['pending_journal_preview' => $rows]);

        $errors = [];
        $grouped = collect($rows)->groupBy('piece');
        $validatedPieces = [];

        // 1. Validation de l'équilibre et des comptes après modif
        foreach ($grouped as $pieceNum => $lines) {
            $pieceTotalDebit = 0;
            $pieceTotalCredit = 0;
            $pieceLines = [];
            $pieceErrors = [];

            foreach ($lines as $line) {
                $accountNum = trim($line['account'] ?? '');
                $debit = floatval($line['debit'] ?? 0);
                $credit = floatval($line['credit'] ?? 0);
                
                $pieceTotalDebit += $debit;
                $pieceTotalCredit += $credit;

                // Re-vérification stricte
                $isMain = Account::where('code_compte', $accountNum)->exists();
                if ($isMain) {
                    $pieceErrors[] = "La pièce $pieceNum (Ligne " . ($line['line'] ?? '?') . ") utilise le compte général $accountNum (Interdit).";
                }

                $sousCompte = SousCompte::where('entreprise_id', $entrepriseId)->where('numero_sous_compte', $accountNum)->first();
                
                if (!$sousCompte) {
                    $parent = Account::whereRaw('? LIKE code_compte || "%"', [$accountNum])->orderByRaw('LENGTH(code_compte) DESC')->first();
                    if (!$parent) {
                        $pieceErrors[] = "Le compte " . $accountNum . " (Ligne " . ($line['line'] ?? '') . ") n'a pas de compte parent trouvé.";
                    } else {
                        $pieceLines[] = [
                            'is_new' => true,
                            'account_id' => $parent->id,
                            'numero' => $accountNum,
                            'libelle' => 'Général ' . $parent->libelle,
                            'debit' => $debit,
                            'credit' => $credit,
                            'label' => $line['label'] ?? 'Import'
                        ];
                    }
                } else {
                    $pieceLines[] = [
                        'is_new' => false,
                        'sous_compte_id' => $sousCompte->id,
                        'debit' => $debit,
                        'credit' => $credit,
                        'label' => $line['label'] ?? 'Import'
                    ];
                }
            }

            if (abs($pieceTotalDebit - $pieceTotalCredit) > 0.1) {
                $pieceErrors[] = "La pièce $pieceNum est déséquilibrée (D: $pieceTotalDebit, C: $pieceTotalCredit).";
            }

            if (!empty($pieceErrors)) {
                $errors = array_merge($errors, $pieceErrors);
            } else {
                $first = collect($lines)->first();
                $validatedPieces[] = [
                    'piece' => $pieceNum,
                    'date' => $first['date'] ?? date('Y-m-d'),
                    'journal' => $first['journal'] ?? 'AC',
                    'label' => $first['label'] ?? 'Import',
                    'lines' => $pieceLines
                ];
            }
        }

        if (!empty($errors)) {
            return back()->with('error_list', $errors)->with('error', "Des erreurs subsistent dans les données.");
        }

        // 2. Enregistrement final
        try {
            DB::beginTransaction();
            foreach ($validatedPieces as $vPiece) {
                $journal = Journal::where('name', $vPiece['journal'])->first() ?? Journal::first();
                
                $entry = JournalEntry::create([
                    'entreprise_id' => $entrepriseId,
                    'journal_id' => $journal->id,
                    'numero_piece' => $vPiece['piece'],
                    'date' => $vPiece['date'],
                    'libelle' => $vPiece['label']
                ]);

                foreach ($vPiece['lines'] as $vLine) {
                    $scId = $vLine['sous_compte_id'] ?? null;
                    if ($vLine['is_new']) {
                        $sc = \App\Models\SousCompte::firstOrCreate(
                            ['entreprise_id' => $entrepriseId, 'numero_sous_compte' => $vLine['numero']],
                            ['account_id' => $vLine['account_id'], 'libelle' => $vLine['libelle']]
                        );
                        $scId = $sc->id;
                    }

                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'sous_compte_id' => $scId,
                        'debit' => $vLine['debit'],
                        'credit' => $vLine['credit'],
                        'libelle' => $vLine['label']
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('accounting.journal.index')->with('success', count($validatedPieces) . " écritures importées.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Erreur technique : " . $e->getMessage());
        }
    }

    private function mapCsvHeaders($header)
    {
        $map = [];
        $columns = [
            'date' => ['date', 'dates', 'le', 'jour', 'période'],
            'piece' => ['piece', 'pièce', 'num pc', 'num_pc', 'réf', 'ref', 'numero_piece', 'pc', 'n° pièce', 'n pièce', 'numéro', 'numero', 'n°', 'no'],
            'journal' => ['journalCode', 'journal', 'code_journal', 'code journal', 'jrn', 'libellé journal'],
            'account' => ['account', 'compte', 'n° de compte', 'code_compte', 'code compte', 'n° compte', 'n compte', 'num compte', 'no compte', 'compte n°', 'n° de compte', 'sous-compte', 'sous compte', 'sous_compte', 'num sous-compte', 'n° sc', 'compte général'],
            'label' => ['label', 'libelles', 'libellés', 'operation', 'libelle', 'libellé', 'intitulé / libellé', 'intitulé', 'désignation', 'description'],
            'debit' => ['debit', 'débit', 'montant_debit', 'entrant', 'somme débit', 'débits'],
            'credit' => ['credit', 'crédit', 'montant_credit', 'sortant', 'somme crédit', 'crédits'],
        ];

        foreach ($header as $index => $colName) {
            // Nettoyage agressif : minuscule, pas d'accents, pas de ponctuation
            $rawName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', mb_strtolower(trim($colName)));
            $cleanName = preg_replace('/[^a-z0-9]/', '', $rawName);
            
            if (empty($cleanName)) {
                // Cas des colonnes nommées "N°" ou "#" qui deviennent vides après nettoyage a-z0-9
                if (str_contains($rawName, 'n') || str_contains($rawName, '#')) {
                    if (!isset($map['piece'])) $map['piece'] = $index;
                }
                continue;
            }

            foreach ($columns as $key => $aliases) {
                foreach ($aliases as $alias) {
                    $cleanAlias = preg_replace('/[^a-z0-9]/', '', mb_strtolower($alias));
                    
                    // Match EXACT (après nettoyage)
                    if ($cleanName === $cleanAlias) {
                        // Priorité absolue au sous-compte pour la colonne 'account'
                        if ($key === 'account' && str_contains($cleanName, 'sous')) {
                            $map[$key] = $index;
                            continue 3; 
                        }
                        // Si exact match, on affecte et on passe à la colonne suivante (continue 3 sort des 2 boucles aliases)
                        if (!isset($map[$key])) {
                             $map[$key] = $index;
                             continue 3;
                        }
                    }

                    // Match PARTIEL (si le nom de la colonne contient un mot clé métier significatif)
                    if (strlen($cleanAlias) >= 4 && str_contains($cleanName, $cleanAlias)) {
                        if (!isset($map[$key])) {
                            $map[$key] = $index;
                        }
                    }
                }
            }
        }
        
        // Second pass if mandatory column missing (hard filters)
        if (!isset($map['account']) || !isset($map['debit']) || !isset($map['credit'])) {
            foreach ($header as $index => $colName) {
                $colName = mb_strtolower(trim($colName));
                if (!isset($map['account']) && (str_contains($colName, 'compte') || $colName == 'acc')) $map['account'] = $index;
                if (!isset($map['debit']) && (str_contains($colName, 'deb') || str_contains($colName, 'entrant'))) $map['debit'] = $index;
                if (!isset($map['credit']) && (str_contains($colName, 'cred') || str_contains($colName, 'sortant'))) $map['credit'] = $index;
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

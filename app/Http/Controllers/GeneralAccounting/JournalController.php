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
            
            // Detect delimiter efficiently
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
            
            $required = [
                'account' => 'COMPTE (ou N° COMPTE)',
                'debit' => 'DÉBIT',
                'credit' => 'CRÉDIT'
            ];
            
            foreach ($required as $key => $label) {
                if (!isset($map[$key])) {
                    fclose($handle);
                    return back()->with('error', "La colonne obligatoire [$label] est introuvable. Colonnes : " . implode(', ', $header));
                }
            }

            $rows = [];
            $lastDate = now()->format('Y-m-d');
            $lastPiece = 'IMP-' . date('Ymd-His');
            $lastJournal = 'AC';
            $lineNum = 1;

            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $lineNum++;
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
                    'line' => $lineNum,
                    'date' => $rowDate,
                    'piece' => $rowPiece,
                    'journal_name' => $rowJournal,
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
            $conflicts = JournalEntry::whereIn('numero_piece', $allPiecesInFile)->where('entreprise_id', '=', $entrepriseId, 'and')->pluck('numero_piece')->toArray();

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
            $maxNum = JournalEntry::where('entreprise_id', '=', $entrepriseId, 'and')
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
                $journal = Journal::where('name', '=', $firstLine['journal_name'], 'and')->first(['*']) ?? Journal::first();
                
                $entry = JournalEntry::create([
                    'journal_id' => $journal->id,
                    'numero_piece' => $usedPieceNumber,
                    'date' => $firstLine['date'],
                    'libelle' => $firstLine['label'],
                    'entreprise_id' => $entrepriseId,
                ]);

                foreach ($lines as $line) {
                    $sousCompte = \App\Models\SousCompte::where('entreprise_id', '=', $entrepriseId, 'and')
                        ->where('numero_sous_compte', '=', $line['account'], 'and')
                        ->first(['*']);

                    if (!$sousCompte) {
                        // Tentative de trouver le compte par correspondance exacte ou par préfixe
                        $account = Account::where('code_compte', '=', $line['account'], 'and')->first(['*']);
                        
                        if (!$account) {
                            // Recherche par préfixe (le plus long d'abord)
                            $account = Account::whereRaw('? LIKE code_compte || "%"', [$line['account']], 'and')
                                ->orderByRaw('LENGTH(code_compte) DESC')
                                ->first();
                        }

                        if (!$account) throw new \Exception("[Ligne {$line['line']}] Compte ou sous-compte non trouvé : " . $line['account']);
                        
                        // Création automatique du sous-compte pour ce parent
                        $sousCompte = \App\Models\SousCompte::firstOrCreate(
                            ['entreprise_id' => $entrepriseId, 'numero_sous_compte' => $line['account']],
                            ['account_id' => $account->id, 'libelle' => 'Général ' . $account->libelle]
                        );
                    }

                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'sous_compte_id' => $sousCompte->id,
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
            'account' => ['account', 'compte', 'n° de compte', 'code_compte', 'code compte', 'n° compte', 'n compte', 'num compte', 'no compte', 'compte n°', 'n° de compte', 'sous-compte', 'sous compte', 'sous_compte', 'num sous-compte'],
            'label' => ['label', 'libelles', 'libellés', 'operation', 'libelle', 'libellé', 'intitulé / libellé', 'intitulé', 'désignation'],
            'debit' => ['debit', 'débit', 'montant_debit', 'entrant', 'somme débit'],
            'credit' => ['credit', 'crédit', 'montant_credit', 'sortant', 'somme crédit'],
        ];

        foreach ($header as $index => $colName) {
            // Nettoyage agressif : minuscule, pas d'accents, pas de ponctuation
            $rawName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', mb_strtolower(trim($colName)));
            $cleanName = preg_replace('/[^a-z0-9]/', '', $rawName);
            
            foreach ($columns as $key => $aliases) {
                foreach ($aliases as $alias) {
                    $cleanAlias = preg_replace('/[^a-z0-9]/', '', mb_strtolower($alias));
                    
                    // Match EXACT (après nettoyage) -> Sortie immédiate pour cette colonne
                    if ($cleanName === $cleanAlias) {
                        // Priorité absolue au sous-compte
                        if ($key === 'account' && str_contains($cleanName, 'sous')) {
                            $map[$key] = $index;
                            continue 3; 
                        }
                        if (!isset($map[$key]) || ($key === 'account' && !str_contains($cleanName, 'sous'))) {
                             $map[$key] = $index;
                        }
                    }

                    // Match PARTIEL (si le nom de la colonne contient un mot clé métier)
                    if (strlen($cleanAlias) >= 4 && str_contains($cleanName, $cleanAlias)) {
                        if (!isset($map[$key]) || ($key === 'account' && str_contains($cleanName, 'sous'))) {
                            $map[$key] = $index;
                        }
                    }
                }
            }
        }
        
        // Second pass if mandatory column missing
        if (!isset($map['account']) || !isset($map['debit']) || !isset($map['credit'])) {
            foreach ($header as $index => $colName) {
                $colName = mb_strtolower(trim($colName));
                if (!isset($map['account']) && (str_contains($colName, 'compte') || $colName == 'acc' || $colName == 'n')) $map['account'] = $index;
                if (!isset($map['debit']) && str_contains($colName, 'deb')) $map['debit'] = $index;
                if (!isset($map['credit']) && str_contains($colName, 'cred')) $map['credit'] = $index;
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

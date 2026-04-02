<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\Account;
use App\Models\GeneralAccounting\Journal;
use App\Models\GeneralAccounting\JournalEntry;
use App\Models\GeneralAccounting\JournalEntryLine;
use App\Models\SousCompte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class JournalDataController extends Controller
{
    /**
     * Export global du journal en PDF
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }

        $query = JournalEntry::query()->with(['journal', 'lines.sousCompte.account'])
            ->where('entreprise_id', '=', $user->entreprise_id);
        
        if ($request->query('show_archived') == '1') {
            $query->where('is_archived', '=', true);
        } else {
            $query->where('is_archived', '=', false);
        }

        if ($request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('date', '<=', $request->end_date);
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

    /**
     * Affichage PDF d'une seule écriture
     */
    public function showPdf($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::with(['lines.sousCompte.account', 'journal'])
            ->where('entreprise_id', '=', $user->entreprise_id)
            ->findOrFail($id);
        return view('accounting.journal.show-pdf', compact('entry', 'user'));
    }

    /**
     * Importation OCR Google Vision
     */
    public function ocrImport(Request $request)
    {
        $request->validate(['file' => 'required|image|max:5120']);
        $file = $request->file('file');
        $apiKey = env('GOOGLE_VISION_API_KEY');

        if (!$apiKey || $apiKey === 'xxxxxxx') {
            return response()->json(['error' => 'Clé API Google Vision non configurée.'], 400);
        }

        try {
            $imageData = base64_encode(file_get_contents($file->getRealPath()));
            
            $response = Http::post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
                'requests' => [
                    [
                        'image' => ['content' => $imageData],
                        'features' => [['type' => 'TEXT_DETECTION']]
                    ]
                ]
            ]);

            if ($response->failed()) {
                return response()->json(['error' => 'Erreur API Vision: ' . $response->body()], 500);
            }

            $result = $response->json();
            $fullText = $result['responses'][0]['fullTextAnnotation']['text'] ?? '';

            if (empty($fullText)) {
                return response()->json(['error' => 'Aucun texte détecté sur la facture.'], 404);
            }

            $data = [
                'raw_text' => $fullText,
                'date' => null,
                'amount' => null,
                'libelle' => 'Achat selon facture'
            ];

            $datePatterns = [
                '/\b(\d{1,2})[\/\.-](\d{1,2})[\/\.-](\d{2,4})\b/',
                '/\b(\d{4})[\/\.-](\d{1,2})[\/\.-](\d{1,2})\b/'
            ];
            foreach ($datePatterns as $pattern) {
                if (preg_match($pattern, $fullText, $matches)) {
                    if (strlen($matches[3] ?? '') == 2) $matches[3] = '20'.$matches[3];
                    $year = $matches[3] ?? date('Y');
                    $month = $matches[2] ?? date('m');
                    $day = $matches[1] ?? date('d');
                    $data['date'] = "$year-$month-$day";
                    break;
                }
            }

            preg_match_all('/\b\d+[\s,.]\d{2,3}[:]*\s*([\d\s,.]+)\b|\b\d{2,}\b/', $fullText, $amounts);
            $numericAmounts = [];
            foreach ($amounts[0] as $amt) {
                $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $amt));
                if (is_numeric($clean)) $numericAmounts[] = (float)$clean;
            }
            
            if (preg_match('/(?:TOTAL|TTC|NET|PAYER)\s*[:]*\s*([\d\s,.]+)/i', $fullText, $m)) {
                $cleanMatch = preg_replace('/[^0-9.]/', '', str_replace(',', '.', $m[1]));
                if (is_numeric($cleanMatch)) $data['amount'] = (float)$cleanMatch;
            }
            
            if (!$data['amount'] && !empty($numericAmounts)) {
                $data['amount'] = max($numericAmounts);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur technique: ' . $e->getMessage()], 500);
        }
    }

    /* --- IMPORT CSV METHODS --- */

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

        session(['pending_journal_preview' => $rows]);

        $errors = [];
        $grouped = collect($rows)->groupBy('piece');
        $validatedPieces = [];

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
            $rawName = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', mb_strtolower(trim($colName)));
            $cleanName = preg_replace('/[^a-z0-9]/', '', $rawName);
            
            if (empty($cleanName)) {
                if (str_contains($rawName, 'n') || str_contains($rawName, '#')) {
                    if (!isset($map['piece'])) $map['piece'] = $index;
                }
                continue;
            }

            foreach ($columns as $key => $aliases) {
                foreach ($aliases as $alias) {
                    $cleanAlias = preg_replace('/[^a-z0-9]/', '', mb_strtolower($alias));
                    if ($cleanName === $cleanAlias) {
                        if ($key === 'account' && str_contains($cleanName, 'sous')) {
                            $map[$key] = $index;
                            continue 3; 
                        }
                        if (!isset($map[$key])) {
                             $map[$key] = $index;
                             continue 3;
                        }
                    }
                    if (strlen($cleanAlias) >= 4 && str_contains($cleanName, $cleanAlias)) {
                        if (!isset($map[$key])) {
                            $map[$key] = $index;
                        }
                    }
                }
            }
        }
        
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
        $date = trim($date);
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d', 'j/n/Y', 'j-n-Y'];
        foreach ($formats as $f) {
            try {
                return \Carbon\Carbon::createFromFormat($f, $date)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }
        }
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }
}

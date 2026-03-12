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
    public function index()
    {
        $user = Auth::user();
        
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup')
                ->with('warning', 'Veuillez configurer votre entreprise pour accéder au journal.');
        }

        $entries = JournalEntry::with(['journal', 'lines.account'])
            ->where('entreprise_id', $user->entreprise_id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50);
            
        return view('accounting.journal.index', compact('entries'));
    }

    public function ledger(Request $request, $account_id = null)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;

        $accounts = Account::orderBy('code_compte')->get()->groupBy('classe');
        $selectedAccount = $account_id ? Account::find($account_id) : null;
        $selectedClass = $request->query('class');
        $mode = $request->query('mode', 'single');
        
        $data = [];

        if ($mode === 'all') {
            $data = Account::with(['entryLines' => function($q) use ($entrepriseId) {
                $q->whereHas('entry', function($qe) use ($entrepriseId) {
                    $qe->where('entreprise_id', $entrepriseId);
                });
            }, 'entryLines.entry.journal'])
            ->orderBy('code_compte')
            ->get()
            ->filter(fn($acc) => $acc->entryLines->count() > 0);
        } elseif ($mode === 'class' && $selectedClass) {
            $data = Account::with(['entryLines' => function($q) use ($entrepriseId) {
                $q->whereHas('entry', function($qe) use ($entrepriseId) {
                    $qe->where('entreprise_id', $entrepriseId);
                });
            }, 'entryLines.entry.journal'])
            ->where('classe', $selectedClass)
            ->orderBy('code_compte')
            ->get()
            ->filter(fn($acc) => $acc->entryLines->count() > 0);
        } elseif ($selectedAccount) {
            $lines = JournalEntryLine::with('entry.journal')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_lines.account_id', $selectedAccount->id)
                ->where('journal_entries.entreprise_id', $entrepriseId)
                ->orderBy('journal_entries.date', 'asc')
                ->select('journal_entry_lines.*')
                ->get();
            $data = collect([
                (object)[
                    'id' => $selectedAccount->id,
                    'code_compte' => $selectedAccount->code_compte,
                    'libelle' => $selectedAccount->libelle,
                    'entryLines' => $lines
                ]
            ]);
        }

        return view('accounting.ledger', compact('accounts', 'selectedAccount', 'data', 'mode', 'selectedClass'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        
        $journals = Journal::all();
        $accounts = Account::orderBy('code_compte')->get()->groupBy('classe');
        
        $latestEntry = JournalEntry::where('entreprise_id', $user->entreprise_id)->latest()->first();
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

            $latestEntry = JournalEntry::where('entreprise_id', $entrepriseId)->latest()->first();
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

    public function balance()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;

        // On ne récupère que les comptes qui ont des mouvements pour cette entreprise
        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId) {
            $q->whereHas('entry', function($qe) use ($entrepriseId) {
                $qe->where('entreprise_id', $entrepriseId);
            });
        }])->get();
        
        $balanceData = [];
        $grandTotal = [
            'mouv_debit' => 0, 'mouv_credit' => 0,
            'fin_debit' => 0, 'fin_credit' => 0
        ];

        for ($c = 1; $c <= 9; $c++) {
            $classAccounts = $accounts->filter(fn($a) => $a->classe == $c);
            if ($classAccounts->isEmpty()) continue;

            $classData = [
                'label' => 'Total Classe ' . $c,
                'groups' => [],
                'class_totals' => ['mouv_debit' => 0, 'mouv_credit' => 0, 'fin_debit' => 0, 'fin_credit' => 0]
            ];

            $groupedByPrefix = $classAccounts->groupBy(function($acc) {
                return substr(str_pad($acc->code_compte, 2, '0', STR_PAD_RIGHT), 0, 2);
            })->sortKeys();

            foreach ($groupedByPrefix as $prefix => $accs) {
                $groupData = [
                    'prefix' => str_pad($prefix, 6, '0', STR_PAD_RIGHT),
                    'accounts' => [],
                    'group_totals' => ['mouv_debit' => 0, 'mouv_credit' => 0, 'fin_debit' => 0, 'fin_credit' => 0]
                ];

                foreach ($accs as $account) {
                    $mouv_debit = $account->entryLines->sum('debit');
                    $mouv_credit = $account->entryLines->sum('credit');

                    if ($mouv_debit == 0 && $mouv_credit == 0) continue;

                    $solde = $mouv_debit - $mouv_credit;
                    $fin_debit = $solde > 0 ? $solde : 0;
                    $fin_credit = $solde < 0 ? abs($solde) : 0;

                    $accRow = [
                        'code' => str_pad($account->code_compte, 9, '0', STR_PAD_RIGHT),
                        'libelle' => $account->libelle,
                        'mouv_debit' => $mouv_debit,
                        'mouv_credit' => $mouv_credit,
                        'fin_debit' => $fin_debit,
                        'fin_credit' => $fin_credit,
                    ];

                    $groupData['accounts'][] = $accRow;
                    $groupData['group_totals']['mouv_debit'] += $mouv_debit;
                    $groupData['group_totals']['mouv_credit'] += $mouv_credit;
                    $groupData['group_totals']['fin_debit'] += $fin_debit;
                    $groupData['group_totals']['fin_credit'] += $fin_credit;
                }

                if (!empty($groupData['accounts'])) {
                    $classData['groups'][$prefix] = $groupData;
                    $classData['class_totals']['mouv_debit'] += $groupData['group_totals']['mouv_debit'];
                    $classData['class_totals']['mouv_credit'] += $groupData['group_totals']['mouv_credit'];
                    $classData['class_totals']['fin_debit'] += $groupData['group_totals']['fin_debit'];
                    $classData['class_totals']['fin_credit'] += $groupData['group_totals']['fin_credit'];
                }
            }

            if (!empty($classData['groups'])) {
                $balanceData[$c] = $classData;
                $grandTotal['mouv_debit'] += $classData['class_totals']['mouv_debit'];
                $grandTotal['mouv_credit'] += $classData['class_totals']['mouv_credit'];
                $grandTotal['fin_debit'] += $classData['class_totals']['fin_debit'];
                $grandTotal['fin_credit'] += $classData['class_totals']['fin_credit'];
            }
        }

        return view('accounting.balance', compact('balanceData', 'grandTotal'));
    }

    public function bilan()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;

        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId) {
            $q->whereHas('entry', function($qe) use ($entrepriseId) {
                $qe->where('entreprise_id', $entrepriseId);
            });
        }])->get();
        
        $actif = collect();
        $passif = collect();

        foreach ($accounts as $acc) {
            $debit = $acc->entryLines->sum('debit');
            $credit = $acc->entryLines->sum('credit');
            $soldeDebit = $debit - $credit;
            
            if ($soldeDebit == 0) continue;

            if ($acc->classe == 2 || $acc->classe == 3) {
                $actif->push(['libelle' => $acc->libelle, 'solde' => $soldeDebit]);
            } elseif ($acc->classe == 1) {
                $passif->push(['libelle' => $acc->libelle, 'solde' => -$soldeDebit]);
            } elseif ($acc->classe == 4 || $acc->classe == 5) {
                if ($soldeDebit > 0) {
                    $actif->push(['libelle' => $acc->libelle, 'solde' => $soldeDebit]);
                } else {
                    $passif->push(['libelle' => $acc->libelle, 'solde' => abs($soldeDebit)]);
                }
            }
        }

        $totalCharges = $accounts->whereIn('classe', [6, 8])->sum(function($a) {
            $solde = $a->entryLines->sum('debit') - $a->entryLines->sum('credit');
            if ($a->classe == 8 && !in_array(substr($a->code_compte, 0, 2), ['81', '83', '85', '87', '89'])) return 0;
            return $solde > 0 ? $solde : 0;
        });

        $totalProduits = $accounts->whereIn('classe', [7, 8])->sum(function($a) {
            $solde = $a->entryLines->sum('credit') - $a->entryLines->sum('debit');
            if ($a->classe == 8 && !in_array(substr($a->code_compte, 0, 2), ['82', '84', '86', '88'])) return 0;
            return $solde > 0 ? $solde : 0;
        });

        $resultatNet = $totalProduits - $totalCharges;

        $passif->push([
            'libelle' => $resultatNet >= 0 ? 'RÉSULTAT NET (BÉNÉFICE)' : 'RÉSULTAT NET (PERTE)',
            'solde' => $resultatNet,
            'is_resultat' => true
        ]);

        return view('accounting.bilan', compact('actif', 'passif'));
    }

    public function resultat()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;

        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId) {
            $q->whereHas('entry', function($qe) use ($entrepriseId) {
                $qe->where('entreprise_id', $entrepriseId);
            });
        }])->get();

        $data = [
            'charges' => ['total' => 0, 'groups' => []],
            'produits' => ['total' => 0, 'groups' => []]
        ];

        $chargeAccounts = $accounts->filter(function($acc) {
            if ($acc->classe == 6) return true;
            if ($acc->classe == 8 && in_array(substr($acc->code_compte, 0, 2), ['81', '83', '85', '87', '89'])) return true;
            return false;
        });
        
        $groupedCharges = $chargeAccounts->groupBy(function($acc) {
            return substr(str_pad($acc->code_compte, 2, '0', STR_PAD_RIGHT), 0, 2);
        })->sortKeys();

        foreach ($groupedCharges as $prefix => $accs) {
            $groupTotal = 0;
            $accountsList = [];
            foreach ($accs as $acc) {
                $solde = $acc->entryLines->sum('debit') - $acc->entryLines->sum('credit');
                if ($solde != 0) {
                    $accountsList[] = [
                        'code' => str_pad($acc->code_compte, 9, '0', STR_PAD_RIGHT),
                        'libelle' => $acc->libelle,
                        'montant' => $solde
                    ];
                    $groupTotal += $solde;
                }
            }
            if (!empty($accountsList)) {
                $data['charges']['groups'][$prefix] = [
                    'prefix' => str_pad($prefix, 6, '0', STR_PAD_RIGHT),
                    'total' => $groupTotal,
                    'accounts' => $accountsList
                ];
                $data['charges']['total'] += $groupTotal;
            }
        }

        $produitAccounts = $accounts->filter(function($acc) {
            if ($acc->classe == 7) return true;
            if ($acc->classe == 8 && in_array(substr($acc->code_compte, 0, 2), ['82', '84', '86', '88'])) return true;
            return false;
        });
        
        $groupedProduits = $produitAccounts->groupBy(function($acc) {
            return substr(str_pad($acc->code_compte, 2, '0', STR_PAD_RIGHT), 0, 2);
        })->sortKeys();

        foreach ($groupedProduits as $prefix => $accs) {
            $groupTotal = 0;
            $accountsList = [];
            foreach ($accs as $acc) {
                $solde = $acc->entryLines->sum('credit') - $acc->entryLines->sum('debit');
                if ($solde != 0) {
                    $accountsList[] = [
                        'code' => str_pad($acc->code_compte, 9, '0', STR_PAD_RIGHT),
                        'libelle' => $acc->libelle,
                        'montant' => $solde
                    ];
                    $groupTotal += $solde;
                }
            }
            if (!empty($accountsList)) {
                $data['produits']['groups'][$prefix] = [
                    'prefix' => str_pad($prefix, 6, '0', STR_PAD_RIGHT),
                    'total' => $groupTotal,
                    'accounts' => $accountsList
                ];
                $data['produits']['total'] += $groupTotal;
            }
        }

        $profit = $data['produits']['total'] - $data['charges']['total'];

        return view('accounting.resultat', [
            'charges' => $data['charges'],
            'produits' => $data['produits'],
            'totalCharges' => $data['charges']['total'],
            'totalProduits' => $data['produits']['total'],
            'profit' => $profit
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::with(['lines.account', 'journal'])
            ->where('entreprise_id', $user->entreprise_id)
            ->findOrFail($id);
        return view('accounting.journal.show', compact('entry'));
    }

    public function help()
    {
        return view('accounting.help');
    }
}

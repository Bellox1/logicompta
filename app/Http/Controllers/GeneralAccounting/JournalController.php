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
        
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $sort = strtolower($request->query('sort', 'date'));
        $order = strtolower($request->query('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $data = collect();

        // 1. Déterminer les IDs des comptes concernés
        $accountIdsQuery = Account::query();
        if ($mode === 'class' && $selectedClass) {
            $accountIdsQuery->where('classe', $selectedClass);
        } elseif ($mode === 'single' && $selectedAccount) {
            $accountIdsQuery->where('id', $selectedAccount->id);
        }
        $accountIds = $accountIdsQuery->pluck('id')->toArray();

        if (empty($accountIds) && ($mode !== 'all')) {
            $data = collect();
        } else {
            // 2. Construire la requête sur les lignes avec jointure explicite pour le tri
            $query = JournalEntryLine::with(['entry.journal', 'account'])
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.entreprise_id', $entrepriseId);

            if (!empty($accountIds)) {
                $query->whereIn('journal_entry_lines.account_id', $accountIds);
            }

            if ($startDate) $query->where('journal_entries.date', '>=', $startDate);
            if ($endDate) $query->where('journal_entries.date', '<=', $endDate);

            // 3. Application du tri SQL avec préfixes de table explicites
            if ($sort === 'numero_piece') {
                $query->orderBy('journal_entries.numero_piece', $order)
                      ->orderBy('journal_entries.date', $order);
            } else {
                // Par défaut, tri par date
                $query->orderBy('journal_entries.date', $order)
                      ->orderBy('journal_entries.numero_piece', $order);
            }
            
            // Toujours ajouter un tri par ID pour la stabilité
            $query->orderBy('journal_entry_lines.id', $order);

            // Charger les résultats (Select lines.* pour éviter les conflits d'ID de jointure)
            $allLines = $query->select('journal_entry_lines.*')->get();

            // 4. Regrouper par compte pour la vue
            $data = $allLines->groupBy('account_id')->map(function($accLines) {
                if ($accLines->isEmpty()) return null;
                $account = $accLines->first()->account;
                // On attache la collection déjà triée par le SQL
                $account->setRelation('entryLines', $accLines);
                return $account;
            })->filter()->sortBy('code_compte')->values();
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

    public function balance(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // On ne récupère que les comptes qui ont des mouvements pour cette entreprise sur la période
        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId, $startDate, $endDate) {
            $q->whereHas('entry', function($qe) use ($entrepriseId, $startDate, $endDate) {
                $qe->where('entreprise_id', $entrepriseId);
                if ($startDate) $qe->where('date', '>=', $startDate);
                if ($endDate) $qe->where('date', '<=', $endDate);
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

    public function bilan(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId, $startDate, $endDate) {
            $q->whereHas('entry', function($qe) use ($entrepriseId, $startDate, $endDate) {
                $qe->where('entreprise_id', $entrepriseId);
                if ($startDate) $qe->where('date', '>=', $startDate);
                if ($endDate) $qe->where('date', '<=', $endDate);
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

    public function resultat(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId, $startDate, $endDate) {
            $q->whereHas('entry', function($qe) use ($entrepriseId, $startDate, $endDate) {
                $qe->where('entreprise_id', $entrepriseId);
                if ($startDate) $qe->where('date', '>=', $startDate);
                if ($endDate) $qe->where('date', '<=', $endDate);
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

    public function showPdf($id)
    {
        $user = Auth::user();
        $entry = JournalEntry::with(['lines.account', 'journal'])
            ->where('entreprise_id', $user->entreprise_id)
            ->findOrFail($id);
        return view('accounting.journal.show-pdf', compact('entry', 'user'));
    }

    public function help()
    {
        return view('accounting.help');
    }

    /* --- PDF METHODS --- */

    public function ledgerPdf(Request $request, $account_id = null)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) return redirect()->route('entreprise.setup');
        $entrepriseId = $user->entreprise_id;

        $selectedAccount = $account_id ? Account::find($account_id) : null;
        $mode = $request->query('mode', 'single');
        $selectedClass = $request->query('class');
        
        $data = [];
        if ($mode === 'all') {
            $data = Account::with(['entryLines.entry.journal'])->whereHas('entryLines.entry', fn($q) => $q->where('entreprise_id', $entrepriseId))->orderBy('code_compte')->get();
        } elseif ($mode === 'class' && $selectedClass) {
            $data = Account::with(['entryLines.entry.journal'])->where('classe', $selectedClass)->whereHas('entryLines.entry', fn($q) => $q->where('entreprise_id', $entrepriseId))->orderBy('code_compte')->get();
        } elseif ($selectedAccount) {
            $lines = JournalEntryLine::with('entry.journal')->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')->where('journal_entry_lines.account_id', $selectedAccount->id)->where('journal_entries.entreprise_id', $entrepriseId)->orderBy('journal_entries.date', 'asc')->select('journal_entry_lines.*')->get();
            $data = json_decode(json_encode([['id' => $selectedAccount->id, 'code_compte' => $selectedAccount->code_compte, 'libelle' => $selectedAccount->libelle, 'entryLines' => $lines]]));
        }

        return view('accounting.ledger.pdf', compact('selectedAccount', 'data', 'mode', 'selectedClass', 'user'));
    }

    public function balancePdf()
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId) {
            $q->whereHas('entry', function($qe) use ($entrepriseId) {
                $qe->where('entreprise_id', $entrepriseId);
            });
        }])->get();
        
        $balanceData = [];
        $grandTotal = ['mouv_debit' => 0, 'mouv_credit' => 0, 'fin_debit' => 0, 'fin_credit' => 0];

        for ($c = 1; $c <= 9; $c++) {
            $classAccounts = $accounts->filter(fn($a) => $a->classe == $c);
            if ($classAccounts->isEmpty()) continue;
            $classData = ['label' => 'Total Classe ' . $c, 'groups' => [], 'class_totals' => ['mouv_debit' => 0, 'mouv_credit' => 0, 'fin_debit' => 0, 'fin_credit' => 0]];
            $groupedByPrefix = $classAccounts->groupBy(fn($acc) => substr(str_pad($acc->code_compte, 2, '0', STR_PAD_RIGHT), 0, 2))->sortKeys();
            foreach ($groupedByPrefix as $prefix => $accs) {
                $groupData = ['prefix' => $prefix, 'accounts' => [], 'group_totals' => ['mouv_debit' => 0, 'mouv_credit' => 0, 'fin_debit' => 0, 'fin_credit' => 0]];
                foreach ($accs as $account) {
                    $mouv_debit = $account->entryLines->sum('debit');
                    $mouv_credit = $account->entryLines->sum('credit');
                    if ($mouv_debit == 0 && $mouv_credit == 0) continue;
                    $solde = $mouv_debit - $mouv_credit;
                    $accRow = ['code' => $account->code_compte, 'libelle' => $account->libelle, 'mouv_debit' => $mouv_debit, 'mouv_credit' => $mouv_credit, 'fin_debit' => $solde > 0 ? $solde : 0, 'fin_credit' => $solde < 0 ? abs($solde) : 0];
                    $groupData['accounts'][] = $accRow;
                    $groupData['group_totals']['mouv_debit'] += $mouv_debit;
                    $groupData['group_totals']['mouv_credit'] += $mouv_credit;
                    $groupData['group_totals']['fin_debit'] += $accRow['fin_debit'];
                    $groupData['group_totals']['fin_credit'] += $accRow['fin_credit'];
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
        return view('accounting.balance.pdf', compact('balanceData', 'grandTotal', 'user'));
    }

    public function bilanPdf()
    {
        $user = Auth::user();
        $entrepriseId = $user->entreprise_id;
        $accounts = Account::with(['entryLines' => fn($q) => $q->whereHas('entry', fn($qe) => $qe->where('entreprise_id', $entrepriseId))])->get();
        $actif = collect(); $passif = collect();
        foreach ($accounts as $acc) {
            $debit = $acc->entryLines->sum('debit'); $credit = $acc->entryLines->sum('credit'); $soldeDebit = $debit - $credit;
            if ($soldeDebit == 0) continue;
            if ($acc->classe == 2 || $acc->classe == 3) { $actif->push(['libelle' => $acc->libelle, 'solde' => $soldeDebit]); }
            elseif ($acc->classe == 1) { $passif->push(['libelle' => $acc->libelle, 'solde' => -$soldeDebit]); }
            elseif ($acc->classe == 4 || $acc->classe == 5) {
                if ($soldeDebit > 0) { $actif->push(['libelle' => $acc->libelle, 'solde' => $soldeDebit]); }
                else { $passif->push(['libelle' => $acc->libelle, 'solde' => abs($soldeDebit)]); }
            }
        }
        $totalCharges = $accounts->whereIn('classe', [6, 8])->sum(fn($a) => ($solde = $a->entryLines->sum('debit') - $a->entryLines->sum('credit')) > 0 ? $solde : 0);
        $totalProduits = $accounts->whereIn('classe', [7, 8])->sum(fn($a) => ($solde = $a->entryLines->sum('credit') - $a->entryLines->sum('debit')) > 0 ? $solde : 0);
        $resultatNet = $totalProduits - $totalCharges;
        $passif->push(['libelle' => $resultatNet >= 0 ? 'RÉSULTAT NET (BÉNÉFICE)' : 'RÉSULTAT NET (PERTE)', 'solde' => $resultatNet, 'is_resultat' => true]);
        return view('accounting.bilan.pdf', compact('actif', 'passif', 'user'));
    }

    public function resultatPdf()
    {
        $user = Auth::user(); $entrepriseId = $user->entreprise_id;
        $accounts = Account::with(['entryLines' => fn($q) => $q->whereHas('entry', fn($qe) => $qe->where('entreprise_id', $entrepriseId))])->get();
        $data = ['charges' => ['total' => 0, 'groups' => []], 'produits' => ['total' => 0, 'groups' => []]];
        
        $chargeAccounts = $accounts->filter(fn($acc) => $acc->classe == 6 || ($acc->classe == 8 && in_array(substr($acc->code_compte, 0, 2), ['81', '83', '85', '87', '89'])));
        foreach ($chargeAccounts->groupBy(fn($acc) => substr(str_pad($acc->code_compte, 2, '0', STR_PAD_RIGHT), 0, 2))->sortKeys() as $prefix => $accs) {
            $groupTotal = 0; $accountsList = [];
            foreach ($accs as $acc) {
                if (($solde = $acc->entryLines->sum('debit') - $acc->entryLines->sum('credit')) != 0) {
                    $accountsList[] = ['code' => $acc->code_compte, 'libelle' => $acc->libelle, 'montant' => $solde]; $groupTotal += $solde;
                }
            }
            if (!empty($accountsList)) { $data['charges']['groups'][$prefix] = ['prefix' => $prefix, 'total' => $groupTotal, 'accounts' => $accountsList]; $data['charges']['total'] += $groupTotal; }
        }

        $produitAccounts = $accounts->filter(fn($acc) => $acc->classe == 7 || ($acc->classe == 8 && in_array(substr($acc->code_compte, 0, 2), ['82', '84', '86', '88'])));
        foreach ($produitAccounts->groupBy(fn($acc) => substr(str_pad($acc->code_compte, 2, '0', STR_PAD_RIGHT), 0, 2))->sortKeys() as $prefix => $accs) {
            $groupTotal = 0; $accountsList = [];
            foreach ($accs as $acc) {
                if (($solde = $acc->entryLines->sum('credit') - $acc->entryLines->sum('debit')) != 0) {
                    $accountsList[] = ['code' => $acc->code_compte, 'libelle' => $acc->libelle, 'montant' => $solde]; $groupTotal += $solde;
                }
            }
            if (!empty($accountsList)) { $data['produits']['groups'][$prefix] = ['prefix' => $prefix, 'total' => $groupTotal, 'accounts' => $accountsList]; $data['produits']['total'] += $groupTotal; }
        }

        return view('accounting.resultat.pdf', [ 'charges' => $data['charges'], 'produits' => $data['produits'], 'profit' => $data['produits']['total'] - $data['charges']['total'], 'user' => $user ]);
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

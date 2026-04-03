<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrialBalanceController extends Controller
{
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
        // On récupère les comptes avec leurs lignes, incluant celles dont le sous-compte ou la ligne est supprimé(e)
        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId, $startDate, $endDate, $request) {
            $q->withTrashed()->whereHas('entry', function($qe) use ($entrepriseId, $startDate, $endDate, $request) {
                $qe->withTrashed()->where('entreprise_id', $entrepriseId);
                $showArchived = $request->query('show_archived', '0');
                if ($showArchived === '1') {
                    $qe->where('is_archived', '=', true);
                } elseif ($showArchived !== 'all') {
                    $qe->where('is_archived', '=', false);
                }
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

    public function balancePdf(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $accounts = Account::with(['entryLines' => function($q) use ($entrepriseId, $startDate, $endDate, $request) {
            $q->withTrashed()->whereHas('entry', function($qe) use ($entrepriseId, $startDate, $endDate, $request) {
                $qe->withTrashed()->where('entreprise_id', $entrepriseId);
                $showArchived = $request->query('show_archived', '0');
                if ($showArchived === '1') {
                    $qe->where('is_archived', '=', true);
                } elseif ($showArchived !== 'all') {
                    $qe->where('is_archived', '=', false);
                }
                if ($startDate) $qe->where('date', '>=', $startDate);
                if ($endDate) $qe->where('date', '<=', $endDate);
            });
        }])->get();

        $balanceData = [];
        $grandTotal = ['mouv_debit' => 0, 'mouv_credit' => 0, 'fin_debit' => 0, 'fin_credit' => 0];

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
        
        return view('accounting.balance.pdf', compact('balanceData', 'grandTotal', 'user', 'startDate', 'endDate'));
    }
}

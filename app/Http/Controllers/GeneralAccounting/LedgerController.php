<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\Account;
use App\Models\GeneralAccounting\JournalEntryLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LedgerController extends Controller
{
    public function ledger(Request $request, $account_id = null)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;

        $accounts = Account::orderBy('code_compte', 'asc')->get()->groupBy('classe');
        $selectedAccount = $account_id ? Account::find($account_id, ['*']) : null;
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

    public function ledgerPdf(Request $request, $account_id = null)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) {
            return redirect()->route('entreprise.setup');
        }
        $entrepriseId = $user->entreprise_id;

        $selectedAccount = $account_id ? Account::find($account_id) : null;
        $selectedClass = $request->query('class');
        $mode = $request->query('mode', 'single');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

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
            $query = JournalEntryLine::with(['entry.journal', 'account'])
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.entreprise_id', $entrepriseId);

            if (!empty($accountIds)) {
                $query->whereIn('journal_entry_lines.account_id', $accountIds);
            }
            if ($startDate) $query->where('journal_entries.date', '>=', $startDate);
            if ($endDate) $query->where('journal_entries.date', '<=', $endDate);

            $query->orderBy('journal_entries.date', 'asc')
                  ->orderBy('journal_entries.numero_piece', 'asc')
                  ->orderBy('journal_entry_lines.id', 'asc');

            $allLines = $query->select('journal_entry_lines.*')->get();
            $data = $allLines->groupBy('account_id')->map(function($accLines) {
                $account = $accLines->first()->account;
                $account->setRelation('entryLines', $accLines);
                return $account;
            })->sortBy('code_compte')->values();
        }

        return view('accounting.ledger.pdf', compact('data', 'user', 'selectedAccount', 'selectedClass', 'mode'));
    }
}

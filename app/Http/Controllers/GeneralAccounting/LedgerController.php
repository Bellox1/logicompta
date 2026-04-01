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
            $query = JournalEntryLine::with(['entry.journal', 'sousCompte.account'])
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->join('sous_comptes', 'journal_entry_lines.sous_compte_id', '=', 'sous_comptes.id')
                ->where('journal_entries.entreprise_id', $entrepriseId);

            $showArchived = $request->query('show_archived', '0');
            if ($showArchived === '1') {
                $query->where('journal_entries.is_archived', '=', true);
            } elseif ($showArchived !== 'all') {
                $query->where('journal_entries.is_archived', '=', false);
            }

            if (!empty($accountIds)) {
                $query->whereIn('sous_comptes.account_id', $accountIds);
            }

            if ($startDate) $query->where('journal_entries.date', '>=', $startDate);
            if ($endDate) $query->where('journal_entries.date', '<=', $endDate);

            // 3. Application du tri
            if ($sort === 'numero_piece') {
                $query->orderBy('journal_entries.numero_piece', $order)
                      ->orderBy('journal_entries.date', $order);
            } else {
                $query->orderBy('journal_entries.date', $order)
                      ->orderBy('journal_entries.numero_piece', $order);
            }
            
            $query->orderBy('journal_entry_lines.id', $order);

            $allLines = $query->select('journal_entry_lines.*')->get();

            // 4. Regrouper par compte général (via le sous-compte)
            $data = $allLines->groupBy(function($line) {
                return $line->sousCompte->account_id;
            })->map(function($accLines) {
                if ($accLines->isEmpty()) return null;
                $account = $accLines->first()->sousCompte->account;
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
            $query = JournalEntryLine::with(['entry.journal', 'sousCompte.account'])
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->join('sous_comptes', 'journal_entry_lines.sous_compte_id', '=', 'sous_comptes.id')
                ->where('journal_entries.entreprise_id', $entrepriseId);

            $showArchived = $request->query('show_archived', '0');
            if ($showArchived === '1') {
                $query->where('journal_entries.is_archived', '=', true);
            } elseif ($showArchived !== 'all') {
                $query->where('journal_entries.is_archived', '=', false);
            }

            if (!empty($accountIds)) {
                $query->whereIn('sous_comptes.account_id', $accountIds);
            }
            if ($startDate) $query->where('journal_entries.date', '>=', $startDate);
            if ($endDate) $query->where('journal_entries.date', '<=', $endDate);

            $query->orderBy('journal_entries.date', 'asc')
                  ->orderBy('journal_entries.numero_piece', 'asc')
                  ->orderBy('journal_entry_lines.id', 'asc');

            $allLines = $query->select('journal_entry_lines.*')->get();
            $data = $allLines->groupBy(function($line) {
                return $line->sousCompte->account_id;
            })->map(function($accLines) {
                $account = $accLines->first()->sousCompte->account;
                $account->setRelation('entryLines', $accLines);
                return $account;
            })->sortBy('code_compte')->values();
        }

        return view('accounting.ledger.pdf', compact('data', 'user', 'selectedAccount', 'selectedClass', 'mode'));
    }
}

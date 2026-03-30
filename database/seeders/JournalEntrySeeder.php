<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneralAccounting\Journal;
use App\Models\GeneralAccounting\JournalEntry;
use App\Models\GeneralAccounting\JournalEntryLine;
use App\Models\GeneralAccounting\Account;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        // On récupère ou crée un journal par défaut si besoin (ex: Journal Général)
        $journal = Journal::first() ?? Journal::create([
            'name' => 'Journal Général',
            'description' => 'Journal par défaut'
        ]);

        // On s'assure d'avoir quelques comptes pour le débit/crédit (SYSCOHADA)
        $bank = Account::firstOrCreate(['code_compte' => '521'], ['classe' => 5, 'libelle' => 'Banque']);
        $capital = Account::firstOrCreate(['code_compte' => '101'], ['classe' => 1, 'libelle' => 'Capital']);
        $expense = Account::firstOrCreate(['code_compte' => '601'], ['classe' => 6, 'libelle' => 'Achats de marchandises']);
        $revenue = Account::firstOrCreate(['code_compte' => '701'], ['classe' => 7, 'libelle' => 'Vente de marchandises']);

        $years = [2022, 2023, 2024, 2025, 2026];
        $pieceCounter = 1000;

        foreach ($years as $year) {
            $is_archived = ($year < 2026); // Tout ce qui est avant l'année en cours est archivé

            // --- Écriture 1: Apport de capital ---
            $entry = JournalEntry::create([
                'journal_id' => $journal->id,
                'piece_number' => 'PC-' . $year . '-' . ($pieceCounter++),
                'date' => "$year-01-05",
                'libelle' => "Constitution capital $year",
                'total_debit' => 10000000,
                'total_credit' => 10000000,
                'is_archived' => $is_archived
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $bank->id,
                'debit' => 10000000,
                'credit' => 0,
                'libelle' => 'Versement banque'
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $capital->id,
                'debit' => 0,
                'credit' => 10000000,
                'libelle' => 'Dotation capital initiale'
            ]);

            // --- Écriture 2: Ventes / Recettes ---
            $entry2 = JournalEntry::create([
                'journal_id' => $journal->id,
                'piece_number' => 'PC-' . $year . '-' . ($pieceCounter++),
                'date' => "$year-06-15",
                'libelle' => "Ventes périodiques $year",
                'total_debit' => 2500000,
                'total_credit' => 2500000,
                'is_archived' => $is_archived
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry2->id,
                'account_id' => $bank->id,
                'debit' => 2500000,
                'credit' => 0,
                'libelle' => 'Encaissement ventes'
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry2->id,
                'account_id' => $revenue->id,
                'debit' => 0,
                'credit' => 2500000,
                'libelle' => 'CA annuel'
            ]);

            // --- Écriture 3: Achats / Charges ---
            $entry3 = JournalEntry::create([
                'journal_id' => $journal->id,
                'piece_number' => 'PC-' . $year . '-' . ($pieceCounter++),
                'date' => "$year-10-20",
                'libelle' => "Achats de marchandises $year",
                'total_debit' => 1200000,
                'total_credit' => 1200000,
                'is_archived' => $is_archived
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry3->id,
                'account_id' => $expense->id,
                'debit' => 1200000,
                'credit' => 0,
                'libelle' => 'Facture fournisseur X'
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry3->id,
                'account_id' => $bank->id,
                'debit' => 0,
                'credit' => 1200000,
                'libelle' => 'Règlement par chèque'
            ]);
        }
    }
}

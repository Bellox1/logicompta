<?php

namespace Database\Seeders;

use App\Models\GeneralAccounting\Journal;
use App\Models\GeneralAccounting\JournalEntry;
use App\Models\GeneralAccounting\JournalEntryLine;
use App\Models\SousCompte;
use App\Models\Entreprise;
use Illuminate\Database\Seeder;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        $entreprise = Entreprise::first();
        if (!$entreprise) return;

        $journal = Journal::first() ?? Journal::create([
            'name' => 'Journal Général',
            'description' => 'Journal par défaut',
            'entreprise_id' => $entreprise->id
        ]);

        $bank = SousCompte::where('numero_sous_compte', '521001')->first();
        $capital = SousCompte::where('numero_sous_compte', '101000')->first();
        $expense = SousCompte::where('numero_sous_compte', '601000')->first();
        $revenue = SousCompte::where('numero_sous_compte', '701000')->first();

        if (!$bank || !$capital || !$expense || !$revenue) return;

        $years = [2025, 2026];
        $pieceCounter = 1;

        foreach ($years as $year) {
            $is_archived = ($year < 2026);

            // Apport
            $entry = JournalEntry::create([
                'journal_id' => $journal->id,
                'entreprise_id' => $entreprise->id,
                'numero_piece' => str_pad($pieceCounter++, 6, '0', STR_PAD_LEFT),
                'date' => "$year-01-05",
                'libelle' => "Constitution capital $year",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'sous_compte_id' => $bank->id,
                'debit' => 10000000,
                'credit' => 0,
                'libelle' => 'Versement banque BOA'
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'sous_compte_id' => $capital->id,
                'debit' => 0,
                'credit' => 10000000,
                'libelle' => 'Dotation capital'
            ]);

            // Vente
            $entry2 = JournalEntry::create([
                'journal_id' => $journal->id,
                'entreprise_id' => $entreprise->id,
                'numero_piece' => str_pad($pieceCounter++, 6, '0', STR_PAD_LEFT),
                'date' => "$year-06-15",
                'libelle' => "Ventes périodiques $year",
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry2->id,
                'sous_compte_id' => $bank->id,
                'debit' => 2500000,
                'credit' => 0,
                'libelle' => 'Encaissement ventes'
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry2->id,
                'sous_compte_id' => $revenue->id,
                'debit' => 0,
                'credit' => 2500000,
                'libelle' => 'CA annuel'
            ]);
        }
    }
}

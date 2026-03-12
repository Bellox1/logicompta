<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneralAccounting\Journal;
use App\Models\GeneralAccounting\JournalEntry;
use App\Models\GeneralAccounting\JournalEntryLine;
use App\Models\GeneralAccounting\Account;

class JournalEntrySeeder extends Seeder
{
    private int $counter = 1;

    public function run(): void
    {
        $CAI = Journal::where('code', 'CAI')->first();
        $BNQ = Journal::where('code', 'BNQ')->first();
        $OD  = Journal::where('code', 'OD')->first();
        
        // Récupérer l'entreprise par défaut
        $entreprise = \App\Models\Entreprise::first();
        if (!$entreprise) {
            $this->command->error("Aucune entreprise trouvée. Veuillez exécuter EntrepriseSeeder d'abord.");
            return;
        }

        // CLASSE 1 & 5 — Apport en capital
        $this->entry($BNQ, '2026-01-02', 'Apport initial en capital', [
            ['code' => '52',  'D' => 5000000, 'C' => 0],
            ['code' => '101', 'D' => 0, 'C' => 5000000],
        ]);

        // CLASSE 1 — Emprunt bancaire
        $this->entry($BNQ, '2026-01-05', 'Emprunt bancaire reçu', [
            ['code' => '52', 'D' => 2000000, 'C' => 0],
            ['code' => '16', 'D' => 0, 'C' => 2000000],
        ]);

        // CLASSE 2 & 5 — Achat matériel
        $this->entry($BNQ, '2026-01-10', 'Achat matériel informatique', [
            ['code' => '24', 'D' => 600000, 'C' => 0],
            ['code' => '52', 'D' => 0, 'C' => 600000],
        ]);

        // CLASSE 3 & 4 — Achat stock marchandises
        $this->entry($OD, '2026-01-15', 'Achat marchandises fournisseur', [
            ['code' => '31', 'D' => 500000, 'C' => 0],
            ['code' => '40', 'D' => 0, 'C' => 500000],
        ]);

        // CLASSE 4 & 5 — Paiement fournisseur
        $this->entry($BNQ, '2026-01-20', 'Règlement fournisseur FACT-001', [
            ['code' => '40', 'D' => 500000, 'C' => 0],
            ['code' => '52', 'D' => 0, 'C' => 500000],
        ]);

        // CLASSE 4 & 7 — Vente à crédit client
        $this->entry($OD, '2026-02-01', 'Vente marchandises à crédit', [
            ['code' => '41', 'D' => 800000, 'C' => 0],
            ['code' => '70', 'D' => 0, 'C' => 800000],
        ]);

        // CLASSE 5 — Encaissement client
        $this->entry($BNQ, '2026-02-10', 'Encaissement client FACT-002', [
            ['code' => '52', 'D' => 800000, 'C' => 0],
            ['code' => '41', 'D' => 0, 'C' => 800000],
        ]);

        // CLASSE 6 & 5 — Charges de personnel
        $this->entry($BNQ, '2026-02-25', 'Salaires février 2026', [
            ['code' => '66', 'D' => 900000, 'C' => 0],
            ['code' => '52', 'D' => 0, 'C' => 900000],
        ]);

        // CLASSE 6 & 5 — Loyer
        $this->entry($CAI, '2026-02-28', 'Loyer local commercial', [
            ['code' => '62', 'D' => 150000, 'C' => 0],
            ['code' => '57', 'D' => 0, 'C' => 150000],
        ]);

        // CLASSE 7 & 5 — Vente comptant
        $this->entry($CAI, '2026-03-05', 'Ventes comptoir semaine S10', [
            ['code' => '57', 'D' => 350000, 'C' => 0],
            ['code' => '70', 'D' => 0, 'C' => 350000],
        ]);

        // CLASSE 7 — Subvention d'exploitation
        $this->entry($BNQ, '2026-03-08', 'Réception subvention État', [
            ['code' => '52', 'D' => 200000, 'C' => 0],
            ['code' => '71', 'D' => 0, 'C' => 200000],
        ]);

        // CLASSE 8 — Cession matériel (HAO)
        $this->entry($BNQ, '2026-03-10', 'Cession ancien véhicule', [
            ['code' => '52', 'D' => 250000, 'C' => 0],
            ['code' => '82', 'D' => 0, 'C' => 250000],
        ]);

        // CLASSE 9 — Engagement analytique
        $this->entry($OD, '2026-03-15', 'Engagement commandes fournisseur', [
            ['code' => '9073', 'D' => 100000, 'C' => 0],
            ['code' => '9078', 'D' => 0, 'C' => 100000],
        ]);

        $this->command->info("✅ " . ($this->counter - 1) . " écritures créées pour toutes les classes (1 à 9) !");
    }

    private function entry($journal, string $date, string $libelle, array $lines): void
    {
        if (!$journal) return;
        
        // Récupérer l'entreprise par défaut
        $entreprise = \App\Models\Entreprise::first();
        if (!$entreprise) return;

        $num = 'SD-' . str_pad($this->counter, 5, '0', STR_PAD_LEFT);
        $this->counter++;

        $entry = JournalEntry::create([
            'journal_id'    => $journal->id,
            'numero_piece'  => $num,
            'date'          => $date,
            'libelle'       => $libelle,
            'entreprise_id' => $entreprise->id,
        ]);

        foreach ($lines as $line) {
            $account = Account::where('code_compte', $line['code'])->first();
            if ($account) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $account->id,
                    'debit'            => $line['D'],
                    'credit'           => $line['C'],
                    'libelle'          => $libelle,
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GeneralAccounting\Journal;

class JournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $journals = [
            ['name' => 'Journal de Caisse', 'description' => 'Opérations en espèces'],
            ['name' => 'Journal de Banque', 'description' => 'Opérations bancaires'],
            ['name' => 'Journal de Paie', 'description' => 'Enregistrement de la paie'],
            ['name' => 'Journal des Achats', 'description' => 'Enregistrement des factures fournisseurs'],
            ['name' => 'Journal des Ventes', 'description' => 'Enregistrement des factures clients'],
            ['name' => 'Journal des prestations de services', 'description' => 'Enregistrement des prestations effectuées'],
            ['name' => 'Journal des salaires', 'description' => 'Enregistrement des salaires'],
            ['name' => 'Journal des Opérations diverses', 'description' => 'Écritures de régularisation et autres'],
        ];

        foreach ($journals as $journal) {
            Journal::updateOrCreate(['name' => $journal['name']], $journal);
        }

        $this->command->info(count($journals) . " journaux configurés conformément au cahier des charges !");
    }
}

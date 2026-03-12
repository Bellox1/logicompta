<?php

namespace Database\Seeders;

use App\Models\Entreprise;
use Illuminate\Database\Seeder;

class EntrepriseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Entreprise::updateOrCreate(
            ['code' => 'LOGICOMPTA01'],
            ['name' => 'Logicompta SARL']
        );

        $this->command->info("Entreprise par défaut créée !");
    }
}

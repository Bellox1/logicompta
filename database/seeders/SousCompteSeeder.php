<?php

namespace Database\Seeders;

use App\Models\SousCompte;
use App\Models\Entreprise;
use App\Models\GeneralAccounting\Account;
use Illuminate\Database\Seeder;

class SousCompteSeeder extends Seeder
{
    public function run(): void
    {
        $entreprise = Entreprise::first(['*']);
        if (!$entreprise) return;

        $accounts = [
            '521' => ['521001' => 'BOA', '521002' => 'Ecobank'],
            '101' => ['101000' => 'Capital Social'],
            '601' => ['601000' => 'Achats Merch'],
            '701' => ['701000' => 'Ventes Merch'],
        ];

        foreach ($accounts as $code => $subs) {
            $parent = Account::where('code_compte', '=', $code, 'and')->first(['*']);
            if (!$parent) continue;

            foreach ($subs as $num => $libelle) {
                SousCompte::create([
                    'entreprise_id' => $entreprise->id,
                    'account_id' => $parent->id,
                    'numero_sous_compte' => $num,
                    'libelle' => $libelle,
                ]);
            }
        }
    }
}

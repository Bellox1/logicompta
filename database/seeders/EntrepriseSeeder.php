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
        $name = 'LOGICOMPTA TEST';
        Entreprise::create([
            'name' => $name,
            'code' => Entreprise::generateCode($name),
        ]);
    }
}

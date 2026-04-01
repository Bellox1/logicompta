<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Appel des seeders comptables
        $this->call([
            AccountSeeder::class,
            EntrepriseSeeder::class,
            UserSeeder::class,
            JournalSeeder::class,
            SousCompteSeeder::class,
            JournalEntrySeeder::class,
        ]);
    }
}

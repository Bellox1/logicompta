<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Bellox Admin',
            'email' => 'admin@COMPTAFIQ.bj',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'entreprise_id' => \App\Models\Entreprise::first(['*'])?->id,
        ]);
    }
}

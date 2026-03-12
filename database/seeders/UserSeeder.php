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
        // Création de l'administrateur
        User::updateOrCreate(
            ['email' => 'admin@logicompta.com'],
            [
                'name' => 'Admin',
                'prenom' => 'Logicompta',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );
        //création de comptable
        User::updateOrCreate(
            ['email' => 'comptable@logicompta.com'],
            [
                'name' => 'Comptable',
                'prenom' => 'Logicompta',
                'password' => Hash::make('password'),
                'role' => 'comptable',
            ]
        );


        // Création d'un utilisateur standard
        User::updateOrCreate(
            ['email' => 'user@logicompta.com'],
            [
                'name' => 'Utilisateur',
                'prenom' => 'Test',
                'password' => Hash::make('password'),
                'role' => 'utilisateur',
            ]
        );

        $this->command->info("Utilisateurs (Admin, Comptable, Utilisateur) créés avec succès !");
    }
}

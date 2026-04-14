<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Désactiver temporairement les contraintes pour éviter l'erreur MySQL 1553 sur AlwaysData
        Schema::disableForeignKeyConstraints();

        Schema::table('journal_entries', function (Blueprint $table) {
            // Supprimer l'ancienne contrainte trop restrictive
            $table->dropUnique(['entreprise_id', 'numero_piece']);
            
            // Ajouter la nouvelle contrainte incluant le journal
            $table->unique(['entreprise_id', 'journal_id', 'numero_piece'], 'journal_entries_entreprise_journal_piece_unique');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique('journal_entries_entreprise_journal_piece_unique');
            $table->unique(['entreprise_id', 'numero_piece']);
        });

        Schema::enableForeignKeyConstraints();
    }
};

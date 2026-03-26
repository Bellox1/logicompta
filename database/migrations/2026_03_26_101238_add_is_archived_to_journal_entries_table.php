<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute le champ is_archived pour gérer l'archivage annuel automatique
     * des écritures de journal (déclenché le 30 décembre de chaque année).
     */
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            // false = écriture de l'exercice en cours, true = archivée
            $table->boolean('is_archived')->default(false)->after('libelle');
            // Date/heure à laquelle l'archivage a été effectué
            $table->timestamp('archived_at')->nullable()->after('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['is_archived', 'archived_at']);
        });
    }
};

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
        Schema::table('journal_entries', function (Blueprint $table) {
            // Drop current global unique
            try {
                $table->dropUnique(['numero_piece']);
            } catch (\Exception $e) {
                // If it fails (some drivers), we just continue
            }

            // Create scoped unique
            $table->unique(['entreprise_id', 'numero_piece']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropUnique(['entreprise_id', 'numero_piece']);
            $table->unique(['numero_piece']);
        });
    }
};

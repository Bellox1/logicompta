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
        // Vérifier si la table existe déjà
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('email')->index();
                $table->string('token')->unique();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('password_reset_tokens')) {
            Schema::dropIfExists('password_reset_tokens');
        }
    }
};

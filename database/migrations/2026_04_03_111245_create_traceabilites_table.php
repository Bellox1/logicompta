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
        Schema::create('traceabilites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('entreprise_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('model_type'); // Ex: App\Models\JournalEntry
            $table->unsignedBigInteger('model_id');
            $table->string('action')->default('DELETE'); // DELETE, RESTORE
            $table->json('details')->nullable(); // Copie des données au moment de l'action
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traceabilites');
    }
};

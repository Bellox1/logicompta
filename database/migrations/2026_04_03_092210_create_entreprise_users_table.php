<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entreprise_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('entreprise_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('utilisateur'); // Admin, comptable, etc. spécifique à cette entreprise
            $table->timestamps();
        });

        // Transférer les données existantes avant de supprimer les colonnes
        $columnName = Schema::hasColumn('users', 'active_entreprise_id') ? 'active_entreprise_id' : 'entreprise_id';
        
        $users = DB::table('users')->whereNotNull($columnName)->get();
        foreach ($users as $user) {
            DB::table('entreprise_users')->insert([
                'user_id' => $user->id,
                'entreprise_id' => $user->$columnName,
                'role' => $user->role ?? 'utilisateur',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Supprimer les colonnes entreprise_id/active_entreprise_id et role de la table users
        Schema::table('users', function (Blueprint $table) use ($columnName) {
            $table->dropColumn([$columnName, 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('entreprise_id')->nullable();
            $table->string('role')->default('utilisateur');
        });

        Schema::dropIfExists('entreprise_users');
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entreprise extends Model
{
    use HasFactory, \App\Traits\AuditTraceable;

    protected $fillable = ['name', 'code'];

    /**
     * Génère un code unique pour une entreprise
     */
    public static function generateCode(string $name): string
    {
        // Génération d'un code aléatoire sécurisé sans lien avec le nom de l'entreprise
        return 'SOC-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    /**
     * Utilisateurs de l'entreprise
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'entreprise_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function journals()
    {
        return $this->hasMany(\App\Models\GeneralAccounting\Journal::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(\App\Models\GeneralAccounting\JournalEntry::class);
    }
}

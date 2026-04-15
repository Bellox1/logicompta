<?php

namespace App\Models\GeneralAccounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToEntreprise;

class Journal extends Model
{
    use BelongsToEntreprise, \App\Traits\AuditTraceable;

    protected $fillable = ['name', 'description', 'entreprise_id'];

    /**
     * Boot the model and handle cascading soft deletes
     */
    protected static function boot()
    {
        parent::boot();
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'journal_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Crée les journaux par défaut pour une entreprise
     */
    public static function createDefaultJournals($entrepriseId)
    {
        $journals = [
            ['name' => 'Journal de Caisse', 'description' => 'Opérations en espèces'],
            ['name' => 'Journal de Banque', 'description' => 'Opérations bancaires'],
            ['name' => 'Journal de Paie', 'description' => 'Enregistrement de la paie'],
            ['name' => 'Journal des Achats', 'description' => 'Enregistrement des factures fournisseurs'],
            ['name' => 'Journal des Ventes', 'description' => 'Enregistrement des factures clients'],
            ['name' => 'Journal des prestations de services', 'description' => 'Enregistrement des prestations effectuées'],
            ['name' => 'Journal des salaires', 'description' => 'Enregistrement des salaires'],
            ['name' => 'Journal des Opérations diverses', 'description' => 'Écritures de régularisation et autres'],
        ];

        foreach ($journals as $data) {
            self::create([
                'name'          => $data['name'],
                'description'   => $data['description'],
                'entreprise_id' => $entrepriseId
            ]);
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToEntreprise;

class SousCompte extends Model
{
    use BelongsToEntreprise, \App\Traits\AuditTraceable;

    protected $fillable = ['entreprise_id', 'account_id', 'numero_sous_compte', 'libelle'];

    /**
     * Boot the model and handle cascading soft deletes
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($sousCompte) {
            if (!$sousCompte->isForceDeleting()) {
                // Supprimer les lignes (déclenche l'AuditTraceable de chaque ligne)
                // Attention: en comptabilité c'est risqué, mais on suit la demande de traçabilité totale.
                $sousCompte->entryLines()->get()->each->delete();
            }
        });

        static::restoring(function ($sousCompte) {
            $sousCompte->entryLines()->withTrashed()->get()->each->restore();
        });
    }

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\GeneralAccounting\Account::class, 'account_id');
    }

    public function entryLines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\GeneralAccounting\JournalEntryLine::class, 'sous_compte_id');
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }
}

<?php

namespace App\Models\GeneralAccounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToEntreprise;

class JournalEntry extends Model
{
    use BelongsToEntreprise, \App\Traits\AuditTraceable;

    protected $fillable = ['journal_id', 'numero_piece', 'date', 'libelle', 'entreprise_id', 'is_archived', 'archived_at'];

    /**
     * Boot the model and handle cascading soft deletes
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($entry) {
            if (!$entry->isForceDeleting()) {
                // Supprimer les lignes (déclenche l'AuditTraceable de chaque ligne)
                $entry->lines()->get()->each->delete();
            }
        });

        static::restoring(function ($entry) {
            // Restaurer les lignes si on restaure l'écriture
            $entry->lines()->withTrashed()->get()->each->restore();
        });
    }

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Entreprise::class);
    }
}

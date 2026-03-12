<?php

namespace App\Models\GeneralAccounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = ['journal_id', 'numero_piece', 'date', 'libelle', 'entreprise_id'];

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Entreprise::class);
    }
}

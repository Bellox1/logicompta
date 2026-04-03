<?php

namespace App\Models\GeneralAccounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use \App\Traits\AuditTraceable;
    protected $fillable = ['journal_entry_id', 'sous_compte_id', 'debit', 'credit', 'libelle'];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function sousCompte(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SousCompte::class, 'sous_compte_id');
    }

    // Helper pour accéder au compte général via le sous-compte
    public function getAccountAttribute()
    {
        return $this->sousCompte?->account;
    }
}

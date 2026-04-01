<?php

namespace App\Models\GeneralAccounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = ['classe', 'code_compte', 'libelle'];

    public function entryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function sousComptes(): HasMany
    {
        return $this->hasMany(\App\Models\SousCompte::class, 'account_id');
    }
}

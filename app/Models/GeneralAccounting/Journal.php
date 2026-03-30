<?php

namespace App\Models\GeneralAccounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journal extends Model
{
    protected $fillable = ['name', 'description', 'entreprise_id'];

    public function entries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
}

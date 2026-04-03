<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToEntreprise;

class SousCompte extends Model
{
    use BelongsToEntreprise;

    protected $fillable = ['entreprise_id', 'account_id', 'numero_sous_compte', 'libelle'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Models\GeneralAccounting\Account::class, 'account_id');
    }

    public function entreprise(): BelongsTo
    {
        return $this->belongsTo(Entreprise::class);
    }
}

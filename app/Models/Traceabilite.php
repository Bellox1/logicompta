<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traceabilite extends Model
{
    protected $fillable = [
        'user_id',
        'entreprise_id',
        'model_type',
        'model_id',
        'action',
        'details',
        'ip_address'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}

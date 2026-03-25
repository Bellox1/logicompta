<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'email',
        'token',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Vérifier si le token est encore valide
     */
    public function isValid()
    {
        return !$this->isExpired();
    }

    /**
     * Vérifier si le token a expiré
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}

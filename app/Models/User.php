<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, \App\Traits\AuditTraceable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Les entreprises auxquelles appartient l'utilisateur
     */
    public function entreprises()
    {
        return $this->belongsToMany(Entreprise::class, 'entreprise_users')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Accesseur pour l'entreprise active (rétrocompatibilité)
     */
    public function getEntrepriseIdAttribute()
    {
        return session('active_entreprise_id') ?? $this->entreprises()->first()?->id;
    }

    public function getEntrepriseAttribute()
    {
        $id = $this->entreprise_id;
        return $id ? Entreprise::find($id, ['*']) : null;
    }

    /**
     * Accesseur pour le rôle dans l'entreprise active (rétrocompatibilité)
     */
    public function getRoleAttribute()
    {
        $entreId = $this->entreprise_id;
        if (!$entreId) return 'utilisateur';
        
        $pivot = $this->entreprises()->where('entreprise_id', '=', $entreId)->first()?->pivot;
        return $pivot ? $pivot->role : 'utilisateur';
    }
}

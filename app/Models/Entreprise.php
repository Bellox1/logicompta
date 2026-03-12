<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code'];

    /**
     * Génère un code unique pour une entreprise
     */
    public static function generateCode(string $name): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 6));
        $suffix = strtoupper(substr(md5(uniqid()), 0, 4));
        return $prefix . '-' . $suffix;
    }

    /**
     * Utilisateurs de l'entreprise
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}

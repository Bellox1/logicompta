<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToEntreprise
{
    protected static function bootBelongsToEntreprise()
    {
        static::addGlobalScope('entreprise', function (Builder $builder) {
            $entrepriseId = session('active_entreprise_id');
            if ($entrepriseId) {
                $builder->where('entreprise_id', $entrepriseId);
            }
        });

        static::creating(function ($model) {
            if (!$model->entreprise_id) {
                $model->entreprise_id = session('active_entreprise_id');
            }
        });
    }
}

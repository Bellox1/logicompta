<?php

namespace App\Traits;

use App\Models\Traceabilite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;

trait AuditTraceable
{
    use SoftDeletes;

    public static function bootAuditTraceable()
    {
        static::deleted(function ($model) {
            // Si c'est une suppression définitive (forceDelete), on peut aussi loguer
            $action = $model->isForceDeleting() ? 'PERMANENT_DELETE' : 'DELETE';
            self::logAction($model, $action);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                self::logAction($model, 'RESTORE');
            });
        }
    }

    protected static function logAction($model, $action)
    {
        Traceabilite::create([
            'user_id'       => Auth::id(),
            'entreprise_id' => session('active_entreprise_id') ?? ($model->entreprise_id ?? null),
            'model_type'    => get_class($model),
            'model_id'      => $model->id,
            'action'        => $action,
            'details'       => $model->getAttributes(), // Sauvegarde des données
            'ip_address'    => request()->ip(),
        ]);
    }
}

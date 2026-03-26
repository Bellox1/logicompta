<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Planification de l'archivage annuel du journal ─────────────────────
        // La commande `journal:archive` est déclenchée automatiquement le
        // 30 décembre de chaque année à 23h00, sans aucune intervention humaine.
        // Elle archive toutes les écritures de l'exercice N-1 (année précédente).
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('journal:archive')
                     ->yearlyOn(12, 30, '23:59')   // 30 décembre à 23h59
                     ->withoutOverlapping()         // Évite les exécutions simultanées
                     ->runInBackground()             // N'interrompt pas le serveur web
                     ->onSuccess(function () {
                         \Illuminate\Support\Facades\Log::info(
                             '[Scheduler] Archivage annuel du journal exécuté avec succès.'
                         );
                     })
                     ->onFailure(function () {
                         \Illuminate\Support\Facades\Log::error(
                             '[Scheduler] Échec de l\'archivage annuel du journal.'
                         );
                     });
        });
    }
}

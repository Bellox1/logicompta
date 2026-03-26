<?php

namespace App\Console\Commands;

use App\Models\GeneralAccounting\JournalEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArchiveJournalEntries extends Command
{
    /**
     * Signature de la commande Artisan.
     *
     * Option --year=YYYY permet de forcer l'archivage d'une année précise.
     * Exemple : php artisan journal:archive --year=2025
     */
    protected $signature = 'journal:archive
                            {--year= : Année à archiver (par défaut : l\'année précédente)}
                            {--dry-run : Simule l\'archivage sans modifier la base}';

    protected $description = 'Archive automatiquement toutes les écritures de journal de l\'exercice écoulé. '
                           . 'Planifié le 30 décembre de chaque année.';

    public function handle(): int
    {
        // ── Déterminer l'année à archiver ──────────────────────────────────────
        $targetYear = $this->option('year')
            ? (int) $this->option('year')
            : Carbon::now()->subYear()->year;   // Par défaut : l'année précédente

        $isDryRun = $this->option('dry-run');

        $startOfYear = Carbon::create($targetYear, 1, 1)->startOfDay();
        $endOfYear   = Carbon::create($targetYear, 12, 31)->endOfDay();

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("  Archivage des écritures de journal — Année {$targetYear}");
        if ($isDryRun) {
            $this->warn("  MODE SIMULATION (--dry-run) : aucune modification.");
        }
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // ── Comptage des entrées concernées ────────────────────────────────────
        // ── Comptage des entrées concernées ────────────────────────────────────
        // On archive tout ce qui appartient à l'année qui se termine (ou aux précédentes)
        $count = JournalEntry::where('date', '<=', Carbon::now()->endOfYear()->toDateString())
            ->where('is_archived', '=', false)
            ->count();

        if ($count === 0) {
            $this->info("  Aucune écriture à archiver.");
            return Command::SUCCESS;
        }

        $this->info("  {$count} écriture(s) à archiver (exercices clôturés ou en cours d'archivage).");

        if ($isDryRun) {
            $this->warn("  Fin de simulation. Aucune donnée modifiée.");
            return Command::SUCCESS;
        }

        // ── Archivage en base ──────────────────────────────────────────────────
        try {
            DB::beginTransaction();

            $archivedAt = Carbon::now();

            $updated = JournalEntry::where('date', '<=', Carbon::now()->endOfYear()->toDateString())
                ->where('is_archived', '=', false)
                ->update([
                    'is_archived' => true,
                    'archived_at' => $archivedAt,
                ]);

            DB::commit();

            $message = "Archivage réussi : {$updated} écriture(s) archivées le {$archivedAt->toDateTimeString()}.";
            $this->info("  ✔ " . $message);
            Log::info("[ArchiveJournalEntries] " . $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("  ✖ Erreur lors de l'archivage : " . $e->getMessage());
            Log::error("[ArchiveJournalEntries] Erreur : " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        return Command::SUCCESS;
    }
}

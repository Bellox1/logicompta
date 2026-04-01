<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use App\Models\GeneralAccounting\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    /**
     * Liste des années archivées pour l'entreprise.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) return redirect()->route('entreprise.setup');

        // Récupérer les années uniques avec le nombre d'écritures archivées
        $query = JournalEntry::where('entreprise_id', '=', $user->entreprise_id)
            ->where('is_archived', '=', true);
            
        if (DB::getDriverName() === 'sqlite') {
            $query->selectRaw('strftime("%Y", date) as year, count(*) as total');
        } else {
            $query->selectRaw('YEAR(date) as year, count(*) as total');
        }

        $archivedYears = $query->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        return view('accounting.archive.index', compact('archivedYears'));
    }

    /**
     * Affiche un "hub" pour une année archivée spécifique.
     */
    public function show($year)
    {
        $user = Auth::user();
        if (!$user || !$user->entreprise_id) return redirect()->route('entreprise.setup');

        $startDate = "{$year}-01-01";
        $endDate = "{$year}-12-31";

        // Récupérer le nombre d'écritures pour cette année
        $totalEntries = JournalEntry::where('entreprise_id', '=', $user->entreprise_id)
            ->where('is_archived', '=', true)
            ->whereYear('date', '=', $year)
            ->count();

        // Liens vers les rapports existants avec les bons filtres dates
        $links = [
            'journal' => route('accounting.journal.index', ['start_date' => $startDate, 'end_date' => $endDate, 'show_archived' => 1]),
            'balance' => route('accounting.balance', ['start_date' => $startDate, 'end_date' => $endDate, 'show_archived' => 1]),
            'bilan'   => route('accounting.bilan',   ['start_date' => $startDate, 'end_date' => $endDate, 'show_archived' => 1]),
            'resultat'=> route('accounting.resultat',['start_date' => $startDate, 'end_date' => $endDate, 'show_archived' => 1]),
        ];

        return view('accounting.archive.show', compact('year', 'links', 'totalEntries'));
    }
}

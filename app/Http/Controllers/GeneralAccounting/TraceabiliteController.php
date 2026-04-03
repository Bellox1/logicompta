<?php

namespace App\Http\Controllers\GeneralAccounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Traceabilite;
use Illuminate\Support\Facades\Auth;

class TraceabiliteController extends Controller
{
    /**
     * Liste des actions de traçabilité avec recherche
     */
    public function index(Request $request)
    {
        $entrepriseId = session('active_entreprise_id');
        $search = $request->query('search');
        
        $query = Traceabilite::with('user')
            ->where('entreprise_id', '=', $entrepriseId);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('action', 'LIKE', "%{$search}%")
                  ->orWhere('model_type', 'LIKE', "%{$search}%")
                  ->orWhere('model_id', 'LIKE', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('accounting.traceabilite.index', compact('logs', 'search'));
    }

    /**
     * Supprimer DEFINITIVEMENT un log (ADMIN SEULEMENT)
     */
    public function forceDelete($id)
    {
        // Vérifier si Admin via l'accesseur role du modèle User
        if (Auth::user()->role !== 'admin') {
            return back()->with('error', 'Action réservée à l\'administrateur.');
        }

        $log = Traceabilite::findOrFail($id);
        if ($log->entreprise_id != session('active_entreprise_id')) abort(403);
        
        $log->delete(); // On supprime la trace (elle n'est pas soft-deletable elle-même)
        return back()->with('success', 'Trace supprimée définitivement.');
    }

    /**
     * Tout vider (ADMIN SEULEMENT)
     */
    public function clearAll()
    {
        if (Auth::user()->role !== 'admin') {
             return back()->with('error', 'Action réservée à l\'administrateur.');
        }

        Traceabilite::where('entreprise_id', session('active_entreprise_id'))->delete();
        return back()->with('success', 'Historique vidé avec succès.');
    }

    /**
     * Restaurer une donnée supprimée
     */
    public function restore($id)
    {
        $log = Traceabilite::findOrFail($id);
        
        // Vérifier les droits (seulement si même entreprise)
        if ($log->entreprise_id != session('active_entreprise_id')) {
            abort(403);
        }

        $modelClass = $log->model_type;
        $modelId = $log->model_id;

        // On cherche l'élément supprimé
        if (class_exists($modelClass)) {
            $item = $modelClass::withTrashed()->find($modelId);
            
            if ($item && $item->trashed()) {
                $item->restore();
                
                // Enregistrer l'action de restauration
                Traceabilite::create([
                    'user_id'       => Auth::id(),
                    'entreprise_id' => session('active_entreprise_id'),
                    'model_type'    => $modelClass,
                    'model_id'      => $modelId,
                    'action'        => 'RESTORE',
                    'details'       => $item->getAttributes(),
                    'ip_address'    => request()->ip(),
                ]);

                return back()->with('success', 'Élément restauré avec succès.');
            }
        }

        return back()->with('error', 'Impossible de restaurer cet élément. Il est possible qu\'il soit définitivement supprimé ou introuvable.');
    }
}

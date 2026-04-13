<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\User;
use App\Models\GeneralAccounting\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EntrepriseController extends Controller
{
    /**
     * Web - Finalisation de l'inscription et configuration entreprise
     */
    public function webRegisterAndSetup(Request $request)
    {
        $user = \Auth::user();
        $pendingUser = session('pending_user');
        
        // Si l'utilisateur n'est pas connecté ET qu'on n'a pas de données d'inscription en cours
        if (!$user && !$pendingUser) {
            return redirect()->route('signup')->with('error', 'Session expirée ou accès non autorisé. Veuillez vous inscrire.');
        }

        $request->validate([
            'action'       => 'required|string|in:join,create,skip',
            'company_code' => 'required_if:action,join|nullable|string',
            'company_name' => 'required_if:action,create|nullable|string|max:255',
        ]);

        return \DB::transaction(function() use ($request, $pendingUser, $user) {
            // 1. Si pas d'utilisateur authentifié, on le crée (via inscription)
            if (!$user) {
                $user = User::create([
                    'name'     => $pendingUser['name'],
                    'email'    => $pendingUser['email'],
                    'password' => \Hash::make($pendingUser['password']),
                    'role'     => 'utilisateur',
                ]);
                $isNewUser = true;
            } else {
                $isNewUser = false;
            }

            // 2. Gérer l'action entreprise
            if ($request->action === 'join') {
                $entreprise = Entreprise::where('code', strtoupper(trim($request->company_code)))->first();
                if (!$entreprise) {
                    throw new \Exception('Code entreprise introuvable.');
                }
                $user->entreprises()->syncWithoutDetaching([
                    $entreprise->id => [
                        'role' => ($user->role === 'admin' ? 'admin' : 'comptable'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
                session(['active_entreprise_id' => $entreprise->id]);
            } 
            elseif ($request->action === 'create') {
                do {
                    $code = Entreprise::generateCode($request->company_name);
                } while (Entreprise::where('code', $code)->exists());

                $entreprise = Entreprise::create([
                    'name' => $request->company_name,
                    'code' => $code,
                ]);

                $user->entreprises()->syncWithoutDetaching([
                    $entreprise->id => [
                        'role' => 'admin',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
                session(['active_entreprise_id' => $entreprise->id]);
                
                // Créer les journaux par défaut pour cette nouvelle entreprise
                Journal::createDefaultJournals($entreprise->id);
            }

            // 3. Finalisation (nettoyage session et connexion si nécessaire)
            if (session()->has('pending_user')) {
                session()->forget('pending_user');
            }

            if ($isNewUser) {
                \Auth::login($user);
            }

            return redirect()->route('accounting.dashboard')->with('success', 'Votre espace de travail est prêt !');
        });
    }

    /**
     * Affiche la page de setup entreprise post-inscription
     */
    public function setup()
    {
        return view('entreprise-setup');
    }

    /**
     * API - Inscription + Configuration Entreprise (Join, Create ou Skip)
     */
    public function registerAndSetup(Request $request)
    {
        \Log::info('registerAndSetup called with data: ', $request->all());
        
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
            'action'                => 'required|string|in:join,create,skip',
            'company_code'          => 'required_if:action,join|nullable|string',
            'company_name'          => 'required_if:action,create|nullable|string|max:255',
        ]);

        \Log::info('Validation passed');

        return \DB::transaction(function() use ($request) {
            // 1. Créer l'utilisateur
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => \Hash::make($request->password),
                'role'     => 'utilisateur', // Par défaut
            ]);

            $entreprise = null;

            // 2. Gérer l'action entreprise
            if ($request->action === 'join') {
                $entreprise = Entreprise::where('code', strtoupper(trim($request->company_code)))->first();
                if (!$entreprise) {
                    throw new \Exception('Code entreprise introuvable.');
                }
                $user->entreprises()->syncWithoutDetaching([
                    $entreprise->id => [
                        'role' => 'comptable',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
                session(['active_entreprise_id' => $entreprise->id]);
            } 
            elseif ($request->action === 'create') {
                do {
                    $code = Entreprise::generateCode($request->company_name);
                } while (Entreprise::where('code', $code)->exists());

                $entreprise = Entreprise::create([
                    'name' => $request->company_name,
                    'code' => $code,
                ]);

                $user->entreprises()->syncWithoutDetaching([
                    $entreprise->id => [
                        'role' => 'admin',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
                session(['active_entreprise_id' => $entreprise->id]);

                // Créer les journaux par défaut pour cette nouvelle entreprise
                Journal::createDefaultJournals($entreprise->id);
            }

            // 3. Créer le token de session
            $token = $user->createToken('comptafriq_token')->plainTextToken;

            return response()->json([
                'success'    => true,
                'message'    => 'Compte configuré avec succès !',
                'user'       => $user,
                'token'      => $token,
                'entreprise' => $entreprise,
                'code'       => $entreprise ? $entreprise->code : null,
            ]);
        });
    }

    /**
     * API - Rejoindre une entreprise existante via son code
     */
    public function join(Request $request)
    {
        $request->validate([
            'code'    => 'required|string|max:20',
            'user_id' => 'required|exists:users,id',
        ]);

        $entreprise = Entreprise::where('code', strtoupper(trim($request->code)))->first();

        if (!$entreprise) {
            return response()->json([
                'success' => false,
                'message' => 'Code entreprise introuvable. Vérifiez le code et réessayez.'
            ], 404);
        }

        $user = User::findOrFail($request->user_id);
        $user->entreprises()->syncWithoutDetaching([
            $entreprise->id => [
                'role' => 'comptable',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        session(['active_entreprise_id' => $entreprise->id]);

        return response()->json([
            'success'    => true,
            'message'    => 'Vous avez rejoint l\'entreprise "' . $entreprise->name . '" avec succès !',
            'entreprise' => $entreprise,
        ]);
    }

    /**
     * API - Créer une nouvelle entreprise (l'utilisateur devient admin)
     */
    public function create(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Générer un code unique
        do {
            $code = Entreprise::generateCode($request->name);
        } while (Entreprise::where('code', $code)->exists());

        $entreprise = Entreprise::create([
            'name' => $request->name,
            'code' => $code,
        ]);

        $user->entreprises()->syncWithoutDetaching([
            $entreprise->id => [
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        session(['active_entreprise_id' => $entreprise->id]);

        // Créer les journaux par défaut pour cette nouvelle entreprise
        Journal::createDefaultJournals($entreprise->id);

        return response()->json([
            'success'    => true,
            'message'    => 'Entreprise "' . $entreprise->name . '" créée avec succès !',
            'entreprise' => $entreprise,
            'code'       => $code,
        ]);
    }

    /**
     * API - Dashboard : lier une entreprise par code (pour un utilisateur déjà connecté)
     */
    public function linkFromDashboard(Request $request)
    {
        $request->validate([
            'code'    => 'required|string|max:20',
            'user_id' => 'required|exists:users,id',
        ]);

        return $this->join($request);
    }

    /**
     * Web - Rejoindre une entreprise existante via son code (depuis le dashboard)
     */
    public function webJoin(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $entreprise = Entreprise::where('code', strtoupper(trim($request->code)))->first();

        if (!$entreprise) {
            return back()->with('error', 'Code entreprise introuvable. Vérifiez le code et réessayez.');
        }

        $user = \Auth::user();
        $user->entreprises()->syncWithoutDetaching([
            $entreprise->id => [
                'role' => 'comptable',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        session(['active_entreprise_id' => $entreprise->id]);

        return redirect()->route('accounting.dashboard')->with('success', 'Vous avez rejoint l\'entreprise "' . $entreprise->name . '" avec succès !');
    }

    /**
     * Web - Créer une nouvelle entreprise (depuis le dashboard)
     */
    public function webCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = \Auth::user();

        // Générer un code unique
        do {
            $code = Entreprise::generateCode($request->name);
        } while (Entreprise::where('code', $code)->exists());

        $entreprise = Entreprise::create([
            'name' => $request->name,
            'code' => $code,
        ]);

        $user->entreprises()->syncWithoutDetaching([
            $entreprise->id => [
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
        session(['active_entreprise_id' => $entreprise->id]);

        // Créer les journaux par défaut pour cette nouvelle entreprise
        Journal::createDefaultJournals($entreprise->id);

        return redirect()->route('accounting.dashboard')->with('success', 'Entreprise "' . $entreprise->name . '" créée avec succès !');
    }

    /**
     * API - Récupérer les infos de son entreprise
     */
    public function info(Request $request)
    {
        $user = User::with('entreprises')->find($request->input('user_id'));

        if (!$user || $user->entreprises->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune entreprise associée.'
            ], 404);
        }

        return response()->json([
            'success'    => true,
            'entreprise' => $user->entreprise,
        ]);
    }

    /**
     * Web - Changer l'entreprise active
     */
    public function switch(Request $request)
    {
        $request->validate([
            'entreprise_id' => 'required|exists:entreprises,id',
        ]);

        $user = \Auth::user();
        
        $entreprise = $user->entreprises()->where('entreprise_id', $request->entreprise_id)->first();
        // Vérifier que l'utilisateur appartient bien à cette entreprise
        if (!$entreprise) {
            return back()->with('error', 'Accès non autorisé à cette entreprise.');
        }

        session(['active_entreprise_id' => $request->entreprise_id]);

        return back()->with('success', "Session ouverte: " . $entreprise->name);
    }

    /**
     * Mettre à jour les informations de l'entreprise active
     */
    public function update(Request $request)
    {
        $user = \Auth::user();
        $entreprise = $user->entreprise;

        if (!$entreprise) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune entreprise active trouvée'
            ], 404);
        }

        // Vérifier si l'utilisateur est admin de cette entreprise
        if ($user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Seul l\'administrateur peut modifier les informations de l\'entreprise'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $entreprise->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Informations de l\'entreprise mises à jour avec succès',
            'entreprise' => $entreprise
        ]);
    }
}

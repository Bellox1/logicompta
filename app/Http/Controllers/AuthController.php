<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Mail\ProfileAccessMail;

class AuthController extends Controller
{
    /**
     * Web - Pré-inscription (sauvegarde en session avant setup entreprise)
     */
    public function postSignup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Stocker les données en session pour l'étape suivante
        session(['pending_user' => [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]]);

        return redirect()->route('entreprise.setup');
    }

    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:admin,comptable,utilisateur'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Création de l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'utilisateur'
        ]);

        // Création du token
        $token = $user->createToken('comptafriq_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé avec succès',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Connexion d'un utilisateur
     */
    public function login(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Vérification des identifiants
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants incorrects'
                ], 401);
            }
            return back()->with('error', 'Identifiants incorrects');
        }

        // Connexion avec session web
        Auth::login($user);
        session()->regenerate();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $user
            ]);
        }

        return redirect()->route('accounting.dashboard');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        // Invalidation de la session web
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        }

        return redirect()->route('login');
    }

    /**
     * Informations de l'utilisateur connecté
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }

    /**
     * Liste de tous les utilisateurs (admin seulement)
     */
    public function users(Request $request)
    {
        // Vérifier si l'utilisateur est admin
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $users = User::with('entreprise')->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Mot de passe oublié
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email non trouvé ou invalide'
            ], 422);
        }

        // Récupérer l'utilisateur
        $user = User::where('email', $request->email)->first();
        
        // Générer un token de réinitialisation (valide 20 minutes)
        $resetToken = Str::random(60);
        
        // Stocker le token dans la base de données
        PasswordResetToken::create([
            'email' => $user->email,
            'token' => $resetToken,
            'expires_at' => now()->addMinutes(20),
        ]);
        
        try {
            // Envoyer l'email d'accès au profil
            Mail::to($user->email)->send(new ProfileAccessMail($resetToken, $user->email));
            
            return response()->json([
                'success' => true,
                'message' => 'Un lien de réinitialisation de mot de passe a été envoyé à votre email',
                'debug_info' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'token_generated' => $resetToken,
                    'mail_sent' => true
                ]
            ]);
        } catch (\Exception $e) {
            // En cas d'erreur d'envoi d'email, retourner quand même le succès mais avec un avertissement
            return response()->json([
                'success' => true,
                'message' => 'Un lien de réinitialisation de mot de passe a été généré (email en simulation)',
                'debug_info' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'token_generated' => $resetToken,
                    'mail_error' => $e->getMessage(),
                    'mail_sent' => false
                ]
            ]);
        }
    }

    /**
     * Mettre à jour le profil utilisateur
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }

    /**
     * Obtenir l'utilisateur via token d'accès direct
     */
    public function getUserByToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Token ou email invalide',
                'errors' => $validator->errors()
            ], 422);
        }

        // Pour l'instant, on simule la validation du token
        // En production, vous devriez stocker et valider le token dans la base de données
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur trouvé',
            'user' => $user
        ]);
    }

    /**
     * Mettre à jour le profil via token d'accès direct
     */
    public function updateProfileByToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'name' => 'required|string|max:255',
            'new_email' => 'nullable|string|email|max:255|unique:users,email,' . $request->email,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        $updateData = ['name' => $request->name];
        
        if ($request->has('new_email') && $request->new_email) {
            $updateData['email'] = $request->new_email;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => $user
        ]);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Pour l'instant, on simule la validation du token
        // En production, vous devriez valider le token et vérifier l'expiration
        $resetToken = PasswordResetToken::where('token', $request->token)
            ->where('email', $request->email)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetToken) {
            return response()->json([
                'success' => false,
                'message' => 'Lien de réinitialisation invalide ou expiré'
            ], 404);
        }

        // Récupérer l'utilisateur depuis le token
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Supprimer le token après utilisation
        $resetToken->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.',
            'user_updated' => false // Indique que seul le mot de passe a été modifié
        ]);
    }
}

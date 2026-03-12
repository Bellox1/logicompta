<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EntrepriseController;

// Routes publiques d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/user-by-token', [AuthController::class, 'getUserByToken']); // Accès direct via token
Route::put('/update-profile-by-token', [AuthController::class, 'updateProfileByToken']); // Mise à jour via token
Route::post('/reset-password', [AuthController::class, 'resetPassword']); // Réinitialisation du mot de passe

// Route pour synchroniser l'utilisateur dans la session
Route::post('/set-session-user', [AuthController::class, 'setSessionUser']);

// Routes entreprises
Route::post('/register-and-setup', [EntrepriseController::class, 'registerAndSetup']);
Route::post('/entreprise/join', [EntrepriseController::class, 'join']);
Route::post('/entreprise/create', [EntrepriseController::class, 'create']);
Route::get('/entreprise/info', [EntrepriseController::class, 'info']);

// Routes protégées (nécessitent un token valide)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user', [AuthController::class, 'updateProfile']); // Mettre à jour le profil
    Route::get('/users', [AuthController::class, 'users']); // Liste des utilisateurs
});

// Route test pour vérifier l'API
Route::get('/test', function () {
    return response()->json([
        'message' => 'API Comptafriq fonctionne!',
        'version' => '1.0.0',
        'status' => 'online'
    ]);
});

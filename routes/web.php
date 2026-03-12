<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneralAccounting\JournalController;

// Route principale vers la page d'accueil
Route::get('/', function () {
    return view('home');
});

// Route pour la page de connexion
Route::get('/login', function () {
    return view('login');
});

// Route pour la page d'inscription
Route::get('/signup', function () {
    return view('signup');
});

// Route pour la page de mot de passe oublié
Route::get('/forgot-password', function () {
    return view('forgot-password');
});

// Route pour la page de profil
Route::get('/profile', function () {
    return view('profile');
});

// Route pour la page de réinitialisation de mot de passe
Route::get('/reset-password', function () {
    return view('reset-password');
});

// Route pour le dashboard
Route::get('/dashbord', function () {
    return view('dashbord');
});

Route::prefix('accounting')->name('accounting.')->group(function () {
    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
    Route::get('/journal/create', [JournalController::class, 'create'])->name('journal.create');
    Route::post('/journal/store', [JournalController::class, 'store'])->name('journal.store');
    Route::get('/journal/{id}', [JournalController::class, 'show'])->name('journal.show');
    Route::get('/ledger/{account_id?}', [JournalController::class, 'ledger'])->name('ledger');
    Route::get('/balance', [JournalController::class, 'balance'])->name('balance');
    Route::get('/bilan', [JournalController::class, 'bilan'])->name('bilan');
    Route::get('/resultat', [JournalController::class, 'resultat'])->name('resultat');
    Route::get('/help', [JournalController::class, 'help'])->name('help');
});

<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\GeneralAccounting\JournalController;
use App\Http\Controllers\EntrepriseController;
use App\Http\Controllers\AuthController;

// La racine redirige directement vers le dashboard comptable
Route::get('/', function () {
    return redirect()->route('accounting.dashboard');
})->name('home');

// Route pour la page de connexion
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route pour la page d'inscription
Route::get('/signup', function () {
    return view('signup');
})->name('signup');

Route::post('/signup', [AuthController::class, 'postSignup'])->name('signup.post');

// Route pour la page de mot de passe oublié
Route::get('/forgot-password', function () {
    return view('forgot-password');
})->name('forgot-password');

// Route pour la page de profil
Route::get('/profile', function () {
    return view('profile');
})->name('profile');

// Route pour la page de réinitialisation de mot de passe
Route::get('/reset-password', function () {
    return view('reset-password');
})->name('reset-password');

// Route pour la page de configuration d'entreprise (après inscription)
Route::get('/entreprise-setup', [EntrepriseController::class, 'setup'])->name('entreprise.setup');
Route::post('/entreprise-setup', [EntrepriseController::class, 'webRegisterAndSetup'])->name('entreprise.setup.post'); // Added this line

// Route de compatibilité pour l'ancien lien dashboard
Route::get('/dashbord', function() {
    return redirect()->route('accounting.dashboard');
});

Route::prefix('accounting')->name('accounting.')->middleware(['web', 'auth'])->group(function () {
    Route::get('/dashbord', function () {
        return view('dashbord', ['user' => Auth::user()]);
    })->name('dashboard');

    Route::post('/entreprise/join', [EntrepriseController::class, 'webJoin'])->name('entreprise.join');
    Route::post('/entreprise/create', [EntrepriseController::class, 'webCreate'])->name('entreprise.create');

    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
    Route::get('/journal/export-pdf', [JournalController::class, 'exportPdf'])->name('journal.export.pdf');
    Route::get('/journal/create', [JournalController::class, 'create'])->name('journal.create');
    Route::post('/journal/store', [JournalController::class, 'store'])->name('journal.store');
    Route::get('/journal/import', [JournalController::class, 'importForm'])->name('journal.import');
    Route::post('/journal/import', [JournalController::class, 'importProcess'])->name('journal.import.process');
    Route::get('/journal/{id}', [JournalController::class, 'show'])->name('journal.show');
    Route::get('/journal/{id}/pdf', [JournalController::class, 'showPdf'])->name('journal.show.pdf');
    Route::get('/ledger/{account_id?}', [JournalController::class, 'ledger'])->name('ledger');
    Route::get('/ledger-pdf/{account_id?}', [JournalController::class, 'ledgerPdf'])->name('ledger.pdf');
    Route::get('/balance', [JournalController::class, 'balance'])->name('balance');
    Route::get('/balance-pdf', [JournalController::class, 'balancePdf'])->name('balance.pdf');
    Route::get('/bilan', [JournalController::class, 'bilan'])->name('bilan');
    Route::get('/bilan-pdf', [JournalController::class, 'bilanPdf'])->name('bilan.pdf');
    Route::get('/resultat', [JournalController::class, 'resultat'])->name('resultat');
    Route::get('/resultat-pdf', [JournalController::class, 'resultatPdf'])->name('resultat.pdf');
    Route::get('/help', [JournalController::class, 'help'])->name('help');
});

<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\GeneralAccounting\JournalController;
use App\Http\Controllers\GeneralAccounting\JournalDataController;
use App\Http\Controllers\GeneralAccounting\LedgerController;
use App\Http\Controllers\GeneralAccounting\TrialBalanceController;
use App\Http\Controllers\GeneralAccounting\FinancialStatementController;
use App\Http\Controllers\GeneralAccounting\JournalSettingsController;
use App\Http\Controllers\GeneralAccounting\SupportController;
use App\Http\Controllers\GeneralAccounting\AccountController;
use App\Http\Controllers\GeneralAccounting\ArchiveController;
use App\Http\Controllers\GeneralAccounting\OcrController;
use App\Http\Controllers\GeneralAccounting\TraceabiliteController;
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

Route::get('/forgot-password', function () {
    return view('forgot-password');
})->name('forgot-password');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.post');

// Route pour la page de profil
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', function () {
        return view('profile', ['user' => Auth::user()]);
    })->name('profile');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');
    Route::post('/entreprise/update', [EntrepriseController::class, 'update'])->name('entreprise.update');
});

Route::get('/reset-password', function () {
    return view('reset-password');
})->name('reset-password');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.post');

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
    Route::post('/entreprise/switch', [EntrepriseController::class, 'switch'])->name('entreprise.switch');

    Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
    Route::get('/journal/export-pdf', [JournalDataController::class, 'exportPdf'])->name('journal.export.pdf');
    Route::get('/journal/create', [JournalController::class, 'create'])->name('journal.create');
    Route::post('/journal/store', [JournalController::class, 'store'])->name('journal.store');
    Route::get('/journal/import', [JournalDataController::class, 'importForm'])->name('journal.import');
    Route::post('/journal/import/preview', [JournalDataController::class, 'importPreview'])->name('journal.import.preview');
    Route::get('/journal/import/preview', [JournalDataController::class, 'importPreview']); // Allow GET for refreshing/back
    Route::post('/journal/import/process', [JournalDataController::class, 'importProcess'])->name('journal.import.process');
    Route::get('/journal/{id}', [JournalController::class, 'show'])->name('journal.show');
    Route::get('/journal/{id}/edit', [JournalController::class, 'edit'])->name('journal.edit');
    Route::post('/journal/{id}/update', [JournalController::class, 'update'])->name('journal.update');
    Route::delete('/journal/{id}', [JournalController::class, 'destroy'])->name('journal.destroy');
    Route::get('/journal/{id}/pdf', [JournalDataController::class, 'showPdf'])->name('journal.show.pdf');
    // OCR : redirigé vers OcrController (Tesseract local) — anciennement Google Vision API
    Route::post('/journal/ocr-import', [OcrController::class, 'ocrImport'])->name('journal.ocr_import');
    
    // Paramétrage des journaux
    Route::get('/journals-settings', [JournalSettingsController::class, 'index'])->name('journals-settings.index');
    Route::get('/journals-settings/create', [JournalSettingsController::class, 'create'])->name('journals-settings.create');
    Route::post('/journals-settings', [JournalSettingsController::class, 'store'])->name('journals-settings.store');
    Route::get('/journals-settings/{id}/edit', [JournalSettingsController::class, 'edit'])->name('journals-settings.edit');
    Route::post('/journals-settings/{id}', [JournalSettingsController::class, 'update'])->name('journals-settings.update');
    Route::delete('/journals-settings/{id}', [JournalSettingsController::class, 'destroy'])->name('journals-settings.destroy');
    
    Route::get('/ledger/{account_id?}', [LedgerController::class, 'ledger'])->name('ledger');
    Route::get('/ledger-pdf/{account_id?}', [LedgerController::class, 'ledgerPdf'])->name('ledger.pdf');
    
    Route::get('/balance', [TrialBalanceController::class, 'balance'])->name('balance');
    Route::get('/balance-pdf', [TrialBalanceController::class, 'balancePdf'])->name('balance.pdf');
    
    Route::get('/bilan', [FinancialStatementController::class, 'bilan'])->name('bilan');
    Route::get('/bilan-pdf', [FinancialStatementController::class, 'bilanPdf'])->name('bilan.pdf');
    Route::get('/resultat', [FinancialStatementController::class, 'resultat'])->name('resultat');
    Route::get('/resultat-pdf', [FinancialStatementController::class, 'resultatPdf'])->name('resultat.pdf');
    
    Route::get('/help', [SupportController::class, 'help'])->name('help');
    Route::get('/system-date', [SupportController::class, 'systemeDate'])->name('system-date');
    
    // Plan Comptable et Sous-comptes
    Route::get('/compte', [AccountController::class, 'index'])->name('account.index');
    Route::get('/compte/import', [AccountController::class, 'importForm'])->name('account.import');
    Route::post('/compte/import/preview', [AccountController::class, 'importPreview'])->name('account.import.preview');
    Route::post('/compte/import/confirm', [AccountController::class, 'importProcess'])->name('account.import.process');
    Route::post('/compte/sous-compte', [AccountController::class, 'storeSousCompte'])->name('account.store_sous_compte');
    Route::put('/compte/sous-compte/{id}', [AccountController::class, 'updateSousCompte'])->name('account.update_sous_compte');
    Route::delete('/compte/sous-compte/{id}', [AccountController::class, 'destroySousCompte'])->name('account.destroy_sous_compte');

    // Archives & Traçabilité
    Route::get('/archives', [ArchiveController::class, 'index'])->name('archive.index');
    Route::get('/archives/{year}', [ArchiveController::class, 'show'])->name('archive.show');
    
    Route::get('/traceabilite', [TraceabiliteController::class, 'index'])->name('traceabilite.index');
    Route::post('/traceabilite/{id}/restore', [TraceabiliteController::class, 'restore'])->name('traceabilite.restore');
    Route::delete('/traceabilite/{id}', [TraceabiliteController::class, 'forceDelete'])->name('traceabilite.force_delete');
    Route::delete('/traceabilite/clear-all', [TraceabiliteController::class, 'clearAll'])->name('traceabilite.clear_all');
});

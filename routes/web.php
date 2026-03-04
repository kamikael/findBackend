<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSectorController;
use App\Http\Controllers\Admin\AdminCandidatureController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminProviderController;
use Illuminate\Support\Facades\Mail;

Route::get('/', fn () => view('welcome'));

// Redirect navigateur FedaPay (web)
Route::get('/fedapay/redirect', [PaymentController::class, 'redirectFromFedaPay'])
    ->name('fedapay.redirect');



// Admin (Breeze Blade)
Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Secteurs
        Route::get('/sectors', [AdminSectorController::class, 'index'])->name('sectors.index');
        Route::post('/sectors', [AdminSectorController::class, 'store'])->name('sectors.store');
        Route::put('/sectors/{id}', [AdminSectorController::class, 'update'])->name('sectors.update');
        Route::delete('/sectors/{id}', [AdminSectorController::class, 'destroy'])->name('sectors.destroy');
        Route::post('/sectors/{id}/reset-slots', [AdminSectorController::class, 'resetSlots'])->name('sectors.resetSlots');

        // Candidatures
        Route::get('/candidatures', [AdminCandidatureController::class, 'index'])->name('candidatures.index');
        Route::get('/candidatures/{id}', [AdminCandidatureController::class, 'show'])->name('candidatures.show');
        Route::post('/candidatures/{id}/cancel', [AdminCandidatureController::class, 'cancel'])->name('candidatures.cancel');
        Route::get('/candidatures/export/paid', [AdminCandidatureController::class, 'exportPaid'])->name('candidatures.exportPaid');

        // Paiements
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');

        // Providers (Mobile Money)
        Route::get('/providers', [AdminProviderController::class, 'index'])->name('providers.index');
        Route::post('/providers', [AdminProviderController::class, 'store'])->name('providers.store');
        Route::put('/providers/{id}', [AdminProviderController::class, 'update'])->name('providers.update');
        Route::post('/providers/{id}/toggle', [AdminProviderController::class, 'toggle'])->name('providers.toggle');
        Route::delete('/providers/{id}', [AdminProviderController::class, 'destroy'])->name('providers.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });



require __DIR__.'/auth.php';

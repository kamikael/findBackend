<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\CandidatureController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicStatsController;

Route::prefix('v1')->group(function () {


    Route::get('/public/stats', [PublicStatsController::class, 'index']);

    Route::get('/providers', function () {
    return \App\Models\MobileMoneyProvider::where('is_active', true)->get();
});

    // ✅ Public (Frontend candidat) - secteurs
    Route::get('/sectors', [SectorController::class, 'getSectors']);
    Route::get('/sectors/{id}', [SectorController::class, 'getSector']);

    // ✅ Public (Frontend candidat) - postuler + obtenir URL paiement
    Route::post('/candidatures', [CandidatureController::class, 'createCandidature']);

    // ✅ Webhook FedaPay (serveur -> serveur)
    Route::post('/fedapay/webhook', [PaymentController::class, 'callback'])
        ->middleware('fedapay.webhook');

    // (Optionnel) Ping API
    // Route::get('/health', fn () => response()->json(['ok' => true]));
});
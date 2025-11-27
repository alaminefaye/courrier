<?php

use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\Api\CourrierApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API QR Code (publique pour scan)
Route::prefix('v1')->group(function () {
    Route::post('/qrcode/scan', [QrCodeController::class, 'scan']);
    Route::get('/qrcode/verify', [QrCodeController::class, 'verify']);

    // API Courriers (authentifiée)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/courrier/{nim}', [CourrierApiController::class, 'getCourrier']);
        Route::post('/courrier/{nim}/confirmer-reception', [CourrierApiController::class, 'confirmerReceptionEntrant']);
        Route::post('/courrier/{nim}/confirmer-livraison', [CourrierApiController::class, 'confirmerLivraisonSortant']);
        Route::get('/courriers', [CourrierApiController::class, 'liste']);
    });
});

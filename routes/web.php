<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CourrierEntrantController;
use App\Http\Controllers\CourrierSortantController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\DirectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Courriers Entrants
    Route::prefix('courriers/entrants')->name('courriers.entrants.')->group(function () {
        Route::get('/', [CourrierEntrantController::class, 'index'])->middleware('can:viewAny,App\Models\CourrierEntrant')->name('index');
        Route::get('/create', [CourrierEntrantController::class, 'create'])->middleware('can:create,App\Models\CourrierEntrant')->name('create');
        Route::post('/', [CourrierEntrantController::class, 'store'])->middleware('can:create,App\Models\CourrierEntrant')->name('store');
        Route::get('/{id}', [CourrierEntrantController::class, 'show'])->middleware('can:view,courrierEntrant')->name('show');
        Route::get('/{id}/edit', [CourrierEntrantController::class, 'edit'])->middleware('can:update,courrierEntrant')->name('edit');
        Route::put('/{id}', [CourrierEntrantController::class, 'update'])->middleware('can:update,courrierEntrant')->name('update');
        Route::delete('/{id}', [CourrierEntrantController::class, 'destroy'])->middleware('can:delete,courrierEntrant')->name('destroy');
        Route::post('/{id}/transmettre', [CourrierEntrantController::class, 'transmettre'])->middleware('can:transmettre,courrierEntrant')->name('transmettre');
        Route::post('/{id}/confirmer', [CourrierEntrantController::class, 'confirmerReception'])->middleware('can:confirmerReception,courrierEntrant')->name('confirmer');
        Route::get('/{id}/qr/pdf', [CourrierEntrantController::class, 'imprimerQr'])->middleware('can:imprimerQr,courrierEntrant')->name('qr.pdf');
    });

    // Courriers Sortants
    Route::prefix('courriers/sortants')->name('courriers.sortants.')->group(function () {
        Route::get('/', [CourrierSortantController::class, 'index'])->middleware('can:viewAny,App\Models\CourrierSortant')->name('index');
        Route::get('/create', [CourrierSortantController::class, 'create'])->middleware('can:create,App\Models\CourrierSortant')->name('create');
        Route::post('/', [CourrierSortantController::class, 'store'])->middleware('can:create,App\Models\CourrierSortant')->name('store');
        Route::get('/{id}', [CourrierSortantController::class, 'show'])->middleware('can:view,courrierSortant')->name('show');
        Route::get('/{id}/edit', [CourrierSortantController::class, 'edit'])->middleware('can:update,courrierSortant')->name('edit');
        Route::put('/{id}', [CourrierSortantController::class, 'update'])->middleware('can:update,courrierSortant')->name('update');
        Route::delete('/{id}', [CourrierSortantController::class, 'destroy'])->middleware('can:delete,courrierSortant')->name('destroy');
        Route::post('/{id}/transmettre', [CourrierSortantController::class, 'transmettre'])->middleware('can:transmettre,courrierSortant')->name('transmettre');
        Route::post('/{id}/confirmer', [CourrierSortantController::class, 'confirmerLivraison'])->middleware('can:confirmerLivraison,courrierSortant')->name('confirmer');
        Route::get('/{id}/qr/pdf', [CourrierSortantController::class, 'imprimerQr'])->middleware('can:imprimerQr,courrierSortant')->name('qr.pdf');
    });

    // QR Code
    Route::prefix('qrcode')->name('qrcode.')->group(function () {
        Route::post('/scan', [QrCodeController::class, 'scan'])->name('scan');
        Route::get('/verify', [QrCodeController::class, 'verify'])->name('verify');
    });

    // Directions, Services, Users (Protégés par policies)
    Route::middleware(['auth'])->group(function () {
        Route::resource('directions', DirectionController::class)->middleware('can:viewAny,App\Models\Direction');
        Route::resource('services', ServiceController::class)->middleware('can:viewAny,App\Models\Service');
        Route::resource('users', UserController::class)->middleware('can:viewAny,App\Models\User');
    });

    // Rôles et Permissions (Admin uniquement)
    Route::middleware(['auth'])->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [\App\Http\Controllers\RolePermissionController::class, 'index'])->name('index');
        Route::post('/{userId}/permissions', [\App\Http\Controllers\RolePermissionController::class, 'updatePermissions'])->name('updatePermissions');
    });

    // Recherche
    Route::get('/recherche', [\App\Http\Controllers\RechercheController::class, 'index'])->name('recherche.index');

    // Exports
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/entrants/{id}/pdf', [\App\Http\Controllers\ExportController::class, 'exportPdfEntrant'])->name('entrants.pdf');
        Route::get('/sortants/{id}/pdf', [\App\Http\Controllers\ExportController::class, 'exportPdfSortant'])->name('sortants.pdf');
        Route::get('/entrants/excel', [\App\Http\Controllers\ExportController::class, 'exportExcelEntrants'])->name('entrants.excel');
        Route::get('/sortants/excel', [\App\Http\Controllers\ExportController::class, 'exportExcelSortants'])->name('sortants.excel');
    });
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KomunitasProfilController;
use App\Http\Controllers\Api\RekeningController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CampaignPublicController;
use App\Http\Controllers\Api\KomunitasCampaignController;
use App\Http\Controllers\Api\AdminAuditController;

// Auth
Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('/register/komunitas', [AuthController::class, 'registerKomunitas']);
    });

    Route::prefix('komunitas')->group(function () {
        Route::get('/{id}/profil-publik', [KomunitasProfilController::class, 'profilPublik']);
    });

    Route::prefix('campaign')->group(function () {
        Route::get('/', [CampaignPublicController::class, 'index']);
        Route::get('/{id}/detail-publik', [CampaignPublicController::class, 'detailPublik']);
        Route::get('/{id}/donatur', [CampaignPublicController::class, 'donatur']);
    });


    Route::middleware(['auth:api', 'role:komunitas'])
        ->prefix('komunitas')
        ->group(function () {

            // Profil
            Route::patch('/profil', [KomunitasProfilController::class, 'updateProfil']);

            // Rekening
            Route::prefix('rekening')->group(function () {
                Route::get('/riwayat', [RekeningController::class, 'riwayat']);
                Route::post('/ajukan-perubahan', [RekeningController::class, 'ajukanPerubahan']);
            });

            // Dashboard
            Route::get('/dashboard', [DashboardController::class, 'index']);

            // Campaign
            Route::prefix('campaign')->group(function () {

                Route::get('/riwayat', [KomunitasCampaignController::class, 'riwayat']);

                Route::post('/ajukan', [KomunitasCampaignController::class, 'ajukan']);

                Route::post('/{id}/update-post', [
                    KomunitasCampaignController::class,
                    'updatePost'
                ]);

                Route::post('/{id}/klarifikasi', [
                    KomunitasCampaignController::class,
                    'klarifikasi'
                ]);
            });
        });


    /*
    |--------------------------------------------------------------------------
    | Super Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:api', 'role:superadmin'])
        ->prefix('admin')
        ->group(function () {

            Route::get('/audit-logs', [
                AdminAuditController::class,
                'index'
            ]);

        });
});
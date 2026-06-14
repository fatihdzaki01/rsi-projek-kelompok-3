<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\KomunitasController;

/*
|--------------------------------------------------------------------------
| API Routes - Berbagive Platform
|--------------------------------------------------------------------------
|
| Berdasarkan kebutuhan Frontend untuk modul Komunitas & Campaign Management
| sesuai gambar yang diberikan.
|
*/

Route::prefix('v1')->group(function () {

    // ============================================================
    // ENDPOINT UNTUK KOMUNITAS & CAMPAIGN MANAGEMENT
    // (Memerlukan autentikasi - role: KOMUNITAS)
    // ============================================================
    Route::middleware('auth:sanctum')->group(function () {
        
        // 1. Halaman Dashboard Komunitas (FSD-10.1)
        Route::get('/komunitas/dashboard', [KomunitasController::class, 'dashboard']);
        
        // 2. Halaman Grafik Donasi (FSD-10.2)
        Route::get('/campaign/grafik-donasi', [CampaignController::class, 'grafikDonasi']);
        
        // 3. Halaman Detail Keuangan Campaign (FSD-10.3)
        Route::get('/campaign/{id}/keuangan', [CampaignController::class, 'detailKeuanganCampaign']);
        
        // 4. Halaman Detail Campaign Internal (FSD-7.2)
        Route::get('/campaign/{id}/internal', [CampaignController::class, 'detailCampaignInternal']);
        
        // 5. Halaman Daftar Campaign Komunitas (FSD-2.8)
        Route::get('/campaign/list', [CampaignController::class, 'daftarCampaign']);
        
        // 6. Halaman Buat Campaign (FSD-5.1)
        Route::post('/campaign', [CampaignController::class, 'buatCampaign']);
        
        // 7. Halaman Edit Campaign (FSD-5.x)
        Route::put('/campaign/{id}', [CampaignController::class, 'editCampaign']);
        Route::get('/campaign/{id}/edit', [CampaignController::class, 'getCampaignForEdit']);
        
        // 8. Halaman Post Update Campaign (FSD-7.3)
        Route::post('/campaign/{id}/update', [CampaignController::class, 'postUpdateCampaign']);
        
        // 9. Halaman Ajukan Pencairan (FSD-6.1)
        Route::post('/campaign/pencairan', [CampaignController::class, 'ajukanPencairan']);
        Route::get('/campaign/{id}/syarat-pencairan', [CampaignController::class, 'cekSyaratPencairan']);
        
        // 10. Halaman Edit Profil Komunitas (FSD-2.6)
        Route::get('/komunitas/profil', [KomunitasController::class, 'getProfil']);
        Route::put('/komunitas/profil', [KomunitasController::class, 'updateProfil']);
        
        // 11. Halaman Kelola Rekening (FSD-2.7)
        Route::get('/komunitas/rekening', [KomunitasController::class, 'getRekening']);
        Route::post('/komunitas/rekening', [KomunitasController::class, 'ajukanPerubahanRekening']);
        Route::get('/komunitas/rekening/status', [KomunitasController::class, 'cekStatusRekening']);
        
        // 12. Halaman Riwayat Pencairan (FSD-6.4)
        Route::get('/campaign/riwayat-pencairan', [CampaignController::class, 'riwayatPencairan']);
        Route::get('/campaign/pencairan/{id}/detail', [CampaignController::class, 'detailPencairan']);
        
        // 13. Halaman Notifikasi Komunitas (FSD-9.x)
        Route::get('/komunitas/notifikasi', [KomunitasController::class, 'getNotifikasi']);
        Route::put('/komunitas/notifikasi/{id}/baca', [KomunitasController::class, 'tandaiBaca']);
        Route::put('/komunitas/notifikasi/baca-semua', [KomunitasController::class, 'tandaiBacaSemua']);
        
    });
    
    // ============================================================
    // ENDPOINT UNTUK UPLOAD FILE (Temporary/Media)
    // ============================================================
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/upload/temp', [MediaController::class, 'uploadTemp']);
        Route::delete('/upload/temp/{path}', [MediaController::class, 'deleteTemp']);
    });
    
});

// ============================================================
// FALLBACK ROUTE
// ============================================================
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'error_code' => 'ERR-NOTFOUND-99',
        'message' => 'Endpoint tidak ditemukan'
    ], 404);
});
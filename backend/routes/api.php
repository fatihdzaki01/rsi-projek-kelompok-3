<?php

use Illuminate\Support\Facades\Route;

// ============================================================
// DONATUR routes
// ============================================================
Route::middleware(['auth:sanctum', 'role:DONATUR'])->group(function () {
    Route::post('/donations', [\App\Http\Controllers\DonationController::class, 'store']);
    Route::get('/donations/history', [\App\Http\Controllers\DonationController::class, 'history']);
    Route::get('/donations/{id}', [\App\Http\Controllers\DonationController::class, 'show']);
    Route::get('/donations/{id}/receipt', [\App\Http\Controllers\DonationController::class, 'receipt']);
});

// ============================================================
// KOMUNITAS routes
// ============================================================
Route::middleware(['auth:sanctum', 'role:KOMUNITAS'])->group(function () {
    Route::get('/campaigns/{id}/donors', [\App\Http\Controllers\CampaignController::class, 'donors']);
    Route::get('/dashboard/community/summary', [\App\Http\Controllers\CommunityDashboardController::class, 'summary']);
    Route::get('/dashboard/community/donation-chart', [\App\Http\Controllers\CommunityDashboardController::class, 'donationChart']);
    Route::get('/dashboard/community/campaign-finance/{id}', [\App\Http\Controllers\CommunityDashboardController::class, 'campaignFinance']);
});

// ============================================================
// SUPERADMIN routes
// ============================================================
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    Route::patch('/donations/{id}/payment-status', [\App\Http\Controllers\DonationController::class, 'updatePaymentStatus']);
    Route::get('/dashboard/platforms/statistics', [\App\Http\Controllers\PlatformDashboardController::class, 'statistics']);
    Route::get('/reports/platform-financial/export', [\App\Http\Controllers\ReportController::class, 'exportFinancial']);
});
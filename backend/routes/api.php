<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\CampaignPublicController;
use App\Http\Controllers\Api\PostUpdateReportController;
use App\Http\Controllers\Api\CommunityFollowController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\InternalNotificationController;
use App\Http\Controllers\Api\CampaignReportController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\SuperadminController;
use App\Http\Controllers\Api\KomunitasProfilController;
use App\Http\Controllers\Api\KomunitasCampaignController;
use App\Http\Controllers\Api\RekeningController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\KategoriCampaignController;
use App\Http\Controllers\Api\AdminAuditController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\DonationController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

Route::prefix('auth')->middleware('throttle:30,1')->group(function () {
    Route::post('/register-user', [AuthController::class, 'registerUser']);
    Route::post('/register-komunitas', [AuthController::class, 'registerKomunitas']);
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,15');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:5,1440');
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['throttle:120,1', 'auth:sanctum'])->prefix('users')->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::patch('/me', [UserController::class, 'update']);
    Route::post('/me', [UserController::class, 'update']);
    Route::patch('/me/password', [UserController::class, 'changePassword']);
    Route::get('/me/donations', [DonationController::class, 'history']);
    Route::get('/me/following', [UserController::class, 'following']);
});

Route::get('/communities/{id}/profile', [KomunitasProfilController::class, 'profilPublik']);

// Public campaign routes — accessible without login (visitor browsing)
Route::get('/campaigns/{id}/public', [CampaignPublicController::class, 'show']);
Route::get('/campaigns/{id}/donors', [CampaignPublicController::class, 'donors']);
Route::get('/campaigns/{id}/monitoring', [MonitoringController::class, 'publicCampaign']);

Route::middleware(['throttle:120,1', 'auth:sanctum'])->group(function () {
    Route::post('/campaigns/{id}/complete', [CampaignPublicController::class, 'complete']);
    Route::post('/communities/{communityId}/follow', [CommunityFollowController::class, 'follow']);
    Route::delete('/communities/{communityId}/follow', [CommunityFollowController::class, 'unfollow']);
    Route::get('/communities/{communityId}/followers', [CommunityFollowController::class, 'followers']);

    // KOMUNITAS-only routes
    Route::middleware('role:KOMUNITAS')->group(function () {
        Route::patch('/communities/profile', [KomunitasProfilController::class, 'updateProfil']);
        Route::post('/communities/profile', [KomunitasProfilController::class, 'updateProfil']);
        Route::get('/communities/profile', [KomunitasProfilController::class, 'profilSaya']);

        Route::get('/communities/campaigns', [KomunitasCampaignController::class, 'riwayat']);
        Route::post('/communities/campaigns', [KomunitasCampaignController::class, 'ajukan']);
        Route::post('/communities/campaigns/{id}/updates', [KomunitasCampaignController::class, 'updatePost']);
        Route::post('/communities/campaigns/{id}/clarifications', [KomunitasCampaignController::class, 'klarifikasi']);

        Route::get('/communities/bank-account/history', [RekeningController::class, 'riwayat']);
        Route::post('/communities/bank-account/change', [RekeningController::class, 'ajukanPerubahan']);

        Route::get('/communities/dashboard', [DashboardController::class, 'index']);
    });

    Route::post('/donations', [DonationController::class, 'store']);
    Route::get('/donations/{id}', [DonationController::class, 'show']);
    Route::get('/donations/{id}/receipt', [DonationController::class, 'receipt']);
    Route::get('/donations/{id}/receipt-pdf', [DonationController::class, 'receiptPdf']);
    Route::patch('/donations/{id}/payment-status', [DonationController::class, 'updatePaymentStatus']);
});
Route::post('/campaigns/updates/{updateId}/reports', [PostUpdateReportController::class, 'store']);

Route::middleware('throttle:120,1')->group(function () {
Route::get('/campaigns/search', [SearchController::class, 'campaigns']);
Route::get('/communities/search', [SearchController::class, 'communities']);
Route::get('/campaign-categories', [KategoriCampaignController::class, 'index']);
Route::get('/jenis-lembaga', [MasterDataController::class, 'jenisLembaga']);
Route::get('/wilayah', [MasterDataController::class, 'wilayah']);
Route::get('/jenis-dokumen', [MasterDataController::class, 'jenisDokumen']);
});

Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/{notificationId}/read', [NotificationController::class, 'markAsRead']);
});

Route::post('/internal/notifications/user-events', [InternalNotificationController::class, 'store']);

Route::middleware('auth:sanctum')->post('/campaigns/{id}/reports', [CampaignReportController::class, 'store']);

// Superadmin & Monitoring routes (from sa branch merge)
Route::middleware(['auth:sanctum', 'role:SUPERADMIN'])->group(function () {
    Route::get('/superadmin/profile', [SuperadminController::class, 'profile']);
    Route::patch('/superadmin/profile', [SuperadminController::class, 'updateProfile']);
    Route::post('/superadmin/profile', [SuperadminController::class, 'updateProfile']);
    Route::patch('/superadmin/profile/password', [SuperadminController::class, 'changePassword']);
    Route::get('/superadmin/dashboard', [SuperadminController::class, 'dashboard']);
    Route::get('/superadmin/dashboard/statistics', [SuperadminController::class, 'dashboardStatistics']);
    Route::get('/superadmin/dashboard/activities', [SuperadminController::class, 'dashboardActivities']);
    Route::get('/superadmin/analytics/platform', [SuperadminController::class, 'platformAnalytics']);
    Route::get('/superadmin/donors', [SuperadminController::class, 'donorList']);
    Route::get('/superadmin/donors/{id}', [SuperadminController::class, 'donorDetail']);
    Route::patch('/superadmin/donors/{id}/status', [SuperadminController::class, 'donorToggleStatus']);
    Route::get('/superadmin/communities', [SuperadminController::class, 'communityList']);
    Route::get('/superadmin/communities/{id}', [SuperadminController::class, 'communityDetail']);
    Route::patch('/superadmin/communities/{id}/status', [SuperadminController::class, 'communityUpdateStatus']);
    Route::get('/superadmin/document-templates', [SuperadminController::class, 'documentTemplateList']);
    Route::post('/superadmin/document-templates', [SuperadminController::class, 'documentTemplateStore']);
    Route::get('/superadmin/campaign-categories', [SuperadminController::class, 'categoryList']);
    Route::post('/superadmin/campaign-categories', [SuperadminController::class, 'categoryStore']);
    Route::patch('/superadmin/campaign-categories/{id}', [SuperadminController::class, 'categoryUpdate']);
    Route::patch('/superadmin/campaign-categories/{id}/status', [SuperadminController::class, 'categoryToggleStatus']);
    Route::delete('/superadmin/campaign-categories/{id}', [SuperadminController::class, 'categoryDelete']);
    Route::post('/superadmin/reports/financial/export', [SuperadminController::class, 'financialExport']);
    Route::get('/superadmin/community-registrations', [SuperadminController::class, 'registrationList']);
    Route::get('/superadmin/community-registrations/history', [SuperadminController::class, 'registrationHistory']);
    Route::get('/superadmin/community-registrations/{id}', [SuperadminController::class, 'registrationDetail']);
    Route::post('/superadmin/community-registrations/{id}/approve', [SuperadminController::class, 'registrationApprove']);
    Route::post('/superadmin/community-registrations/{id}/reject', [SuperadminController::class, 'registrationReject']);
    Route::get('/superadmin/bank-account-changes', [SuperadminController::class, 'bankAccountChangeList']);
    Route::get('/superadmin/bank-account-changes/history', [SuperadminController::class, 'bankAccountChangeHistory']);
    Route::get('/superadmin/bank-account-changes/{id}', [SuperadminController::class, 'bankAccountChangeDetail']);
    Route::post('/superadmin/bank-account-changes/{id}/approve', [SuperadminController::class, 'bankAccountChangeApprove']);
    Route::post('/superadmin/bank-account-changes/{id}/reject', [SuperadminController::class, 'bankAccountChangeReject']);
    Route::get('/superadmin/campaigns/review', [SuperadminController::class, 'campaignReviewList']);
    Route::get('/superadmin/campaigns/{id}', [SuperadminController::class, 'campaignDetail']);
    Route::post('/superadmin/campaigns/{id}/approve', [SuperadminController::class, 'approveCampaign']);
    Route::post('/superadmin/campaigns/{id}/reject', [SuperadminController::class, 'rejectCampaign']);
    Route::get('/superadmin/campaign-review-history', [SuperadminController::class, 'campaignReviewHistory']);
    Route::get('/superadmin/disbursements', [SuperadminController::class, 'disbursementList']);
    Route::get('/superadmin/disbursements/history', [SuperadminController::class, 'disbursementHistory']);
    Route::get('/superadmin/disbursements/{id}', [SuperadminController::class, 'disbursementDetail']);
    Route::post('/superadmin/disbursements/{id}/approve', [SuperadminController::class, 'approveDisbursement']);
    Route::post('/superadmin/disbursements/{id}/reject', [SuperadminController::class, 'rejectDisbursement']);
    Route::get('/superadmin/campaign-reports', [SuperadminController::class, 'campaignReportList']);
    Route::get('/superadmin/campaign-reports/{id}', [SuperadminController::class, 'campaignReportDetail']);
    Route::post('/superadmin/campaign-reports/{id}/ignore', [SuperadminController::class, 'campaignReportIgnore']);
    Route::post('/superadmin/campaigns/{id}/disable', [SuperadminController::class, 'campaignDisable']);
    Route::get('/superadmin/campaign-clarifications', [SuperadminController::class, 'clarificationList']);
    Route::get('/superadmin/campaign-clarifications/{id}', [SuperadminController::class, 'clarificationDetail']);
    Route::post('/superadmin/campaign-clarifications/{id}/reactivate', [SuperadminController::class, 'clarificationReactivate']);
    Route::post('/superadmin/campaign-clarifications/{id}/close-permanently', [SuperadminController::class, 'clarificationClosePermanently']);
    Route::get('/campaigns/{id}/internal', [MonitoringController::class, 'internalCampaign']);
    Route::delete('/campaigns/updates/{updateId}', [SuperadminController::class, 'deleteUpdate']);
    Route::get('/superadmin/audit-logs', [AdminAuditController::class, 'index']);
});

Route::get('/health', function () {
    $checks = [];

    try {
        DB::connection()->getPdo();
        $checks['database'] = ['status' => 'ok'];
    } catch (\Exception $e) {
        $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
    }

    try {
        app('redis')->ping();
        $checks['redis'] = ['status' => 'ok'];
    } catch (\Exception $e) {
        $checks['redis'] = ['status' => 'error', 'message' => $e->getMessage()];
    }

    try {
        $disk = config('filesystems.default');
        Storage::disk($disk)->exists('health-check');
        $checks['storage'] = ['status' => 'ok', 'disk' => $disk];
    } catch (\Exception $e) {
        $checks['storage'] = ['status' => 'error', 'message' => $e->getMessage()];
    }

    $healthy = collect($checks)->every(fn($c) => $c['status'] === 'ok');

    return response()->json([
        'status'    => $healthy ? 'ok' : 'degraded',
        'timestamp' => now()->toIso8601String(),
        'checks'    => $checks,
    ], $healthy ? 200 : 503);
});
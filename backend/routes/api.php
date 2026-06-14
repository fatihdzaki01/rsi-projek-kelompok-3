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


Route::prefix('auth')->group(function () {
    Route::post('/register-user', [AuthController::class, 'registerUser']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:5,1440');
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::patch('/me', [UserController::class, 'update']);
    Route::post('/me', [UserController::class, 'update']);
    Route::patch('/me/password', [UserController::class, 'changePassword']);
    Route::get('/me/donations', [UserController::class, 'donations']);
    
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/campaigns/{id}/public', [CampaignPublicController::class, 'show']);
    Route::post('/communities/{communityId}/follow', [CommunityFollowController::class, 'follow']);
    Route::delete('/communities/{communityId}/follow', [CommunityFollowController::class, 'unfollow']);
});
Route::post('/campaigns/updates/{updateId}/reports', [PostUpdateReportController::class, 'store']);

Route::get('/campaigns/search', [SearchController::class, 'campaigns']);
Route::get('/communities/search', [SearchController::class, 'communities']);

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
});
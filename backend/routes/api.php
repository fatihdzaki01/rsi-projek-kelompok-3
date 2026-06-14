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
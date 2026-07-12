<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Railway health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

Route::prefix('mobile')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::post('/update-fcm-token', [\App\Http\Controllers\Api\AuthController::class, 'updateFcmToken']);
        
        Route::get('/violations', [\App\Http\Controllers\Api\ViolationController::class, 'index']);
        Route::get('/violations/{id}', [\App\Http\Controllers\Api\ViolationController::class, 'show']);
        Route::post('/cases/{case}/acknowledge', [\App\Http\Controllers\Api\MobileCaseController::class, 'acknowledge']);
        Route::get('/attachments/{attachment}/download', [\App\Http\Controllers\Api\MobileCaseController::class, 'downloadAttachment']);
        Route::get('/stats', [\App\Http\Controllers\Api\ViolationController::class, 'stats']);
        Route::get('/analytics', [\App\Http\Controllers\Api\ViolationController::class, 'analytics']);
        Route::get('/hearings/upcoming', [\App\Http\Controllers\Api\HearingController::class, 'upcoming']);
        Route::get('/hearings/calendar', [\App\Http\Controllers\Api\HearingController::class, 'calendar']);
        Route::post('/policy-lookup', [\App\Http\Controllers\Api\MobilePolicyController::class, 'lookup'])
            ->middleware('throttle:20,1');
        
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    });
});

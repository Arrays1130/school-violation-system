<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::any('/debug', function() {
        \Log::info('API Debug hit: ' . request()->fullUrl());
        return response()->json(['status' => 'ok']);
    });
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        
        Route::post('/update-fcm-token', [\App\Http\Controllers\Api\AuthController::class, 'updateFcmToken']);
        
        Route::get('/violations', [\App\Http\Controllers\Api\ViolationController::class, 'index']);
        Route::get('/violations/{id}', [\App\Http\Controllers\Api\ViolationController::class, 'show']);
        Route::get('/stats', [\App\Http\Controllers\Api\ViolationController::class, 'stats']);
        
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllAsRead']);
    });
});

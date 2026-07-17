<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    $checks = [
        'database' => false,
        'cache' => false,
        'queue' => false,
        'sms_configured' => false,
    ];

    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $checks['database'] = true;
    } catch (\Throwable) {
    }

    try {
        $key = 'health:'.str()->random(8);
        \Illuminate\Support\Facades\Cache::put($key, 'ok', 10);
        $checks['cache'] = \Illuminate\Support\Facades\Cache::get($key) === 'ok';
        \Illuminate\Support\Facades\Cache::forget($key);
    } catch (\Throwable) {
    }

    $checks['queue'] = config('queue.default') !== 'sync';
    $checks['sms_configured'] = (bool) (config('services.sms_gateway.url') && config('services.sms_gateway.username'));

    // Only DB reachability is required for "health".
    // Cache/queue status is informational for readiness.
    $healthy = (bool) $checks['database'];

    return response()->json([
        'status' => $healthy ? 'ok' : 'degraded',
        'checks' => $checks,
        'timestamp' => now()->toIso8601String(),
    ], $healthy ? 200 : 503);
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

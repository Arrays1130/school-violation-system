<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    public function send(string $token, string $title, string $body, array $data = [], ?int $badge = null): bool
    {
        $serverKey = config('services.fcm.server_key');

        if (! $serverKey || ! $token) {
            return false;
        }

        $notification = [
            'title' => $title,
            'body' => $body,
        ];

        if ($badge !== null && $badge > 0) {
            // iOS home-screen badge; Android clients also read unread_count from data.
            $notification['badge'] = (string) $badge;
            $data['unread_count'] = (string) $badge;
        }

        try {
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'key '.$serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => $notification,
                'data' => array_map('strval', $data),
                'priority' => 'high',
            ]);

            if (! $response->successful()) {
                Log::warning('FCM push failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('FCM push error: '.$e->getMessage());

            return false;
        }
    }
}

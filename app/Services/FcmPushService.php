<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmPushService
{
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        $serverKey = config('services.fcm.server_key');

        if (! $serverKey || ! $token) {
            return false;
        }

        try {
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'key '.$serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
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

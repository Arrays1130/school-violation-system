<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nService
{
    /**
     * Trigger an n8n webhook with the given event name and payload.
     *
     * @param string $eventName The name of the event (e.g., 'violation_recorded')
     * @param array $payload The data to send
     * @return bool True if the request was successful, false otherwise
     */
    public function triggerWebhook(string $eventName, array $payload): bool
    {
        $webhookUrl = env('N8N_WEBHOOK_URL');

        if (empty($webhookUrl)) {
            Log::warning("N8n Webhook URL is not configured. Event '{$eventName}' was not sent.");
            return false;
        }

        try {
            $dataToSend = array_merge([
                'event_type' => $eventName,
                'timestamp' => now()->toIso8601String(),
                'app_name' => env('APP_NAME', 'Violation System'),
            ], $payload);

            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($webhookUrl, $dataToSend);

            if ($response->successful()) {
                Log::info("N8n Webhook triggered successfully for event '{$eventName}'");
                return true;
            }

            Log::error("Failed to trigger N8n Webhook for event '{$eventName}'. Status: {$response->status()}");
            return false;
        } catch (\Exception $e) {
            Log::error("Exception while triggering N8n Webhook for event '{$eventName}': " . $e->getMessage());
            return false;
        }
    }
}

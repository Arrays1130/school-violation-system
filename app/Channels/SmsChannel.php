<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Check if the notifiable model has a method to get the phone number
        if (! method_exists($notifiable, 'routeNotificationForSms')) {
            return;
        }

        $to = $notifiable->routeNotificationForSms($notification);

        if (! $to) {
            return; // No phone number available
        }

        $message = $notification->toSms($notifiable);

        if (! $message) {
            return;
        }

        // --- SANDBOX MODE ---
        // For now, we will log the SMS to the Laravel logs instead of charging an API
        // This makes it ready for production whenever an API key is purchased.
        Log::info("=== SIMULATED SMS NOTIFICATION ===");
        Log::info("TO: " . $to);
        Log::info("MESSAGE: " . $message);
        Log::info("==================================");

        // --- PRODUCTION MODE (Example using Semaphore) ---
        // $apiKey = env('SEMAPHORE_API_KEY');
        // if ($apiKey) {
        //     Http::post('https://api.semaphore.co/api/v4/messages', [
        //         'apikey' => $apiKey,
        //         'number' => $to,
        //         'message' => $message,
        //         'sendername' => env('SEMAPHORE_SENDER_NAME', 'I-LINK CST')
        //     ]);
        // }
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAppsScriptMailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $url = config('school.google_apps_script_url');

        if (! $url) {
            Log::warning('Google Apps Script URL is not configured.');

            return false;
        }

        try {
            $response = Http::timeout(10)->post($url, [
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Google Apps Script mail failed: '.$e->getMessage());

            return false;
        }
    }
}

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
            // Apps Script web apps often 302 to googleusercontent; follow redirects.
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->withOptions(['allow_redirects' => true])
                ->asJson()
                ->post($url, [
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $body,
                ]);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json) && array_key_exists('ok', $json) && $json['ok'] === false) {
                    Log::error('Google Apps Script mail rejected payload', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return false;
                }

                return true;
            }

            Log::error('Google Apps Script mail failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Google Apps Script mail failed: '.$e->getMessage());

            return false;
        }
    }
}

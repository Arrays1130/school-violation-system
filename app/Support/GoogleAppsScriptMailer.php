<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

class GoogleAppsScriptMailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $url = trim((string) config('school.google_apps_script_url'));

        if ($url === '') {
            Log::warning('Google Apps Script URL is not configured.');

            return false;
        }

        try {
            $payload = json_encode([
                'to' => $to,
                'subject' => $subject,
                'body' => $body,
            ], JSON_THROW_ON_ERROR);

            // cURL + POSTREDIR keeps POST through Apps Script's 302 redirect.
            // Laravel/Guzzle often converts that redirect into GET (hits doGet, no email).
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'User-Agent: VioTrack-MailRelay/1.0',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_POSTREDIR => 3,
                CURLOPT_TIMEOUT => 45,
                CURLOPT_CONNECTTIMEOUT => 15,
            ]);

            $raw = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($errno !== 0) {
                Log::error('Google Apps Script mail cURL error', [
                    'errno' => $errno,
                    'error' => $error,
                    'url' => $url,
                ]);

                return false;
            }

            if ($status < 200 || $status >= 300) {
                Log::error('Google Apps Script mail HTTP failure', [
                    'status' => $status,
                    'body' => is_string($raw) ? mb_substr($raw, 0, 500) : null,
                    'url' => $url,
                ]);

                return false;
            }

            $json = is_string($raw) ? json_decode($raw, true) : null;
            if (is_array($json) && array_key_exists('ok', $json) && $json['ok'] === false) {
                Log::error('Google Apps Script mail rejected payload', [
                    'status' => $status,
                    'body' => $raw,
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Google Apps Script mail failed: '.$e->getMessage(), [
                'url' => $url,
            ]);

            return false;
        }
    }
}

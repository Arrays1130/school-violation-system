<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
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

        $payload = [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
        ];

        try {
            if (function_exists('curl_init')) {
                return self::sendWithCurl($url, $payload);
            }

            return self::sendWithHttp($url, $payload);
        } catch (\Throwable $e) {
            Log::error('Google Apps Script mail failed: '.$e->getMessage(), [
                'url' => $url,
            ]);

            return false;
        }
    }

    /**
     * @param  array{to:string,subject:string,body:string}  $payload
     */
    private static function sendWithCurl(string $url, array $payload): bool
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        // cURL + POSTREDIR keeps POST through Apps Script's 302 redirect.
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
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

        return self::responseOk($status, is_string($raw) ? $raw : null, $url);
    }

    /**
     * @param  array{to:string,subject:string,body:string}  $payload
     */
    private static function sendWithHttp(string $url, array $payload): bool
    {
        $response = Http::timeout(45)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'VioTrack-MailRelay/1.0',
            ])
            ->withOptions([
                'allow_redirects' => [
                    'max' => 10,
                    'strict' => true,
                    'referer' => true,
                    'protocols' => ['https'],
                ],
            ])
            ->asJson()
            ->post($url, $payload);

        return self::responseOk($response->status(), $response->body(), $url);
    }

    private static function responseOk(int $status, ?string $raw, string $url): bool
    {
        if ($status < 200 || $status >= 300) {
            Log::error('Google Apps Script mail HTTP failure', [
                'status' => $status,
                'body' => $raw ? mb_substr($raw, 0, 500) : null,
                'url' => $url,
            ]);

            return false;
        }

        $json = $raw ? json_decode($raw, true) : null;
        if (is_array($json) && array_key_exists('ok', $json) && $json['ok'] === false) {
            Log::error('Google Apps Script mail rejected payload', [
                'status' => $status,
                'body' => $raw,
            ]);

            return false;
        }

        return true;
    }
}

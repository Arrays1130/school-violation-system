<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class RegistrationOtp
{
    public const MAX_ATTEMPTS = 5;

    public const TTL_MINUTES = 10;

    public static function generate(): string
    {
        return (string) random_int(100000, 999999);
    }

    public static function cacheKey(string $email): string
    {
        return 'registration_otp_'.strtolower($email);
    }

    public static function attemptsKey(string $email): string
    {
        return 'registration_otp_attempts_'.strtolower($email);
    }

    public static function store(string $email, array $data, string $otp): void
    {
        Cache::put(self::cacheKey($email), [
            'otp' => $otp,
            'data' => $data,
        ], now()->addMinutes(self::TTL_MINUTES));

        Cache::forget(self::attemptsKey($email));
    }

    /**
     * @return array{success: bool, data?: array, error?: string}
     */
    public static function verify(string $email, string $otp): array
    {
        $cacheKey = self::cacheKey($email);
        $attemptsKey = self::attemptsKey($email);
        $cachedData = Cache::get($cacheKey);

        if (! $cachedData) {
            return ['success' => false, 'error' => 'OTP expired. Please register again.'];
        }

        $attempts = (int) Cache::get($attemptsKey, 0);

        if ($attempts >= self::MAX_ATTEMPTS) {
            Cache::forget($cacheKey);
            Cache::forget($attemptsKey);

            return ['success' => false, 'error' => 'Too many failed attempts. Please register again.'];
        }

        if (! hash_equals((string) $cachedData['otp'], $otp)) {
            $attempts++;
            Cache::put($attemptsKey, $attempts, now()->addMinutes(self::TTL_MINUTES));

            if ($attempts >= self::MAX_ATTEMPTS) {
                Cache::forget($cacheKey);
                Cache::forget($attemptsKey);

                return ['success' => false, 'error' => 'Too many failed attempts. Please register again.'];
            }

            return ['success' => false, 'error' => 'Invalid OTP code. Please try again.'];
        }

        Cache::forget($cacheKey);
        Cache::forget($attemptsKey);

        return ['success' => true, 'data' => $cachedData['data']];
    }
}

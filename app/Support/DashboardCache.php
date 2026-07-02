<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardCache
{
    public static function version(): int
    {
        return (int) Cache::get('dashboard.cache.version', 1);
    }

    public static function bust(): void
    {
        try {
            if (! Cache::has('dashboard.cache.version')) {
                Cache::put('dashboard.cache.version', 1, now()->addYear());
            }
            Cache::increment('dashboard.cache.version');
        } catch (\Exception $e) {
            Log::warning('Failed to bust dashboard cache', ['error' => $e->getMessage()]);
        }
    }

    public static function adminKey(int $userId, string $academicYearKey): string
    {
        return 'dashboard.data.v'.self::version().".{$userId}.{$academicYearKey}";
    }

    public static function deanKey(string $department): string
    {
        return 'dean_dashboard.data.v'.self::version().'.'.md5($department);
    }
}

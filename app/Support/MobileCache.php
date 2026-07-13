<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class MobileCache
{
    public static function bust(): void
    {
        Cache::forget('mobile_dashboard_stats_'.md5('all'));
        Cache::forget('mobile_analytics_'.md5('all_analytics'));

        foreach (array_keys(config('school.departments', [])) as $dept) {
            Cache::forget('mobile_dashboard_stats_'.md5('dean:'.$dept));
            Cache::forget('mobile_analytics_'.md5('dean_analytics:'.$dept));
        }
    }
}

<?php

namespace App\Support;

class QueueHelper
{
    public static function triggerBackgroundWorker()
    {
        if (config('queue.default') === 'database') {
            try {
                if (function_exists('exec')) {
                    $artisan = base_path('artisan');
                    if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                        pclose(popen("start /B php \"$artisan\" queue:work --max-time=30 --stop-when-empty > NUL 2>&1", "r"));
                    } else {
                        exec("php \"$artisan\" queue:work --max-time=30 --stop-when-empty > /dev/null 2>&1 &");
                    }
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }
}

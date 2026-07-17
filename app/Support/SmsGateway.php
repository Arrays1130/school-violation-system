<?php

namespace App\Support;

use App\Jobs\SendSmsViaGateway;

class SmsGateway
{
    public static function enabled(): bool
    {
        return filter_var(config('services.sms_gateway.enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function dispatch(?string $phone, string $message, array $context = []): bool
    {
        if (! $phone || ! self::enabled()) {
            return false;
        }

        SendSmsViaGateway::dispatch($phone, $message)
            ->onQueue(config('services.sms_gateway.queue', 'notifications'));

        return true;
    }
}

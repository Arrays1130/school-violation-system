<?php

namespace App\Support;

class Recaptcha
{
    public static function isEnabled(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        return filled(config('services.recaptcha.site_key'))
            && filled(config('services.recaptcha.secret_key'));
    }
}

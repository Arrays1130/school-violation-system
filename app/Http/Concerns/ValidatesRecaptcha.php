<?php

namespace App\Http\Concerns;

use App\Rules\Recaptcha;

trait ValidatesRecaptcha
{
    protected function recaptchaEnabled(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        return filled(config('services.recaptcha.site_key'))
            && filled(config('services.recaptcha.secret_key'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function recaptchaRules(): array
    {
        if (! $this->recaptchaEnabled()) {
            return [];
        }

        return [
            'g-recaptcha-response' => ['required', new Recaptcha],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function recaptchaMessages(): array
    {
        return [
            'g-recaptcha-response.required' => 'Please complete the reCAPTCHA verification.',
        ];
    }
}

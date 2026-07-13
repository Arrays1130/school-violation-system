<?php

namespace App\Http\Middleware;

use App\Support\Recaptcha;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRecaptchaVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Recaptcha::isEnabled()) {
            return $next($request);
        }

        if ($request->session()->get('auth_recaptcha_passed', false)) {
            return $next($request);
        }

        if ($request->routeIs('recaptcha.*', 'logout')) {
            return $next($request);
        }

        if (! $request->session()->has('auth_post_recaptcha_url')) {
            $request->session()->put('auth_post_recaptcha_url', $request->fullUrl());
        }

        return redirect()->route('recaptcha.challenge');
    }
}

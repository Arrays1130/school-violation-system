<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\Recaptcha;
use App\Support\Recaptcha as RecaptchaSupport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecaptchaChallengeController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        if (! RecaptchaSupport::isEnabled()) {
            return redirect()->to($this->intendedUrl($request));
        }

        if ($request->session()->get('auth_recaptcha_passed', false)) {
            return redirect()->to($this->intendedUrl($request));
        }

        return Inertia::render('Auth/RecaptchaChallenge', [
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
            'userName' => $request->user()->name,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'g-recaptcha-response' => ['required', new Recaptcha],
        ]);

        $request->session()->put('auth_recaptcha_passed', true);

        $url = $request->session()->pull('auth_post_recaptcha_url', route('dashboard', absolute: false));

        return redirect()->to($url)
            ->with('success', 'Verification complete. Welcome back, '.$request->user()->name.'!');
    }

    protected function intendedUrl(Request $request): string
    {
        return $request->session()->pull(
            'auth_post_recaptcha_url',
            route('dashboard', absolute: false)
        );
    }
}

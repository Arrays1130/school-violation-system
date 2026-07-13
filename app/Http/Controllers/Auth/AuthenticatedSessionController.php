<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Recaptcha;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Display the Dean login view.
     */
    public function createDean(): Response
    {
        return Inertia::render('Auth/DeanLogin', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request for Admin.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        $request->session()->regenerate();

        return $this->redirectAfterLogin($request, $user);
    }

    /**
     * Handle an incoming authentication request for Dean.
     */
    public function storeDean(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        if (! $user->isDean() && ! $user->isAdmin() && ! $user->isSuperAdmin()) {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => 'This account is not authorized for the dean portal.',
            ]);
        }

        $request->session()->regenerate();

        return $this->redirectAfterLogin($request, $user);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Logged out successfully! See you soon.');
    }

    protected function redirectAfterLogin(Request $request, User $user): RedirectResponse
    {
        $url = $this->postLoginUrl($user);

        if (Recaptcha::isEnabled()) {
            $request->session()->put('auth_recaptcha_passed', false);
            $request->session()->put('auth_post_recaptcha_url', $url);

            return redirect()->route('recaptcha.challenge');
        }

        return redirect()->to($url)
            ->with('success', 'Welcome back, '.$user->name.'!');
    }

    protected function postLoginUrl(User $user): string
    {
        if ($user->isDean()) {
            return route('dean.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}

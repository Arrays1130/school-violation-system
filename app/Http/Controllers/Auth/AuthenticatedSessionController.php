<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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

        if ($user->isDean()) {
            return redirect()->intended(route('dean.dashboard', absolute: false));
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->intended(route('dashboard', absolute: false))
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle an incoming authentication request for Dean.
     */
    public function storeDean(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        $request->session()->regenerate();

        return redirect()->intended(route('dean.dashboard', absolute: false))
            ->with('success', 'Welcome back, ' . $user->name . '!');
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
}

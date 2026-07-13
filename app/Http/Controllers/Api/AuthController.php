<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = $this->loginThrottleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return response()->json([
                'message' => 'Too many login attempts. Please try again in '.$seconds.' seconds.',
            ], 429);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($throttleKey);

            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        if (! $user->isDean() && ! $user->isAdmin() && ! $user->isSuperAdmin()) {
            Auth::logout();

            return response()->json(['message' => 'This account is not authorized for mobile access.'], 403);
        }

        RateLimiter::clear($throttleKey);

        $deviceName = $request->input('device_name', 'mobile_device');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'role', 'department']),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = Auth::user();
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'FCM Token updated successfully']);
    }

    protected function loginThrottleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
    }
}

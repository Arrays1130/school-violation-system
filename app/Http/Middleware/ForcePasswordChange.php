<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only for students who haven't changed their password
        if ($user && $user instanceof \App\Models\Student && !$user->password_changed_at) {
            // Allow access to logout and profile update (to change password)
            if (!$request->is('profile*') && !$request->is('logout')) {
                return redirect()->route('profile.edit')->with('warning', 'Please change your default password to continue.');
            }
        }

        return $next($request);
    }
}

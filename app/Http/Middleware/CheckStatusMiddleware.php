<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckStatusMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->status === 'pending') {
                if ($request->routeIs('not-verified') || $request->routeIs('logout')) {
                    return $next($request);
                }

                return redirect()->route('not-verified');
            }

            if ($user->status === 'rejected') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Permohonan pendaftaran akun Anda telah ditolak oleh Administrator.',
                ]);
            }

            if ($user->status === 'nonactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda sedang dinonaktifkan oleh Administrator. Silakan hubungi pengelola sistem.',
                ]);
            }
        }

        return $next($request);
    }
}

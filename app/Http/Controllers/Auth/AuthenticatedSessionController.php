<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Memproses login pengguna.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validasi dan autentikasi login
        $request->authenticate();

        $user = $request->user();

        // Validasi status verifikasi akun
        if ($user->status === 'pending') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda sedang dalam antrean verifikasi Administrator. Silakan tunggu persetujuan sebelum dapat masuk ke sistem.',
            ]);
        }

        if ($user->status === 'rejected') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Permohonan pendaftaran akun Anda telah ditolak oleh Administrator.',
            ]);
        }

        if ($user->status === 'nonactive') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda sedang dinonaktifkan oleh Administrator. Silakan hubungi pengelola sistem.',
            ]);
        }

        // Membuat ulang session ID untuk keamanan
        $request->session()->regenerate();

        if ($user->role === 'operator') {
            if (class_exists('\App\Http\Controllers\Operator\OperatorController') && method_exists('\App\Http\Controllers\Operator\OperatorController', 'logActivity')) {
                \App\Http\Controllers\Operator\OperatorController::logActivity(
                    'Autentikasi',
                    'Login',
                    'Operator (' . $user->name . ') berhasil login ke sistem.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | REDIRECT SETELAH LOGIN
        |--------------------------------------------------------------------------
        |
        | - admin    -> admin.dashboard
        | - operator -> operator.dashboard (Operator Selection Screen)
        | - user     -> user.profile
        |
        */

        if (!in_array($user->role, ['admin', 'operator'])) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Halaman autentikasi ini hanya diperuntukkan bagi Administrator dan Operator.',
            ]);
        }

        $url = match ($user->role) {
            'admin' => route('admin.dashboard', absolute: false),
            'operator' => route('operator.dashboard', absolute: false),
            default => route('home', absolute: false),
        };

        return redirect()->intended($url);
    }

    /**
     * Logout pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user && $user->role === 'operator') {
            if (class_exists('\App\Http\Controllers\Operator\OperatorController') && method_exists('\App\Http\Controllers\Operator\OperatorController', 'logActivity')) {
                \App\Http\Controllers\Operator\OperatorController::logActivity(
                    'Autentikasi',
                    'Logout',
                    'Operator (' . $user->name . ') logout dari sistem.'
                );
            }
        }

        // Logout dari guard web
        Auth::guard('web')->logout();

        // Hapus session lama
        $request->session()->invalidate();

        // Membuat CSRF token baru
        $request->session()->regenerateToken();

        // Kembali ke halaman utama
        return redirect()->route('home');
    }
}
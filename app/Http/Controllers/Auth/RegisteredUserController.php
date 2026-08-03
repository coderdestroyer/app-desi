<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\UserWilayahScope;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $provinsis = Provinsi::orderBy('nama_provinsi')->get();
        $kabupatens = Kabupaten::orderBy('nama_kabupaten')->get();

        return view('auth.register', compact('provinsis', 'kabupatens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'scope_type' => ['required', 'in:provinsi,kabupaten'],
            'provinsi_id' => ['nullable', 'required_if:scope_type,provinsi', 'exists:provinsi,provinsi_id'],
            'kabupaten_id' => ['nullable', 'required_if:scope_type,kabupaten', 'exists:kabupaten,kab_id'],
        ], [
            'scope_type.required' => 'Tingkat pengajuan wilayah kerja wajib dipilih.',
            'provinsi_id.required_if' => 'Provinsi wajib dipilih untuk pengajuan tingkat provinsi.',
            'kabupaten_id.required_if' => 'Kabupaten/Kota wajib dipilih untuk pengajuan tingkat kabupaten.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'operator',
            'status' => 'pending',
            'email_verified_at' => now(),
        ]);

        if (UserWilayahScope::ensureTableExists()) {
            if ($request->scope_type === 'provinsi' && $request->filled('provinsi_id')) {
                UserWilayahScope::create([
                    'user_id' => $user->id,
                    'provinsi_id' => $request->provinsi_id,
                    'kabupaten_id' => null,
                ]);
            } elseif ($request->scope_type === 'kabupaten' && $request->filled('kabupaten_id')) {
                $kabupaten = Kabupaten::find($request->kabupaten_id);
                if ($kabupaten) {
                    UserWilayahScope::create([
                        'user_id' => $user->id,
                        'provinsi_id' => $kabupaten->provinsi_id,
                        'kabupaten_id' => $kabupaten->kab_id,
                    ]);
                }
            }
        }

        event(new Registered($user));

        return redirect()->route('login')->with('status', 'Pendaftaran Calon Operator berhasil! Akun Anda sedang dalam antrean verifikasi Administrator. Silakan tunggu persetujuan sebelum dapat masuk ke sistem.');
    }
}
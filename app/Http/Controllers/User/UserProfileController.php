<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Menampilkan halaman profil user.
     */
    public function edit(Request $request): View
    {
        return view('user.profil_user', [
            'user' => $request->user(),
        ]);
    }


    /**
     * Memperbarui profil user.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $request->user()->update($validated);

        return redirect()
            ->route('user.profile')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
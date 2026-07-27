<?php

use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\User\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'role:user',
])
->prefix('user')
->name('user.')
->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard User
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return view('user.dashboard');
    })->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Profile User
    |--------------------------------------------------------------------------
    */

    // Halaman profil
    Route::get(
        '/profile',
        [UserProfileController::class, 'edit']
    )->name('profile');

    // Halaman edit profil
    Route::get(
        '/profile/edit',
        [UserProfileController::class, 'editProfile']
    )->name('profile.edit');

    // Update profil
    Route::patch(
        '/profile',
        [UserProfileController::class, 'update']
    )->name('profile.update');

    // Hapus akun
    Route::delete(
        '/profile',
        [UserProfileController::class, 'destroy']
    )->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */

    // Halaman ubah password
    Route::get(
        '/password',
        function () {
            return view('user.edit');
        }
    )->name('password.edit');

    // Proses update password
    Route::put(
        '/password',
        [PasswordController::class, 'update']
    )->name('password.update');

});
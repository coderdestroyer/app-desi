<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\User\UserProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvestmentMapController;


/*
|--------------------------------------------------------------------------
| PUBLIC LANDING PAGES
|--------------------------------------------------------------------------
|
| Halaman publik yang dapat diakses oleh semua pengunjung
| tanpa harus melakukan autentikasi.
|
*/

Route::get('/', function () {
    return view('landing.home');
})->name('home');


// Route::get('/tentang', function () {
//     return view('landing.about');
// })->name('about');


Route::get('/peta-investasi', [InvestmentMapController::class, 'index'])
    ->name('investment.map');

Route::get('/map/analysis/{nama}', [InvestmentMapController::class, 'analysis'])
    ->name('investment.map.analysis');

Route::get(
    '/analisis',
    [
        AnalysisController::class,
        'index',
    ]
)->name('analysis');


Route::get(
    '/perbandingan-sektor',
    [
        ComparisonController::class,
        'index',
    ]
)->name('comparison');


/*
|--------------------------------------------------------------------------
| DASHBOARD REDIRECT
|--------------------------------------------------------------------------
|
| Route /dashboard menjadi pusat redirect setelah login.
|
| admin    → admin.dashboard
| operator → operator.dashboard
| user     → user.profile
|
*/

Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user?->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'operator' => redirect()->route('operator.dashboard'),
        default => redirect()->route('home'),
    };
})
->middleware(['auth'])
->name('dashboard');


Route::get('/about', function () {
    return view('landing.about');
})->name('about');


/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
|
| Route Laravel Breeze:
|
| - Login
| - Register
| - Forgot Password
| - Reset Password
| - Email Verification
| - Logout
|
*/

require __DIR__ . '/auth.php';


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/admin.php';


/*
|--------------------------------------------------------------------------
| OPERATOR ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/operator.php';


/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__ . '/user.php';

/*
|--------------------------------------------------------------------------
| TEMPORARY ERROR PAGES PREVIEW
|--------------------------------------------------------------------------
*/
Route::prefix('test-error')->group(function() {
    Route::get('401', function() { return view('errors.401'); });
    Route::get('403', function() { return view('errors.403'); });
    Route::get('404', function() { return view('errors.404'); });
    Route::get('419', function() { return view('errors.419'); });
    Route::get('429', function() { return view('errors.429'); });
    Route::get('500', function() { return view('errors.500'); });
    Route::get('503', function() { return view('errors.503'); });
});
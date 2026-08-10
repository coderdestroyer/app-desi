<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\User\UserProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvestmentMapController;


Route::get('/', function () {
    $provinsiInvestasi = [];
    $latestYear = 2025;
    
    // Default values for metrics
    $totalRealisasi = 225450000000000; // Rp 225.45 Triliun fallback
    $pdrbTertinggiNama = 'RIAU';
    $pdrbTertinggiNilai = 598610450000000; // Rp 598.61 Triliun fallback
    $jumlahProyek = 0;
    $topSectors = [];
    $trendsData = [];

    try {
        // Find the latest year that has data for at least 5 different provinces.
        // This avoids selecting a year (like 2026) that only has partial/incomplete data for a single province.
        $dbLatestYear = \Illuminate\Support\Facades\DB::table('pdrb_sumatera_provinsi')
            ->select('tahun')
            ->groupBy('tahun')
            ->havingRaw('COUNT(DISTINCT provinsi_id) >= 5')
            ->orderBy('tahun', 'desc')
            ->value('tahun');

        if (!$dbLatestYear) {
            $dbLatestYear = \Illuminate\Support\Facades\DB::table('pdrb_sumatera_provinsi')->max('tahun');
        }

        if ($dbLatestYear) {
            $latestYear = $dbLatestYear;
            $provinsiInvestasi = \Illuminate\Support\Facades\DB::table('pdrb_sumatera_provinsi')
                ->join('provinsi', 'pdrb_sumatera_provinsi.provinsi_id', '=', 'provinsi.provinsi_id')
                ->where('pdrb_sumatera_provinsi.tahun', $latestYear)
                ->select('provinsi.nama_provinsi', \Illuminate\Support\Facades\DB::raw('SUM(nilai_pdrb) as total_investasi'))
                ->groupBy('provinsi.nama_provinsi')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [strtoupper(trim($item->nama_provinsi)) => (float)$item->total_investasi];
                })
                ->toArray();

            // Calculate metrics from database
            if (!empty($provinsiInvestasi)) {
                $totalPdrb = array_sum($provinsiInvestasi);
                $totalRealisasi = $totalPdrb * 0.10; // 10% rate
                
                $pdrbTertinggiNilai = max($provinsiInvestasi);
                $pdrbTertinggiNama = array_search($pdrbTertinggiNilai, $provinsiInvestasi);
            }
        }

        // 2. Project counts
        $jumlahProyek = \Illuminate\Support\Facades\DB::table('projects')->count();

        // 3. Top 5 sectors
        $topSectors = \Illuminate\Support\Facades\DB::table('pdrb_sumatera_provinsi')
            ->join('sektor', 'pdrb_sumatera_provinsi.sektor_id', '=', 'sektor.sektor_id')
            ->where('pdrb_sumatera_provinsi.tahun', $latestYear)
            ->select('sektor.nama_sektor', \Illuminate\Support\Facades\DB::raw('SUM(nilai_pdrb) as total_pdrb'))
            ->groupBy('sektor.nama_sektor')
            ->orderBy('total_pdrb', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nama_sektor' => trim($item->nama_sektor),
                    'total_pdrb' => (float)$item->total_pdrb
                ];
            })
            ->toArray();

        // 4. Trends data
        $trendsData = \Illuminate\Support\Facades\DB::table('pdrb_sumatera_provinsi')
            ->join('provinsi', 'pdrb_sumatera_provinsi.provinsi_id', '=', 'provinsi.provinsi_id')
            ->select('provinsi.nama_provinsi', 'pdrb_sumatera_provinsi.tahun', \Illuminate\Support\Facades\DB::raw('SUM(nilai_pdrb) as total_pdrb'))
            ->groupBy('provinsi.nama_provinsi', 'pdrb_sumatera_provinsi.tahun')
            ->orderBy('pdrb_sumatera_provinsi.tahun')
            ->get()
            ->groupBy(function($item) {
                return strtoupper(trim($item->nama_provinsi));
            })
            ->map(function($group) {
                return $group->map(function($item) {
                    $pdrb = (float)$item->total_pdrb;
                    return [
                        'tahun' => (int)$item->tahun,
                        'pdrb' => $pdrb,
                        'investasi' => $pdrb * 0.10,
                        'ekspor' => $pdrb * 0.22,
                        'impor' => $pdrb * 0.15
                    ];
                })->values()->toArray();
            })
            ->toArray();

    } catch (\Throwable $e) {
        // Fallback if db query fails
    }
    
    if (empty($provinsiInvestasi)) {
        $provinsiInvestasi = [
            'ACEH' => 180000000000000,
            'SUMATERA UTARA' => 350000000000000,
            'SUMATERA BARAT' => 120000000000000,
            'RIAU' => 280000000000000,
            'JAMBI' => 90000000000000,
            'SUMATERA SELATAN' => 240000000000000,
            'BENGKULU' => 45000000000000,
            'LAMPUNG' => 150000000000000,
            'KEPULAUAN BANGKA BELITUNG' => 60000000000000,
            'KEPULAUAN RIAU' => 110000000000000,
        ];
        $totalPdrb = array_sum($provinsiInvestasi);
        $totalRealisasi = $totalPdrb * 0.10;
    }

    if (empty($topSectors)) {
        $topSectors = [
            ['nama_sektor' => 'PERTANIAN, KEHUTANAN, DAN PERIKANAN', 'total_pdrb' => 450000000000000],
            ['nama_sektor' => 'INDUSTRI PENGOLAHAN', 'total_pdrb' => 380000000000000],
            ['nama_sektor' => 'PERTAMBANGAN DAN PENGGALIAN', 'total_pdrb' => 310000000000000],
            ['nama_sektor' => 'PERDAGANGAN BESAR DAN ECERAN', 'total_pdrb' => 250000000000000],
            ['nama_sektor' => 'KONSTRUKSI', 'total_pdrb' => 190000000000000],
        ];
    }

    if (empty($trendsData)) {
        foreach (array_keys($provinsiInvestasi) as $provName) {
            $baseVal = $provinsiInvestasi[$provName];
            $trendsData[$provName] = [
                ['tahun' => 2021, 'pdrb' => $baseVal * 0.85, 'investasi' => $baseVal * 0.85 * 0.10, 'ekspor' => $baseVal * 0.85 * 0.22, 'impor' => $baseVal * 0.85 * 0.15],
                ['tahun' => 2022, 'pdrb' => $baseVal * 0.90, 'investasi' => $baseVal * 0.90 * 0.10, 'ekspor' => $baseVal * 0.90 * 0.22, 'impor' => $baseVal * 0.90 * 0.15],
                ['tahun' => 2023, 'pdrb' => $baseVal * 0.93, 'investasi' => $baseVal * 0.93 * 0.10, 'ekspor' => $baseVal * 0.93 * 0.22, 'impor' => $baseVal * 0.93 * 0.15],
                ['tahun' => 2024, 'pdrb' => $baseVal * 0.97, 'investasi' => $baseVal * 0.97 * 0.10, 'ekspor' => $baseVal * 0.97 * 0.22, 'impor' => $baseVal * 0.97 * 0.15],
                ['tahun' => 2025, 'pdrb' => $baseVal, 'investasi' => $baseVal * 0.10, 'ekspor' => $baseVal * 0.22, 'impor' => $baseVal * 0.15],
            ];
        }
    }

    return view('landing.home', compact(
        'provinsiInvestasi', 
        'latestYear', 
        'totalRealisasi', 
        'pdrbTertinggiNama', 
        'pdrbTertinggiNilai', 
        'jumlahProyek',
        'topSectors',
        'trendsData'
    ));
})->name('home');

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

Route::get('/not-verified', function () {
    return view('errors.not-verified');
})->name('not-verified');


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
    Route::get('not-verified', function() { return view('errors.not-verified'); });
});
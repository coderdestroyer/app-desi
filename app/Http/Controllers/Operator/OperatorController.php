<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class OperatorController extends Controller
{
    public static function logActivity($module, $action, $desc)
    {

        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'desc' => $desc
        ]);
    }

    private function getLatestSummaryStatus(string $modelClass, array $allowedKabIds)
    {
        $query = $modelClass::query();
        if (!empty($allowedKabIds)) {
            $query->whereIn('kabupaten_id', $allowedKabIds);
        }

        $latest = $query->latest('updated_at')->first();

        if (!$latest || !$latest->updated_at) {
            return [
                'date' => now()->format('d M Y'),
                'action' => 'Belum Ada Data',
                'color' => 'bg-slate-100 text-slate-700 border-slate-200'
            ];
        }

        return [
            'date' => $latest->updated_at->format('d M Y'),
            'action' => 'Ter-Sync Otomatis',
            'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        ];
    }

    public function selection()
    {
        $user = Auth::user();
        $kabupatens = $this->getAuthorizedKabupatens($user);
        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        return view('operator.selection', compact('kabupatens', 'sektors'));
    }

    public function index()
    {
        $user = Auth::user();
        $kabupatens = $this->getAuthorizedKabupatens($user);
        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();
        $allowedKabIds = $kabupatens->pluck('kab_id')->toArray();

        if ($user->isAdmin() || empty($allowedKabIds)) {
            $countLq = \App\Models\SummaryLqResult::count();
            $countSs = \App\Models\SummaryShiftShareResult::count();
            $countTipologi = \App\Models\SummaryTipologiSektorResult::count();
            $countKlassen = \App\Models\SummaryKlassenResult::count();
        } else {
            $countLq = \App\Models\SummaryLqResult::whereIn('kabupaten_id', $allowedKabIds)->count();
            $countSs = \App\Models\SummaryShiftShareResult::whereIn('kabupaten_id', $allowedKabIds)->count();
            $countTipologi = \App\Models\SummaryTipologiSektorResult::whereIn('kabupaten_id', $allowedKabIds)->count();
            $countKlassen = \App\Models\SummaryKlassenResult::whereIn('kabupaten_id', $allowedKabIds)->count();
        }

        $totalAnalisa = $countLq + $countSs + $countTipologi + $countKlassen;

        $statusLq = $this->getLatestSummaryStatus(\App\Models\SummaryLqResult::class, $allowedKabIds);
        $statusSs = $this->getLatestSummaryStatus(\App\Models\SummaryShiftShareResult::class, $allowedKabIds);
        $statusTipologi = $this->getLatestSummaryStatus(\App\Models\SummaryTipologiSektorResult::class, $allowedKabIds);
        $statusKlassen = $this->getLatestSummaryStatus(\App\Models\SummaryKlassenResult::class, $allowedKabIds);

        $activityLogs = ActivityLog::whereNotIn('module', ['Autentikasi'])
            ->latest()
            ->take(10)
            ->get();

        return view('operator.potensi_unggulan.dashboard', compact(
            'countLq', 'countSs', 'countTipologi', 'countKlassen', 'totalAnalisa',
            'statusLq', 'statusSs', 'statusTipologi', 'statusKlassen',
            'activityLogs', 'kabupatens', 'sektors'
        ));
    }

    /**
     * Mengambil daftar Provinsi yang berhak diakses oleh operator berdasarkan Regional Scope.
     */
    private function getAuthorizedProvinsis($user)
    {
        if ($user->isAdmin()) {
            return \App\Models\Provinsi::orderBy('nama_provinsi')->get();
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('user_wilayah_scopes')) {
            return \App\Models\Provinsi::orderBy('nama_provinsi')->get();
        }

        $scopes = $user->wilayahScopes()->get();

        if ($scopes->isEmpty()) {
            return \App\Models\Provinsi::orderBy('nama_provinsi')->get();
        }

        $provIds = [];
        foreach ($scopes as $scope) {
            if ($scope->provinsi_id) {
                $provIds[] = $scope->provinsi_id;
            } elseif ($scope->kabupaten_id) {
                $kab = \App\Models\Kabupaten::find($scope->kabupaten_id);
                if ($kab && $kab->provinsi_id) {
                    $provIds[] = $kab->provinsi_id;
                }
            }
        }

        if (empty($provIds)) {
            return \App\Models\Provinsi::orderBy('nama_provinsi')->get();
        }

        return \App\Models\Provinsi::whereIn('provinsi_id', array_unique($provIds))
            ->orderBy('nama_provinsi')
            ->get();
    }

    /**
     * Mengambil daftar Kabupaten/Kota yang berhak diakses oleh operator berdasarkan Regional Scope.
     */
    private function getAuthorizedKabupatens($user)
    {
        if ($user->isAdmin()) {
            return \App\Models\Kabupaten::orderBy('nama_kabupaten')->get();
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('user_wilayah_scopes')) {
            return \App\Models\Kabupaten::orderBy('nama_kabupaten')->get();
        }

        $scopes = $user->wilayahScopes()->get();

        if ($scopes->isEmpty()) {
            return \App\Models\Kabupaten::orderBy('nama_kabupaten')->get();
        }

        $query = \App\Models\Kabupaten::query();

        $query->where(function ($q) use ($scopes) {
            foreach ($scopes as $scope) {
                if ($scope->kabupaten_id) {
                    $q->orWhere('kab_id', $scope->kabupaten_id);
                } elseif ($scope->provinsi_id) {
                    $q->orWhere('provinsi_id', $scope->provinsi_id);
                }
            }
        });

        return $query->orderBy('nama_kabupaten')->get();
    }

    /**
     * Inisiasi pembuatan data PDRB baru dari Modal.
     * Cek otorisasi scope dan cegah duplikasi (kabupaten_id + tahun).
     */
    public function initPdrb(Request $request)
    {
        $request->validate([
            'kabupaten_id' => 'required|exists:kabupaten,kab_id',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $user = Auth::user();

        // Otorisasi Hak Akses Wilayah Operator
        if (!$user->canAccessKabupaten($request->kabupaten_id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses otorisasi untuk menambahkan data PDRB pada daerah ini.');
        }

        $kabupaten = \App\Models\Kabupaten::find($request->kabupaten_id);

        // Cek apakah data PDRB (kabupaten_id + tahun) sudah ada di database
        $exists = \App\Models\PdrbSumateraKabupaten::where('kabupaten_id', $request->kabupaten_id)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Data PDRB untuk {$kabupaten->nama_kabupaten} Tahun {$request->tahun} sudah ada di database! Silakan gunakan tombol Edit pada tabel untuk mengubah nilainya.");
        }

        return redirect()->route('operator.pdrb.entry', [
            'kabupaten_id' => $request->kabupaten_id,
            'tahun' => $request->tahun,
        ])->with('info', "Silakan masukkan nilai PDRB per sektor untuk {$kabupaten->nama_kabupaten} Tahun {$request->tahun}.");
    }



    /**
     * Menyimpan nilai 17 sektor PDRB dari halaman khusus dedicated entry.
     */
    public function saveEntryPdrb(Request $request)
    {
        $request->validate([
            'kabupaten_id' => 'required|exists:kabupaten,kab_id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'sektor_values' => 'required|array',
        ]);

        $user = Auth::user();

        if (!$user->canAccessKabupaten($request->kabupaten_id)) {
            return redirect()->route('operator.pdrb.index')->with('error', 'Anda tidak memiliki hak akses otorisasi untuk mengubah data PDRB daerah ini.');
        }

        $kabupaten = \App\Models\Kabupaten::find($request->kabupaten_id);
        $savedCount = 0;

        foreach ($request->sektor_values as $sektorId => $nilai) {
            if ($nilai !== null && $nilai !== '') {
                \App\Models\PdrbSumateraKabupaten::updateOrCreate(
                    [
                        'kabupaten_id' => $request->kabupaten_id,
                        'sektor_id' => $sektorId,
                        'tahun' => $request->tahun,
                    ],
                    [
                        'nilai_pdrb' => (float) $nilai,
                    ]
                );
                $savedCount++;
            }
        }

        app(\App\Services\AnalysisSyncService::class)->syncKabupaten((int)$request->kabupaten_id, (int)$request->tahun);

        \Illuminate\Support\Facades\Cache::flush();

        self::logActivity(
            'Data PDRB',
            'diperbarui',
            "Menyimpan nilai PDRB {$kabupaten->nama_kabupaten} Tahun {$request->tahun} ({$savedCount} sektor terisi)"
        );

        return redirect()->route('operator.pdrb.index')->with('success', "Berhasil menyimpan nilai PDRB {$kabupaten->nama_kabupaten} Tahun {$request->tahun} ({$savedCount} sektor terisi)!");
    }

    /**
     * Menampilkan halaman Input / Kelola Data PDRB Daerah Operator.
     */
    public function pdrbIndex(Request $request)
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'own');

        $authorizedProvinsis = $this->getAuthorizedProvinsis($user);
        $allProvinsis = \App\Models\Provinsi::orderBy('nama_provinsi')->get();

        $allAuthorizedKabupatens = $this->getAuthorizedKabupatens($user);
        $allKabupatens = \App\Models\Kabupaten::orderBy('nama_kabupaten')->get();

        if ($tab === 'own') {
            $provinsis = $authorizedProvinsis;
            $activeKabupatens = $allAuthorizedKabupatens;
            $allowedKabIds = $allAuthorizedKabupatens->pluck('kab_id')->toArray();
        } else {
            $provinsis = $allProvinsis;
            $activeKabupatens = $allKabupatens;
            $allowedKabIds = $allKabupatens->pluck('kab_id')->toArray();
        }

        $selectedProvinsiId = $request->input('provinsi_id');
        if (!$selectedProvinsiId && $tab === 'own' && !$request->filled('search') && $provinsis->count() === 1) {
            $selectedProvinsiId = $provinsis->first()->provinsi_id;
        }

        if (!$selectedProvinsiId && $request->filled('kabupaten_id')) {
            $selectedKabupaten = \App\Models\Kabupaten::find($request->kabupaten_id);
            if ($selectedKabupaten && $selectedKabupaten->provinsi_id) {
                $selectedProvinsiId = $selectedKabupaten->provinsi_id;
            }
        }

        $kabupatens = $activeKabupatens;
        if ($selectedProvinsiId) {
            $kabupatens = $kabupatens->where('provinsi_id', $selectedProvinsiId);
        }

        $selectedKabupatenId = $request->input('kabupaten_id');
        if (!$selectedKabupatenId && $tab === 'own' && !$request->filled('search') && $activeKabupatens->count() === 1) {
            $selectedKabupatenId = $activeKabupatens->first()->kab_id;
        }

        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        $query = \App\Models\PdrbSumateraKabupaten::selectRaw('kabupaten_id, tahun, COUNT(*) as total_sektor, SUM(nilai_pdrb) as total_pdrb')
            ->whereIn('kabupaten_id', $allowedKabIds)
            ->groupBy('kabupaten_id', 'tahun')
            ->with(['kabupaten.provinsi']);

        if (!$request->filled('search')) {
            if ($selectedProvinsiId) {
                $query->whereHas('kabupaten', function ($q) use ($selectedProvinsiId) {
                    $q->where('provinsi_id', $selectedProvinsiId);
                });
            }

            if ($selectedKabupatenId) {
                $query->where('kabupaten_id', $selectedKabupatenId);
            }
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('search')) {
            $searchLower = mb_strtolower(trim($request->search));
            $query->where(function ($q) use ($searchLower) {
                $q->whereHas('kabupaten', function ($kq) use ($searchLower) {
                    $kq->whereRaw('LOWER(nama_kabupaten) LIKE ?', ["%{$searchLower}%"])
                       ->orWhereHas('provinsi', function ($pq) use ($searchLower) {
                           $pq->whereRaw('LOWER(nama_provinsi) LIKE ?', ["%{$searchLower}%"]);
                       });
                });
                $q->orWhere('tahun', 'LIKE', "%{$searchLower}%");
            });
        }

        $pdrbGroups = $query->orderBy('tahun', 'desc')
            ->orderBy('kabupaten_id')
            ->paginate(12)
            ->withQueryString();

        $availableYears = \App\Models\PdrbSumateraKabupaten::whereIn('kabupaten_id', $allowedKabIds)
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $ownCount = \App\Models\PdrbSumateraKabupaten::selectRaw('kabupaten_id, tahun')
            ->whereIn('kabupaten_id', $allAuthorizedKabupatens->pluck('kab_id')->toArray())
            ->groupBy('kabupaten_id', 'tahun')
            ->get()
            ->count();

        $allCount = \App\Models\PdrbSumateraKabupaten::selectRaw('kabupaten_id, tahun')
            ->groupBy('kabupaten_id', 'tahun')
            ->get()
            ->count();

        return view('operator.potensi_unggulan.pdrb', compact(
            'pdrbGroups', 'provinsis', 'kabupatens', 'sektors', 'availableYears',
            'selectedProvinsiId', 'selectedKabupatenId', 'tab', 'ownCount', 'allCount'
        ));
    }

    /**
     * Menampilkan Halaman Khusus Dedicated Input / Edit Nilai Sektor PDRB.
     */
    public function entryPdrb($kabupaten_id, $tahun)
    {
        $user = Auth::user();
        $kabupaten = \App\Models\Kabupaten::findOrFail($kabupaten_id);

        $isReadOnly = !$user->canAccessKabupaten($kabupaten_id);
        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        $existingValues = \App\Models\PdrbSumateraKabupaten::where('kabupaten_id', $kabupaten_id)
            ->where('tahun', $tahun)
            ->pluck('nilai_pdrb', 'sektor_id')
            ->toArray();

        return view('operator.potensi_unggulan.pdrb_entry', compact(
            'kabupaten', 'tahun', 'sektors', 'existingValues', 'isReadOnly'
        ));
    }

    /**
     * Menampilkan data PDB Nasional untuk Operator (Read Only Access).
     */
    public function pdbNasionalIndex(Request $request)
    {
        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        $query = \App\Models\PdbNasional::selectRaw('tahun, COUNT(*) as total_sektor, SUM(nilai) as total_pdb')
            ->groupBy('tahun');

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $pdbGroups = $query->orderBy('tahun', 'desc')
            ->paginate(15)
            ->withQueryString();

        $availableYears = \App\Models\PdbNasional::distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('operator.potensi_unggulan.pdb_nasional', compact(
            'pdbGroups', 'sektors', 'availableYears'
        ));
    }

    /**
     * Menampilkan detail rincian 17 Sektor PDB Nasional untuk Operator (Read Only Access).
     */
    public function pdbNasionalDetail($tahun)
    {
        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        $existingValues = \App\Models\PdbNasional::where('tahun', $tahun)
            ->pluck('nilai', 'sektor_id')
            ->toArray();

        return view('operator.potensi_unggulan.pdb_nasional_detail', compact(
            'tahun', 'sektors', 'existingValues'
        ));
    }

    /**
     * Menampilkan daftar data PDRB Provinsi untuk Operator.
     */
    public function pdrbProvinsiIndex(Request $request)
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'own');

        $authorizedProvinsis = $this->getAuthorizedProvinsis($user);
        $allProvinsis = \App\Models\Provinsi::orderBy('nama_provinsi')->get();

        if ($tab === 'own') {
            $provinsis = $authorizedProvinsis;
            $allowedProvIds = $authorizedProvinsis->pluck('provinsi_id')->toArray();
        } else {
            $provinsis = $allProvinsis;
            $allowedProvIds = $allProvinsis->pluck('provinsi_id')->toArray();
        }

        $selectedProvinsiId = $request->input('provinsi_id');
        if (!$selectedProvinsiId && $tab === 'own' && !$request->filled('search') && $provinsis->count() === 1) {
            $selectedProvinsiId = $provinsis->first()->provinsi_id;
        }

        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        $query = \App\Models\PdrbSumateraProvinsi::selectRaw('provinsi_id, tahun, COUNT(*) as total_sektor, SUM(nilai_pdrb) as total_pdrb')
            ->whereIn('provinsi_id', $allowedProvIds)
            ->groupBy('provinsi_id', 'tahun')
            ->with('provinsi');

        if (!$request->filled('search')) {
            if ($selectedProvinsiId) {
                $query->where('provinsi_id', $selectedProvinsiId);
            }
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('search')) {
            $searchLower = mb_strtolower(trim($request->search));
            $query->where(function ($q) use ($searchLower) {
                $q->whereHas('provinsi', function ($pq) use ($searchLower) {
                    $pq->whereRaw('LOWER(nama_provinsi) LIKE ?', ["%{$searchLower}%"])
                       ->orWhereHas('kabupaten', function ($kq) use ($searchLower) {
                           $kq->whereRaw('LOWER(nama_kabupaten) LIKE ?', ["%{$searchLower}%"]);
                       });
                });
                $q->orWhere('tahun', 'LIKE', "%{$searchLower}%");
            });
        }

        $pdrbGroups = $query->orderBy('tahun', 'desc')
            ->orderBy('provinsi_id')
            ->paginate(12)
            ->withQueryString();

        $availableYears = \App\Models\PdrbSumateraProvinsi::whereIn('provinsi_id', $allowedProvIds)
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $ownCount = \App\Models\PdrbSumateraProvinsi::selectRaw('provinsi_id, tahun')
            ->whereIn('provinsi_id', $authorizedProvinsis->pluck('provinsi_id')->toArray())
            ->groupBy('provinsi_id', 'tahun')
            ->get()
            ->count();

        $allCount = \App\Models\PdrbSumateraProvinsi::selectRaw('provinsi_id, tahun')
            ->groupBy('provinsi_id', 'tahun')
            ->get()
            ->count();

        return view('operator.potensi_unggulan.pdrb_provinsi', compact(
            'pdrbGroups', 'provinsis', 'sektors', 'availableYears',
            'selectedProvinsiId', 'tab', 'ownCount', 'allCount'
        ));
    }

    /**
     * Inisiasi pembuatan data PDRB Provinsi baru dari Modal.
     */
    public function initPdrbProvinsi(Request $request)
    {
        $request->validate([
            'provinsi_id' => 'required|exists:provinsi,provinsi_id',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $user = Auth::user();

        if (!$user->canManageProvinsi($request->provinsi_id)) {
            return redirect()->back()->with('error', 'Akun Anda memiliki otorisasi tingkat Kabupaten/Kota, sehingga hanya berhak melihat (Read Only) Data PDRB Provinsi.');
        }

        $provinsi = \App\Models\Provinsi::find($request->provinsi_id);

        $exists = \App\Models\PdrbSumateraProvinsi::where('provinsi_id', $request->provinsi_id)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Data PDRB {$provinsi->nama_provinsi} Tahun {$request->tahun} sudah ada di database! Silakan gunakan tombol Edit untuk mengedit nilainya.");
        }

        return redirect()->route('operator.pdrb-provinsi.entry', [
            'provinsi_id' => $request->provinsi_id,
            'tahun' => $request->tahun,
        ])->with('info', "Silakan masukkan nilai PDRB per sektor untuk {$provinsi->nama_provinsi} Tahun {$request->tahun}.");
    }

    /**
     * Menampilkan halaman dedicated input/edit nilai 17 sektor PDRB Provinsi.
     */
    public function entryPdrbProvinsi($provinsi_id, $tahun)
    {
        $user = Auth::user();

        $provinsi = \App\Models\Provinsi::findOrFail($provinsi_id);
        $isReadOnly = !$user->canManageProvinsi($provinsi_id);

        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        $existingValues = \App\Models\PdrbSumateraProvinsi::where('provinsi_id', $provinsi_id)
            ->where('tahun', $tahun)
            ->pluck('nilai_pdrb', 'sektor_id')
            ->toArray();

        return view('operator.potensi_unggulan.pdrb_provinsi_entry', compact(
            'provinsi', 'tahun', 'sektors', 'existingValues', 'isReadOnly'
        ));
    }

    /**
     * Menyimpan nilai 17 sektor PDRB Provinsi.
     */
    public function saveEntryPdrbProvinsi(Request $request)
    {
        $request->validate([
            'provinsi_id' => 'required|exists:provinsi,provinsi_id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'sektor_values' => 'required|array',
        ]);

        $user = Auth::user();

        if (!$user->canManageProvinsi($request->provinsi_id)) {
            return redirect()->route('operator.pdrb-provinsi.index')->with('error', 'Akun Anda memiliki otorisasi tingkat Kabupaten/Kota, sehingga hanya berhak melihat (Read Only) Data PDRB Provinsi.');
        }

        $provinsi = \App\Models\Provinsi::find($request->provinsi_id);
        $savedCount = 0;

        foreach ($request->sektor_values as $sektorId => $nilai) {
            if ($nilai !== null && $nilai !== '') {
                \App\Models\PdrbSumateraProvinsi::updateOrCreate(
                    [
                        'provinsi_id' => $request->provinsi_id,
                        'sektor_id' => $sektorId,
                        'tahun' => $request->tahun,
                    ],
                    [
                        'nilai_pdrb' => (float) $nilai,
                    ]
                );
                $savedCount++;
            }
        }

        app(\App\Services\AnalysisSyncService::class)->syncProvinsiAndChildren((int)$request->provinsi_id, (int)$request->tahun);

        \Illuminate\Support\Facades\Cache::flush();

        self::logActivity(
            'Data PDRB Provinsi',
            'diperbarui',
            "Menyimpan nilai PDRB Provinsi {$provinsi->nama_provinsi} Tahun {$request->tahun} ({$savedCount} sektor terisi)"
        );

        return redirect()->route('operator.pdrb-provinsi.index')->with('success', "Berhasil menyimpan nilai PDRB Provinsi {$provinsi->nama_provinsi} Tahun {$request->tahun} ({$savedCount} sektor terisi)!");
    }

    /**
     * Menghapus seluruh data PDRB per Provinsi & Tahun.
     */
    public function destroyGroupPdrbProvinsi($provinsi_id, $tahun)
    {
        $user = Auth::user();

        if (!$user->canManageProvinsi($provinsi_id)) {
            return redirect()->back()->with('error', 'Akun Anda memiliki otorisasi tingkat Kabupaten/Kota, sehingga tidak berhak menghapus Data PDRB Provinsi.');
        }

        $provinsi = \App\Models\Provinsi::find($provinsi_id);
        $namaProv = $provinsi->nama_provinsi ?? 'Provinsi';

        \App\Models\PdrbSumateraProvinsi::where('provinsi_id', $provinsi_id)
            ->where('tahun', $tahun)
            ->delete();

        app(\App\Services\AnalysisSyncService::class)->syncProvinsiAndChildren((int)$provinsi_id, (int)$tahun);

        \Illuminate\Support\Facades\Cache::flush();

        self::logActivity(
            'Data PDRB Provinsi',
            'dihapus',
            "Menghapus seluruh data PDRB Provinsi {$namaProv} Tahun {$tahun}"
        );

        return redirect()->back()->with('success', "Seluruh data PDRB Provinsi {$namaProv} Tahun {$tahun} berhasil dihapus.");
    }

    /**
     * Menghapus seluruh data PDRB per Kabupaten & Tahun.
     */
    public function destroyGroupPdrb($kabupaten_id, $tahun)
    {
        $user = Auth::user();

        if (!$user->canAccessKabupaten($kabupaten_id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses otorisasi untuk menghapus data PDRB daerah ini.');
        }

        $kabupaten = \App\Models\Kabupaten::find($kabupaten_id);
        $namaKab = $kabupaten->nama_kabupaten ?? 'Kabupaten';

        \App\Models\PdrbSumateraKabupaten::where('kabupaten_id', $kabupaten_id)
            ->where('tahun', $tahun)
            ->delete();

        app(\App\Services\AnalysisSyncService::class)->syncKabupaten($kabupaten_id, (int)$tahun);

        \Illuminate\Support\Facades\Cache::flush();

        self::logActivity(
            'Data PDRB',
            'dihapus',
            "Menghapus seluruh data PDRB {$namaKab} Tahun {$tahun}"
        );

        return redirect()->back()->with('success', "Seluruh data PDRB {$namaKab} Tahun {$tahun} berhasil dihapus.");
    }

    /**
     * Menghapus record PDRB tunggal oleh Operator.
     */
    public function destroyPdrb($id)
    {
        $user = Auth::user();
        $pdrb = \App\Models\PdrbSumateraKabupaten::findOrFail($id);

        if (!$user->canAccessKabupaten($pdrb->kabupaten_id)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses otorisasi untuk menghapus data PDRB daerah ini.');
        }

        $namaKab = $pdrb->kabupaten->nama_kabupaten ?? 'Kabupaten';
        $tahun = $pdrb->tahun;

        $pdrb->delete();

        \Illuminate\Support\Facades\Cache::flush();

        self::logActivity(
            'Data PDRB',
            'dihapus',
            "Menghapus record PDRB {$namaKab} Tahun {$tahun}"
        );

        return redirect()->back()->with('success', "Record PDRB {$namaKab} Tahun {$tahun} berhasil dihapus.");
    }

    public function profile()
    {
        return view('operator.profile');
    }

    public function aktivitas(Request $request)
    {
        $activityLogs = ActivityLog::whereNotIn('module', ['Autentikasi'])
            ->latest()
            ->get();
        $collection = collect($activityLogs);

        $search = $request->input('search');
        $month = $request->input('month');
        $year = $request->input('year');

        $filteredLogs = $collection->filter(function ($log) use ($search, $month, $year) {
            $matchSearch = true;
            $matchMonth = true;
            $matchYear = true;

            // Pencarian text
            if ($search) {
                $searchLower = strtolower($search);
                $matchSearch = str_contains(strtolower($log->module), $searchLower) ||
                               str_contains(strtolower($log->action), $searchLower) ||
                               str_contains(strtolower($log->desc), $searchLower);
            }

            // Filter bulan
            if ($month) {
                $matchMonth = $log->created_at->format('m') === str_pad($month, 2, '0', STR_PAD_LEFT);
            }

            // Filter tahun
            if ($year) {
                $matchYear = $log->created_at->format('Y') == $year;
            }

            return $matchSearch && $matchMonth && $matchYear;
        });

        // Pagination
        $perPage = 10;
        $page = $request->get('page', 1);
        
        $paginatedLogs = new LengthAwarePaginator(
            $filteredLogs->forPage($page, $perPage),
            $filteredLogs->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $baseYears = range(date('Y'), 2020);
        $availableYears = collect(array_merge($activityLogs->pluck('created_at')->map(fn($d) => (int)$d->format('Y'))->toArray(), $baseYears))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return view('operator.aktivitas', compact('paginatedLogs', 'search', 'month', 'year', 'availableYears'));
    }

    public function settings()
    {
        $authLogs = \App\Models\ActivityLog::where('user_id', Auth::id())
            ->where('module', 'Autentikasi')
            ->latest()
            ->limit(50)
            ->get();
            
        return view('operator.settings', compact('authLogs'));
    }

    // Aksi update profil nyata
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            // Hapus avatar lama jika ada
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Simpan avatar baru
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        // Catat aktivitas
        self::logActivity('Profile', 'Update', 'Memperbarui profil operator: ' . $user->name);

        return back()->with('success', 'Data profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        // Catat aktivitas
        self::logActivity('Profile', 'Change Password', 'Mengubah password akun operator');

        return back()->with('success', 'Password akun berhasil diubah!');
    }

    public function toggle2FA(Request $request)
    {
        $user = Auth::user();
        $newStatus = !$user->two_factor_enabled;

        $user->update([
            'two_factor_enabled' => $newStatus,
        ]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', 'Status Autentikasi Dua Faktor (2FA) berhasil ' . $statusText . '!');
    }
}


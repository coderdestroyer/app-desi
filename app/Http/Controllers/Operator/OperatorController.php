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

    private function getLatestStatusByType(string $type)
    {
        $latest = \App\Models\AnalysisResult::where('type', $type)->latest('updated_at')->first();
        if (!$latest) {
            return ['date' => now()->format('d M Y'), 'action' => 'Real-Time', 'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200'];
        }

        $action = ($latest->created_at == $latest->updated_at) ? 'ditambah' : 'diperbarui';

        $color = match ($action) {
            'ditambah' => 'bg-green-100 text-green-700 border-green-200',
            'diperbarui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            default => 'bg-slate-100 text-slate-700 border-slate-200'
        };

        return [
            'date' => $latest->updated_at ? $latest->updated_at->format('d M Y') : '-',
            'action' => ucfirst($action),
            'color' => $color,
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

        $savedLq = \App\Models\AnalysisResult::where('type', 'lq')->count();
        $savedSs = \App\Models\AnalysisResult::where('type', 'shift_share')->count();
        $savedTipologi = \App\Models\AnalysisResult::where('type', 'tipologi_sektor')->count();
        $savedKlassen = \App\Models\AnalysisResult::where('type', 'tipologi_klassen')->count();

        $totalSektorCount = $kabupatens->count() * 17;

        $countLq = $savedLq > 0 ? $savedLq : $totalSektorCount;
        $countSs = $savedSs > 0 ? $savedSs : $totalSektorCount;
        $countTipologi = $savedTipologi > 0 ? $savedTipologi : $totalSektorCount;
        $countKlassen = $savedKlassen > 0 ? $savedKlassen : $totalSektorCount;

        $totalAnalisa = $countLq + $countSs + $countTipologi + $countKlassen;

        $statusLq = $this->getLatestStatusByType('lq');
        $statusSs = $this->getLatestStatusByType('shift_share');
        $statusTipologi = $this->getLatestStatusByType('tipologi_sektor');
        $statusKlassen = $this->getLatestStatusByType('tipologi_klassen');

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
     * Menampilkan Halaman Khusus Dedicated Input / Edit Nilai Sektor PDRB.
     */
    public function entryPdrb($kabupaten_id, $tahun)
    {
        $user = Auth::user();

        if (!$user->canAccessKabupaten($kabupaten_id)) {
            return redirect()->route('operator.pdrb.index')->with('error', 'Anda tidak memiliki hak akses otorisasi untuk mengelola data PDRB daerah ini.');
        }

        $kabupaten = \App\Models\Kabupaten::findOrFail($kabupaten_id);
        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        $existingValues = \App\Models\PdrbSumateraKabupaten::where('kabupaten_id', $kabupaten_id)
            ->where('tahun', $tahun)
            ->pluck('nilai_pdrb', 'sektor_id')
            ->toArray();

        return view('operator.potensi_unggulan.pdrb_entry', compact(
            'kabupaten', 'tahun', 'sektors', 'existingValues'
        ));
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
        $kabupatens = $this->getAuthorizedKabupatens($user);
        $kabIds = $kabupatens->pluck('kab_id')->toArray();
        $sektors = \App\Models\Sektor::orderBy('sektor_id')->get();

        $query = \App\Models\PdrbSumateraKabupaten::selectRaw('kabupaten_id, tahun, COUNT(*) as total_sektor, SUM(nilai_pdrb) as total_pdrb')
            ->whereIn('kabupaten_id', $kabIds)
            ->groupBy('kabupaten_id', 'tahun')
            ->with('kabupaten');

        if ($request->filled('kabupaten_id')) {
            $query->where('kabupaten_id', $request->kabupaten_id);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        $pdrbGroups = $query->orderBy('tahun', 'desc')
            ->orderBy('kabupaten_id')
            ->paginate(12)
            ->withQueryString();

        $availableYears = \App\Models\PdrbSumateraKabupaten::whereIn('kabupaten_id', $kabIds)
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('operator.potensi_unggulan.pdrb', compact(
            'pdrbGroups', 'kabupatens', 'sektors', 'availableYears'
        ));
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


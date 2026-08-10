<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\PdrbSumateraKabupaten;
use App\Models\PdrbSumateraProvinsi;
use App\Models\Provinsi;
use App\Models\Sektor;
use Illuminate\Http\Request;

class AdminPdrbController extends Controller
{
    /**
     * Menampilkan daftar pengelompokan data PDRB per Daerah & Tahun untuk Admin (Kabupaten/Kota atau Provinsi).
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 'kabupaten');
        $provinsis = Provinsi::orderBy('nama_provinsi')->get();
        $sektors = Sektor::orderBy('sektor_id')->get();
        $selectedProvinsiId = $request->input('provinsi_id');

        if ($type === 'provinsi') {
            $query = PdrbSumateraProvinsi::selectRaw('provinsi_id, tahun, COUNT(*) as total_sektor, SUM(nilai_pdrb) as total_pdrb')
                ->groupBy('provinsi_id', 'tahun')
                ->with('provinsi');

            if ($selectedProvinsiId) {
                $query->where('provinsi_id', $selectedProvinsiId);
            }

            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }

            if ($request->filled('search')) {
                $search = strtolower(trim($request->search));
                $query->where(function ($q) use ($search) {
                    $q->whereHas('provinsi', function ($pq) use ($search) {
                        $pq->whereRaw('LOWER(nama_provinsi) LIKE ?', ["%{$search}%"]);
                    });
                    if (is_numeric($search)) {
                        $q->orWhere('tahun', (int) $search);
                    }
                });
            }

            $pdrbGroups = $query->orderBy('tahun', 'desc')
                ->orderBy('provinsi_id')
                ->paginate(15)
                ->withQueryString();

            $availableYears = PdrbSumateraProvinsi::distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun');

            $kabupatens = collect();
        } else {
            if (!$selectedProvinsiId && $request->filled('kabupaten_id')) {
                $selectedKabupaten = Kabupaten::find($request->kabupaten_id);
                if ($selectedKabupaten && $selectedKabupaten->provinsi_id) {
                    $selectedProvinsiId = $selectedKabupaten->provinsi_id;
                }
            }

            $kabupatensQuery = Kabupaten::with('provinsi');
            if ($selectedProvinsiId) {
                $kabupatensQuery->where('provinsi_id', $selectedProvinsiId);
            }
            $kabupatens = $kabupatensQuery->orderBy('nama_kabupaten')->get();

            $query = PdrbSumateraKabupaten::selectRaw('kabupaten_id, tahun, COUNT(*) as total_sektor, SUM(nilai_pdrb) as total_pdrb')
                ->groupBy('kabupaten_id', 'tahun')
                ->with(['kabupaten.provinsi']);

            if ($selectedProvinsiId) {
                $query->whereHas('kabupaten', function ($q) use ($selectedProvinsiId) {
                    $q->where('provinsi_id', $selectedProvinsiId);
                });
            }

            if ($request->filled('kabupaten_id')) {
                $query->where('kabupaten_id', $request->kabupaten_id);
            }

            if ($request->filled('tahun')) {
                $query->where('tahun', $request->tahun);
            }

            if ($request->filled('search')) {
                $search = strtolower(trim($request->search));
                $query->where(function ($q) use ($search) {
                    $q->whereHas('kabupaten', function ($kq) use ($search) {
                        $kq->whereRaw('LOWER(nama_kabupaten) LIKE ?', ["%{$search}%"])
                           ->orWhereHas('provinsi', function ($pq) use ($search) {
                               $pq->whereRaw('LOWER(nama_provinsi) LIKE ?', ["%{$search}%"]);
                           });
                    });
                    if (is_numeric($search)) {
                        $q->orWhere('tahun', (int) $search);
                    }
                });
            }

            $pdrbGroups = $query->orderBy('tahun', 'desc')
                ->orderBy('kabupaten_id')
                ->paginate(15)
                ->withQueryString();

            $availableYears = PdrbSumateraKabupaten::distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun');
        }

        $allKabupatenList = Kabupaten::orderBy('nama_kabupaten')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.pdrb.index', compact(
                    'pdrbGroups', 'provinsis', 'kabupatens', 'sektors', 'availableYears', 'selectedProvinsiId', 'type', 'allKabupatenList'
                ))->render(),
            ]);
        }

        return view('admin.pdrb.index', compact(
            'pdrbGroups', 'provinsis', 'kabupatens', 'sektors', 'availableYears', 'selectedProvinsiId', 'type', 'allKabupatenList'
        ));
    }

    /**
     * Inisiasi data PDRB Kabupaten/Kota baru oleh Admin dari Modal.
     */
    public function init(Request $request)
    {
        $request->validate([
            'kabupaten_id' => 'required|exists:kabupaten,kab_id',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $kabupaten = Kabupaten::findOrFail($request->kabupaten_id);

        $exists = PdrbSumateraKabupaten::where('kabupaten_id', $request->kabupaten_id)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Data PDRB untuk {$kabupaten->nama_kabupaten} Tahun {$request->tahun} sudah ada di database! Silakan gunakan tombol Edit untuk mengedit nilainya.");
        }

        return redirect()->route('admin.pdrb.entry', [
            'kabupaten_id' => $request->kabupaten_id,
            'tahun' => $request->tahun,
        ])->with('info', "Silakan masukkan nilai PDRB per sektor untuk {$kabupaten->nama_kabupaten} Tahun {$request->tahun}.");
    }

    /**
     * Inisiasi data PDRB Provinsi baru oleh Admin dari Modal.
     */
    public function initProvinsi(Request $request)
    {
        $request->validate([
            'provinsi_id' => 'required|exists:provinsi,provinsi_id',
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $provinsi = Provinsi::findOrFail($request->provinsi_id);

        $exists = PdrbSumateraProvinsi::where('provinsi_id', $request->provinsi_id)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Data PDRB Provinsi {$provinsi->nama_provinsi} Tahun {$request->tahun} sudah ada di database! Silakan gunakan tombol Edit untuk mengedit nilainya.");
        }

        return redirect()->route('admin.pdrb.provinsi-entry', [
            'provinsi_id' => $request->provinsi_id,
            'tahun' => $request->tahun,
        ])->with('info', "Silakan masukkan nilai PDRB per sektor untuk Provinsi {$provinsi->nama_provinsi} Tahun {$request->tahun}.");
    }

    /**
     * Menampilkan halaman dedicated input/edit nilai 17 sektor PDRB Kabupaten/Kota untuk Admin.
     */
    public function entry($kabupaten_id, $tahun)
    {
        $kabupaten = Kabupaten::findOrFail($kabupaten_id);
        $sektors = Sektor::orderBy('sektor_id')->get();

        $existingValues = PdrbSumateraKabupaten::where('kabupaten_id', $kabupaten_id)
            ->where('tahun', $tahun)
            ->pluck('nilai_pdrb', 'sektor_id')
            ->toArray();

        return view('admin.pdrb.entry', compact(
            'kabupaten', 'tahun', 'sektors', 'existingValues'
        ));
    }

    /**
     * Menampilkan halaman dedicated input/edit nilai 17 sektor PDRB Provinsi untuk Admin.
     */
    public function entryProvinsi($provinsi_id, $tahun)
    {
        $provinsi = Provinsi::findOrFail($provinsi_id);
        $sektors = Sektor::orderBy('sektor_id')->get();

        $existingValues = PdrbSumateraProvinsi::where('provinsi_id', $provinsi_id)
            ->where('tahun', $tahun)
            ->pluck('nilai_pdrb', 'sektor_id')
            ->toArray();

        return view('admin.pdrb.entry_provinsi', compact(
            'provinsi', 'tahun', 'sektors', 'existingValues'
        ));
    }

    /**
     * Menyimpan data nilai 17 sektor PDRB Kabupaten/Kota oleh Admin.
     */
    public function saveEntry(Request $request)
    {
        $request->validate([
            'kabupaten_id' => 'required|exists:kabupaten,kab_id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'sektor_values' => 'required|array',
        ]);

        $kabupaten = Kabupaten::findOrFail($request->kabupaten_id);
        $savedCount = 0;

        foreach ($request->sektor_values as $sektorId => $nilai) {
            if ($nilai !== null && $nilai !== '') {
                PdrbSumateraKabupaten::updateOrCreate(
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

        return redirect()->route('admin.pdrb.index', ['type' => 'kabupaten'])->with('success', "Berhasil menyimpan data PDRB {$kabupaten->nama_kabupaten} Tahun {$request->tahun} ({$savedCount} sektor terisi)!");
    }

    /**
     * Menyimpan data nilai 17 sektor PDRB Provinsi oleh Admin.
     */
    public function saveEntryProvinsi(Request $request)
    {
        $request->validate([
            'provinsi_id' => 'required|exists:provinsi,provinsi_id',
            'tahun' => 'required|integer|min:2000|max:2100',
            'sektor_values' => 'required|array',
        ]);

        $provinsi = Provinsi::findOrFail($request->provinsi_id);
        $savedCount = 0;

        foreach ($request->sektor_values as $sektorId => $nilai) {
            if ($nilai !== null && $nilai !== '') {
                PdrbSumateraProvinsi::updateOrCreate(
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

        return redirect()->route('admin.pdrb.index', ['type' => 'provinsi'])->with('success', "Berhasil menyimpan data PDRB Provinsi {$provinsi->nama_provinsi} Tahun {$request->tahun} ({$savedCount} sektor terisi)!");
    }

    /**
     * Menghapus seluruh data PDRB per Kabupaten & Tahun oleh Admin.
     */
    public function destroyGroup($kabupaten_id, $tahun)
    {
        $kabupaten = Kabupaten::find($kabupaten_id);
        $namaKab = $kabupaten->nama_kabupaten ?? 'Kabupaten';

        PdrbSumateraKabupaten::where('kabupaten_id', $kabupaten_id)
            ->where('tahun', $tahun)
            ->delete();

        app(\App\Services\AnalysisSyncService::class)->syncKabupaten($kabupaten_id, (int)$tahun);

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', "Seluruh data PDRB {$namaKab} Tahun {$tahun} berhasil dihapus dari database.");
    }

    /**
     * Menghapus seluruh data PDRB per Provinsi & Tahun oleh Admin.
     */
    public function destroyGroupProvinsi($provinsi_id, $tahun)
    {
        $provinsi = Provinsi::find($provinsi_id);
        $namaProv = $provinsi->nama_provinsi ?? 'Provinsi';

        PdrbSumateraProvinsi::where('provinsi_id', $provinsi_id)
            ->where('tahun', $tahun)
            ->delete();

        app(\App\Services\AnalysisSyncService::class)->syncProvinsiAndChildren((int)$provinsi_id, (int)$tahun);

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', "Seluruh data PDRB Provinsi {$namaProv} Tahun {$tahun} berhasil dihapus dari database.");
    }
}


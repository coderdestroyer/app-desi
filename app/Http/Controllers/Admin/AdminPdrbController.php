<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kabupaten;
use App\Models\PdrbSumateraKabupaten;
use App\Models\Provinsi;
use App\Models\Sektor;
use Illuminate\Http\Request;

class AdminPdrbController extends Controller
{
    /**
     * Menampilkan daftar pengelompokan data PDRB per Daerah & Tahun untuk Admin (Akses Seluruh Pulau Sumatera).
     */
    public function index(Request $request)
    {
        $provinsis = Provinsi::orderBy('nama_provinsi')->get();

        $selectedProvinsiId = $request->input('provinsi_id');
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

        $sektors = Sektor::orderBy('sektor_id')->get();

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
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('kabupaten', function ($kq) use ($search) {
                    $kq->where('nama_kabupaten', 'LIKE', "%{$search}%")
                       ->orWhereHas('provinsi', function ($pq) use ($search) {
                           $pq->where('nama_provinsi', 'LIKE', "%{$search}%");
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

        return view('admin.pdrb.index', compact(
            'pdrbGroups', 'provinsis', 'kabupatens', 'sektors', 'availableYears', 'selectedProvinsiId'
        ));
    }

    /**
     * Inisiasi data PDRB daerah baru oleh Admin dari Modal.
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
     * Menampilkan halaman dedicated input/edit nilai 17 sektor PDRB untuk Admin.
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
     * Menyimpan data nilai 17 sektor PDRB daerah oleh Admin.
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

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('admin.pdrb.index')->with('success', "Berhasil menyimpan data PDRB {$kabupaten->nama_kabupaten} Tahun {$request->tahun} ({$savedCount} sektor terisi)!");
    }

    /**
     * Menghapus seluruh data PDRB daerah per Kabupaten & Tahun oleh Admin.
     */
    public function destroyGroup($kabupaten_id, $tahun)
    {
        $kabupaten = Kabupaten::find($kabupaten_id);
        $namaKab = $kabupaten->nama_kabupaten ?? 'Kabupaten';

        PdrbSumateraKabupaten::where('kabupaten_id', $kabupaten_id)
            ->where('tahun', $tahun)
            ->delete();

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->back()->with('success', "Seluruh data PDRB {$namaKab} Tahun {$tahun} berhasil dihapus dari database.");
    }
}

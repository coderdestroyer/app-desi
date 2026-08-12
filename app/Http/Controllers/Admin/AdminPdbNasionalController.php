<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PdbNasional;
use App\Models\Sektor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminPdbNasionalController extends Controller
{
    /**
     * Menampilkan daftar pengelompokan data PDB Nasional per Tahun untuk Admin.
     */
    public function index(Request $request)
    {
        $sektors = Sektor::orderBy('sektor_id')->get();

        $query = PdbNasional::selectRaw('tahun, COUNT(*) as total_sektor, SUM(nilai) as total_pdb')
            ->groupBy('tahun');

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            if (is_numeric($search)) {
                $query->where('tahun', (int) $search);
            }
        }

        $pdbGroups = $query->orderBy('tahun', 'desc')
            ->paginate(15)
            ->withQueryString();

        $availableYears = PdbNasional::distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('admin.pdb-nasional.index', compact(
            'pdbGroups', 'sektors', 'availableYears'
        ));
    }

    /**
     * Inisiasi data PDB Nasional baru oleh Admin.
     */
    public function init(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
        ]);

        $exists = PdbNasional::where('tahun', $request->tahun)->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Data PDB Nasional Tahun {$request->tahun} sudah ada di database! Silakan gunakan tombol Edit untuk mengedit nilainya.");
        }

        return redirect()->route('admin.pdb-nasional.entry', [
            'tahun' => $request->tahun,
        ])->with('info', "Silakan masukkan nilai PDB Nasional per sektor untuk Tahun {$request->tahun}.");
    }

    /**
     * Menampilkan halaman dedicated input/edit nilai 17 sektor PDB Nasional.
     */
    public function entry($tahun)
    {
        $sektors = Sektor::orderBy('sektor_id')->get();

        $existingValues = PdbNasional::where('tahun', $tahun)
            ->pluck('nilai', 'sektor_id')
            ->toArray();

        return view('admin.pdb-nasional.entry', compact(
            'tahun', 'sektors', 'existingValues'
        ));
    }

    /**
     * Menyimpan data nilai 17 sektor PDB Nasional.
     */
    public function saveEntry(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2000|max:2100',
            'sektor_values' => 'required|array',
        ]);

        $sektors = Sektor::pluck('sektor_id');
        $filledCount = 0;

        foreach ($sektors as $sektorId) {
            $rawVal = $request->sektor_values[$sektorId] ?? null;
            $val = ($rawVal !== null && $rawVal !== '') ? (float) $rawVal : 0;
            if ($val > 0) {
                $filledCount++;
            }

            PdbNasional::updateOrCreate(
                [
                    'kode_wilayah' => '00',
                    'sektor_id' => $sektorId,
                    'tahun' => $request->tahun,
                ],
                [
                    'nilai' => $val,
                ]
            );
        }

        Cache::flush();

        if (function_exists('fastcgi_finish_request')) {
            session()->flash('success', "Berhasil menyimpan data PDB Nasional Tahun {$request->tahun} (17/17 sektor tersimpan, {$filledCount} sektor terisi > 0)! Hasil analisis makro sedang diperbarui.");
            redirect()->route('admin.pdb-nasional.index')->send();
            fastcgi_finish_request();
            app(\App\Services\AnalysisSyncService::class)->syncAllForYear((int)$request->tahun);
            exit;
        }

        app(\App\Services\AnalysisSyncService::class)->syncAllForYear((int)$request->tahun);

        return redirect()->route('admin.pdb-nasional.index')->with('success', "Berhasil menyimpan data PDB Nasional Tahun {$request->tahun} (17/17 sektor tersimpan, {$filledCount} sektor terisi > 0)!");
    }

    /**
     * Menghapus seluruh data PDB Nasional per Tahun.
     */
    public function destroyGroup($tahun)
    {
        PdbNasional::where('tahun', $tahun)->delete();
        Cache::flush();

        if (function_exists('fastcgi_finish_request')) {
            session()->flash('success', "Seluruh data PDB Nasional Tahun {$tahun} berhasil dihapus dari database.");
            redirect()->back()->send();
            fastcgi_finish_request();
            app(\App\Services\AnalysisSyncService::class)->syncAllForYear((int)$tahun);
            exit;
        }

        app(\App\Services\AnalysisSyncService::class)->syncAllForYear((int)$tahun);

        return redirect()->back()->with('success', "Seluruh data PDB Nasional Tahun {$tahun} berhasil dihapus dari database.");
    }
}

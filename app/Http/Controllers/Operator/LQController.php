<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Models\SummaryLqResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LQController extends Controller
{
    private function getAuthorizedKabupatens($user)
    {
        return Cache::remember('master_kabupatens', 3600, function () {
            return Kabupaten::orderBy('nama_kabupaten')->get();
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $authorizedKabupatens = $this->getAuthorizedKabupatens($user);

        $allYears = Cache::remember('summary_lq_available_years', 3600, function () {
            $years = SummaryLqResult::distinct()->orderBy('tahun', 'desc')->pluck('tahun');
            return $years->isEmpty() ? collect([2024, 2023, 2022, 2021, 2020]) : $years;
        });

        // Query Rekapitulasi LQ dari Tabel Summary
        $query = SummaryLqResult::with(['provinsi', 'kabupaten'])
            ->selectRaw('tingkat_wilayah, provinsi_id, kabupaten_id, tahun, COUNT(CASE WHEN kategori = \'Basis\' THEN 1 END) as sektor_basis_count, COUNT(CASE WHEN kategori = \'Non Basis\' THEN 1 END) as sektor_non_basis_count')
            ->groupBy('tingkat_wilayah', 'provinsi_id', 'kabupaten_id', 'tahun')
            ->orderBy('tahun', 'desc')
            ->orderBy('tingkat_wilayah', 'desc')
            ->orderBy('provinsi_id', 'asc');

        // Apply Filters
        if ($request->filled('provinsi_id')) {
            $query->where('provinsi_id', (int)$request->provinsi_id);
        }

        if ($request->filled('kabupaten_id')) {
            $kabVal = $request->kabupaten_id;
            if ($kabVal === 'prov_only') {
                $query->where('tingkat_wilayah', 'provinsi');
            } elseif (str_starts_with($kabVal, 'prov_')) {
                $pId = (int) str_replace('prov_', '', $kabVal);
                $query->where('tingkat_wilayah', 'provinsi')->where('provinsi_id', $pId);
            } else {
                $query->where('kabupaten_id', (int)$kabVal);
            }
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', (int)$request->tahun);
        }

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereHas('provinsi', function ($pq) use ($search) {
                    $pq->whereRaw('LOWER(nama_provinsi) LIKE ?', ["%{$search}%"]);
                })->orWhereHas('kabupaten', function ($kq) use ($search) {
                    $kq->whereRaw('LOWER(nama_kabupaten) LIKE ?', ["%{$search}%"]);
                })->orWhereRaw('CAST(tahun AS TEXT) LIKE ?', ["%{$search}%"]);
            });
        }

        $paginatedData = $query->paginate(15)->withQueryString();

        // Transform collection items for view compatibility
        $idCounter = ($paginatedData->currentPage() - 1) * $paginatedData->perPage() + 1;
        $paginatedData->getCollection()->transform(function ($item) use (&$idCounter) {
            $isProv = $item->tingkat_wilayah === 'provinsi';
            $provName = strtoupper($item->provinsi->nama_provinsi ?? 'SUMATERA UTARA');
            $kabName = $item->kabupaten ? strtoupper($item->kabupaten->nama_kabupaten) : '-';
            $daerahAnalisis = $isProv ? $provName : $kabName;
            $daerahPembanding = $isProv ? 'PDB NASIONAL' : 'PDRB ' . $provName;

            $basisCount = (int)$item->sektor_basis_count;
            $nonBasisCount = (int)$item->sektor_non_basis_count;
            $statusDominan = $basisCount >= $nonBasisCount 
                ? "Dominan Sektor Basis ({$basisCount} Sektor)" 
                : "Dominan Sektor Non-Basis ({$nonBasisCount} Sektor)";

            $item->id = $idCounter++;
            $item->tingkat_wilayah_label = $isProv ? 'Provinsi' : 'Kabupaten/Kota';
            $item->daerah_analisis = $daerahAnalisis;
            $item->daerah_pembanding = $daerahPembanding;
            $item->provinsi = $provName;
            $item->kabupaten = $kabName;
            $item->status_dominan = $statusDominan;
            $item->is_provinsi = $isProv;
            return $item;
        });

        $editItem = null;
        if ($request->has('edit')) {
            $editItem = AnalysisResult::where('type', 'lq')->find((int)$request->edit);
        }

        $provinsis = Cache::remember('master_provinsis', 3600, function () {
            return Provinsi::orderBy('nama_provinsi')->get();
        });
        if ($request->filled('provinsi_id')) {
            $provId = (int)$request->provinsi_id;
            $kabupatens = Kabupaten::where('provinsi_id', $provId)->orderBy('nama_kabupaten')->get();
        } else {
            $kabupatens = $authorizedKabupatens;
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('operator.potensi_unggulan.lq.partials.table', [
                    'lqData' => $paginatedData,
                ])->render(),
                'kabupatens' => $kabupatens,
                'provinsis' => $provinsis,
                'selectedProvinsiId' => $request->provinsi_id,
            ]);
        }

        return view('operator.potensi_unggulan.lq.index', [
            'lqData' => $paginatedData,
            'editItem' => $editItem,
            'provinsis' => $provinsis,
            'kabupatens' => $kabupatens,
            'availableYears' => $allYears,
        ]);
    }

    public function show(Request $request)
    {
        $tingkatWilayah = $request->get('tingkat_wilayah', 'Kabupaten/Kota');
        $tahun = (int) $request->get('tahun', 2024);
        $search = $request->get('search');
        $isProv = strtolower($tingkatWilayah) === 'provinsi';

        $query = SummaryLqResult::with(['sektor', 'provinsi', 'kabupaten'])
            ->where('tahun', $tahun);

        if ($isProv) {
            $provinsiId = (int) $request->get('provinsi_id', 12);
            $query->where('tingkat_wilayah', 'provinsi')->where('provinsi_id', $provinsiId);
            $provinsi = Provinsi::find($provinsiId);
            $namaDaerah = $provinsi ? strtoupper($provinsi->nama_provinsi) : 'PROVINSI';
            $namaPembanding = 'PDB NASIONAL';
        } else {
            $kabId = (int) $request->get('kabupaten_id', 1271);
            $query->where('tingkat_wilayah', 'kabupaten')->where('kabupaten_id', $kabId);
            $kabupaten = Kabupaten::with('provinsi')->find($kabId);
            $namaDaerah = $kabupaten ? strtoupper($kabupaten->nama_kabupaten) : 'KABUPATEN';
            $provName = $kabupaten && $kabupaten->provinsi ? strtoupper($kabupaten->provinsi->nama_provinsi) : 'SUMATERA UTARA';
            $namaPembanding = 'PDRB ' . $provName;
        }

        if ($search) {
            $searchLower = strtolower(trim($search));
            $query->where(function ($q) use ($searchLower) {
                $q->whereHas('sektor', function ($sq) use ($searchLower) {
                    $sq->whereRaw('LOWER(nama_sektor) LIKE ?', ["%{$searchLower}%"]);
                })->orWhereRaw('LOWER(kategori) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        $query->orderBy('sektor_id', 'asc');
        $paginatedSectors = $query->paginate(20)->withQueryString();

        $paginatedSectors->getCollection()->transform(function ($item) use ($tingkatWilayah, $namaDaerah, $namaPembanding, $tahun) {
            return [
                'tingkat_wilayah' => $tingkatWilayah,
                'daerah_analisis' => $namaDaerah,
                'daerah_pembanding' => $namaPembanding,
                'sektor' => $item->sektor->nama_sektor ?? '-',
                'tahun' => $tahun,
                'nilai_lq' => (float)$item->nilai_lq,
                'persen_analisis' => (float)$item->persen_daerah,
                'persen_pembanding' => (float)$item->persen_acuan,
                'kategori' => strtoupper($item->kategori),
                'keterangan' => $item->kategori === 'Basis'
                    ? 'Sektor Unggulan (LQ >= 1). Peranannya di daerah lebih dominan dibanding rata-rata acuan.'
                    : 'Sektor Non-Unggulan (LQ < 1). Peranannya lebih rendah dibanding rata-rata acuan.',
            ];
        });

        return view('operator.potensi_unggulan.lq.show', [
            'namaDaerah' => $namaDaerah,
            'namaPembanding' => $namaPembanding,
            'tingkatWilayah' => $tingkatWilayah,
            'tahun' => $tahun,
            'sectorData' => $paginatedSectors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tingkat_wilayah' => 'required|string',
            'sektor' => 'required|string',
            'tahun' => 'required|numeric',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'pdrb_sektor_analisis' => 'required|numeric',
            'total_pdrb_analisis' => 'required|numeric',
            'pdrb_sektor_pembanding' => 'required|numeric',
            'total_pdrb_pembanding' => 'required|numeric',
        ]);

        $persenAnalisis = ($validated['total_pdrb_analisis'] > 0) ? ($validated['pdrb_sektor_analisis'] / $validated['total_pdrb_analisis']) : 0;
        $persenPembanding = ($validated['total_pdrb_pembanding'] > 0) ? ($validated['pdrb_sektor_pembanding'] / $validated['total_pdrb_pembanding']) : 0;
        $lq = ($persenPembanding > 0) ? round($persenAnalisis / $persenPembanding, 4) : 0;

        $kategori = ($lq >= 1.0) ? 'BASIS' : 'NON-BASIS';
        $keterangan = ($lq >= 1.0) 
            ? 'Sektor Unggulan (LQ >= 1). Peranannya di daerah lebih dominan dibanding rata-rata acuan.'
            : 'Sektor Non-Unggulan (LQ < 1). Peranannya lebih rendah dibanding rata-rata acuan.';

        $daerahAnalisis = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? strtoupper($validated['provinsi']) 
            : strtoupper($validated['kabupaten'] ?? $validated['provinsi']);
            
        $daerahPembanding = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? 'PDB NASIONAL' 
            : 'PDRB ' . strtoupper($validated['provinsi']);

        AnalysisResult::create([
            'user_id' => Auth::id(),
            'type' => 'lq',
            'results' => array_merge($validated, [
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'nilai_lq' => $lq,
                'kategori' => $kategori,
                'keterangan' => $keterangan,
            ]),
        ]);

        Cache::flush();

        return redirect()->route('operator.lq.index')->with('success', 'Data simulasi LQ berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $item = AnalysisResult::where('type', 'lq')->findOrFail($id);

        $validated = $request->validate([
            'tingkat_wilayah' => 'required|string',
            'sektor' => 'required|string',
            'tahun' => 'required|numeric',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'pdrb_sektor_analisis' => 'required|numeric',
            'total_pdrb_analisis' => 'required|numeric',
            'pdrb_sektor_pembanding' => 'required|numeric',
            'total_pdrb_pembanding' => 'required|numeric',
        ]);

        $persenAnalisis = ($validated['total_pdrb_analisis'] > 0) ? ($validated['pdrb_sektor_analisis'] / $validated['total_pdrb_analisis']) : 0;
        $persenPembanding = ($validated['total_pdrb_pembanding'] > 0) ? ($validated['pdrb_sektor_pembanding'] / $validated['total_pdrb_pembanding']) : 0;
        $lq = ($persenPembanding > 0) ? round($persenAnalisis / $persenPembanding, 4) : 0;

        $kategori = ($lq >= 1.0) ? 'BASIS' : 'NON-BASIS';
        $keterangan = ($lq >= 1.0) 
            ? 'Sektor Unggulan (LQ >= 1). Peranannya di daerah lebih dominan dibanding rata-rata acuan.'
            : 'Sektor Non-Unggulan (LQ < 1). Peranannya lebih rendah dibanding rata-rata acuan.';

        $daerahAnalisis = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? strtoupper($validated['provinsi']) 
            : strtoupper($validated['kabupaten'] ?? $validated['provinsi']);

        $daerahPembanding = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? 'PDB NASIONAL' 
            : 'PDRB ' . strtoupper($validated['provinsi']);

        $item->update([
            'results' => array_merge($validated, [
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'nilai_lq' => $lq,
                'kategori' => $kategori,
                'keterangan' => $keterangan,
            ]),
        ]);

        Cache::flush();

        return redirect()->route('operator.lq.index')->with('success', 'Data simulasi LQ berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = AnalysisResult::where('type', 'lq')->findOrFail($id);
        $item->delete();

        Cache::flush();

        return redirect()->route('operator.lq.index')->with('success', 'Data simulasi LQ berhasil dihapus.');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'lq')->delete();

        Cache::flush();

        return redirect()->route('operator.lq.index')->with('success', 'Seluruh data simulasi LQ berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Services\LqService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LqController extends Controller
{
    protected LqService $lqService;

    public function __construct(LqService $lqService)
    {
        $this->lqService = $lqService;
    }

    private function getAuthorizedKabupatens($user)
    {
        // Read-only analysis mode: Always return all Kabupatens across Sumatera
        return Kabupaten::orderBy('nama_kabupaten')->get();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $authorizedKabupatens = $this->getAuthorizedKabupatens($user);
        $authorizedIds = $authorizedKabupatens->pluck('kab_id')->toArray();

        $savedResults = AnalysisResult::where('type', 'lq')->orderBy('id', 'desc')->get();

        if ($savedResults->isNotEmpty()) {
            $mappedData = $savedResults->map(function ($item) {
                $res = $item->results ?? [];
                $isProv = ($res['tingkat_wilayah'] ?? '') === 'Provinsi';
                $provName = strtoupper($res['provinsi'] ?? 'SUMATERA UTARA');
                $pembanding = $isProv ? 'PDB NASIONAL' : 'PDRB ' . $provName;

                return [
                    'id' => $item->id,
                    'tingkat_wilayah' => $res['tingkat_wilayah'] ?? 'Kabupaten/Kota',
                    'daerah_analisis' => $res['daerah_analisis'] ?? '-',
                    'daerah_pembanding' => $pembanding,
                    'provinsi' => $res['provinsi'] ?? '-',
                    'kabupaten' => $res['kabupaten'] ?? '-',
                    'tahun' => $res['tahun'] ?? '-',
                    'sektor_basis_count' => isset($res['kategori']) && $res['kategori'] === 'BASIS' ? 1 : 0,
                    'sektor_non_basis_count' => isset($res['kategori']) && $res['kategori'] === 'NON-BASIS' ? 1 : 0,
                    'status_dominan' => $res['kategori'] ?? '-',
                    'is_provinsi' => $isProv,
                    'provinsi_id' => $res['provinsi_id'] ?? null,
                    'kabupaten_id' => $res['kabupaten_id'] ?? null,
                ];
            });

            $allYears = $mappedData->pluck('tahun')->filter()->unique()->values()->toArray();
        } else {
            $cacheKey = 'calc_lq_summary_multi_year_all_regions_v2';

            $allYears = DB::table('pdrb_sumatera_kabupaten')
                ->select('tahun')
                ->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun');

            if ($allYears->isEmpty()) {
                $allYears = collect([2024, 2023, 2022, 2021, 2020]);
            }

            $mappedData = Cache::remember($cacheKey, 86400, function () use ($authorizedKabupatens, $allYears) {
                $provinsiList = Provinsi::whereNotNull('latitude')->orderBy('nama_provinsi')->get();
                if ($provinsiList->isEmpty()) {
                    $provinsiList = Provinsi::orderBy('nama_provinsi')->get();
                }

                $summaryRows = [];
                $idCounter = 1;

                foreach ($allYears as $tahun) {
                    // 1. Data Provinsi Terlebih Dahulu (Prioritas Utama di Atas)
                    foreach ($provinsiList as $prov) {
                        $dynamicProv = $this->lqService->calculateLqProvinsi($prov->provinsi_id, $tahun);
                        if ($dynamicProv->isNotEmpty()) {
                            $basisCount = $dynamicProv->where('kategori', 'Basis')->count();
                            $nonBasisCount = $dynamicProv->where('kategori', 'Non Basis')->count();
                            $statusDominan = $basisCount >= $nonBasisCount 
                                ? "Dominan Sektor Basis ({$basisCount} Sektor)" 
                                : "Dominan Sektor Non-Basis ({$nonBasisCount} Sektor)";

                            $summaryRows[] = [
                                'id' => $idCounter++,
                                'tingkat_wilayah' => 'Provinsi',
                                'provinsi_id' => $prov->provinsi_id,
                                'kabupaten_id' => null,
                                'daerah_analisis' => strtoupper($prov->nama_provinsi),
                                'daerah_pembanding' => 'PDB NASIONAL',
                                'provinsi' => strtoupper($prov->nama_provinsi),
                                'kabupaten' => '-',
                                'tahun' => (int)$tahun,
                                'sektor_basis_count' => $basisCount,
                                'sektor_non_basis_count' => $nonBasisCount,
                                'status_dominan' => $statusDominan,
                                'is_provinsi' => true,
                            ];
                        }
                    }

                    // 2. Data Kabupaten/Kota di Bawahnya
                    foreach ($authorizedKabupatens as $kab) {
                        $dynamicKab = $this->lqService->calculateLq($kab->kab_id, $tahun);
                        if ($dynamicKab->isNotEmpty()) {
                            $basisCount = $dynamicKab->where('kategori', 'Basis')->count();
                            $nonBasisCount = $dynamicKab->where('kategori', 'Non Basis')->count();
                            $statusDominan = $basisCount >= $nonBasisCount 
                                ? "Dominan Sektor Basis ({$basisCount} Sektor)" 
                                : "Dominan Sektor Non-Basis ({$nonBasisCount} Sektor)";

                            $provName = $kab->provinsi->nama_provinsi ?? 'SUMATERA UTARA';

                            $summaryRows[] = [
                                'id' => $idCounter++,
                                'tingkat_wilayah' => 'Kabupaten/Kota',
                                'provinsi_id' => $kab->provinsi_id,
                                'kabupaten_id' => $kab->kab_id,
                                'daerah_analisis' => strtoupper($kab->nama_kabupaten),
                                'daerah_pembanding' => 'PDRB ' . strtoupper($provName),
                                'provinsi' => strtoupper($provName),
                                'kabupaten' => strtoupper($kab->nama_kabupaten),
                                'tahun' => (int)$tahun,
                                'sektor_basis_count' => $basisCount,
                                'sektor_non_basis_count' => $nonBasisCount,
                                'status_dominan' => $statusDominan,
                                'is_provinsi' => false,
                            ];
                        }
                    }
                }

                return collect($summaryRows);
            });
        }

        // Apply Filters (Provinsi, Kabupaten, Tahun, Search)
        if ($request->filled('provinsi_id')) {
            $provId = (int)$request->provinsi_id;
            $mappedData = $mappedData->filter(fn($row) => ($row['provinsi_id'] ?? null) == $provId);
        }

        if ($request->filled('kabupaten_id')) {
            $kabVal = $request->kabupaten_id;
            if ($kabVal === 'prov_only') {
                $mappedData = $mappedData->filter(fn($row) => !empty($row['is_provinsi']));
            } elseif (str_starts_with($kabVal, 'prov_')) {
                $pId = (int) str_replace('prov_', '', $kabVal);
                $mappedData = $mappedData->filter(fn($row) => !empty($row['is_provinsi']) && ($row['provinsi_id'] ?? null) == $pId);
            } else {
                $kabId = (int)$kabVal;
                $mappedData = $mappedData->filter(fn($row) => ($row['kabupaten_id'] ?? null) == $kabId);
            }
        }

        if ($request->filled('tahun')) {
            $thn = (int)$request->tahun;
            $mappedData = $mappedData->filter(fn($row) => (int)$row['tahun'] === $thn);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $mappedData = $mappedData->filter(function ($row) use ($search) {
                return str_contains(strtolower($row['daerah_analisis']), $search) ||
                       str_contains(strtolower($row['provinsi']), $search) ||
                       str_contains((string)$row['tahun'], $search);
            });
        }

        $editItem = null;
        if ($request->has('edit')) {
            $editItem = $mappedData->firstWhere('id', (int)$request->edit);
        }

        $perPage = 15;
        $page = (int) $request->get('page', 1);
        $paginatedData = (new LengthAwarePaginator(
            $mappedData->forPage($page, $perPage)->values(),
            $mappedData->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        ));

        $provinsis = Provinsi::orderBy('nama_provinsi')->get();
        if ($request->filled('provinsi_id')) {
            $provId = (int)$request->provinsi_id;
            $kabupatens = Kabupaten::where('provinsi_id', $provId)->orderBy('nama_kabupaten')->get();
        } else {
            $kabupatens = $authorizedKabupatens;
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

        if ($tingkatWilayah === 'Provinsi') {
            $provinsiId = (int) $request->get('provinsi_id', 1);
            $provinsi = Provinsi::find($provinsiId);
            $namaDaerah = $provinsi ? strtoupper($provinsi->nama_provinsi) : 'PROVINSI';
            $namaPembanding = 'PDB NASIONAL';

            $sectorData = $this->lqService->calculateLqProvinsi($provinsiId, $tahun);
        } else {
            $kabId = (int) $request->get('kabupaten_id', 1);
            $kabupaten = Kabupaten::with('provinsi')->find($kabId);
            $namaDaerah = $kabupaten ? strtoupper($kabupaten->nama_kabupaten) : 'KABUPATEN';
            $provName = $kabupaten && $kabupaten->provinsi ? strtoupper($kabupaten->provinsi->nama_provinsi) : 'SUMATERA UTARA';
            $namaPembanding = 'PDRB ' . $provName;

            $sectorData = $this->lqService->calculateLq($kabId, $tahun);
        }

        $mappedSectors = $sectorData->map(function ($item) use ($tingkatWilayah, $namaDaerah, $namaPembanding, $tahun) {
            return [
                'tingkat_wilayah' => $tingkatWilayah,
                'daerah_analisis' => $namaDaerah,
                'daerah_pembanding' => $namaPembanding,
                'sektor' => $item['sektor']->nama_sektor ?? '-',
                'tahun' => $tahun,
                'nilai_lq' => $item['nilai_lq'],
                'persen_analisis' => $item['persen_kabupaten'] ?? $item['persen_provinsi'] ?? 0,
                'persen_pembanding' => $item['persen_provinsi'] ?? $item['persen_nasional'] ?? 0,
                'kategori' => strtoupper($item['kategori']),
                'keterangan' => $item['kategori'] === 'Basis'
                    ? 'Sektor Unggulan (LQ >= 1). Peranannya di daerah lebih dominan dibanding rata-rata acuan.'
                    : 'Sektor Non-Unggulan (LQ < 1). Peranannya lebih rendah dibanding rata-rata acuan.',
            ];
        });

        if ($search) {
            $searchLower = strtolower($search);
            $mappedSectors = $mappedSectors->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['sektor']), $searchLower) ||
                       str_contains(strtolower($row['kategori']), $searchLower);
            });
        }

        $perPage = 20;
        $page = (int) $request->get('page', 1);
        $paginatedSectors = (new LengthAwarePaginator(
            $mappedSectors->forPage($page, $perPage)->values(),
            $mappedSectors->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        ));

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

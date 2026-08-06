<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Services\TipologiSektorService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TipologiController extends Controller
{
    protected TipologiSektorService $tipologiSektorService;

    public function __construct(TipologiSektorService $tipologiSektorService)
    {
        $this->tipologiSektorService = $tipologiSektorService;
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

        $savedResults = AnalysisResult::where('type', 'tipologi_sektor')->orderBy('id', 'desc')->get();

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
                    'tahun' => (int)($res['tahun'] ?? 2024),
                    'c1_count' => isset($res['tipologi']) && str_contains($res['tipologi'], 'Kuadran I') ? 1 : 0,
                    'c2_count' => isset($res['tipologi']) && str_contains($res['tipologi'], 'Kuadran II') ? 1 : 0,
                    'c3_count' => isset($res['tipologi']) && str_contains($res['tipologi'], 'Kuadran III') ? 1 : 0,
                    'c4_count' => isset($res['tipologi']) && str_contains($res['tipologi'], 'Kuadran IV') ? 1 : 0,
                    'status_dominan' => $res['tipologi'] ?? '-',
                    'is_provinsi' => $isProv,
                    'provinsi_id' => $res['provinsi_id'] ?? null,
                    'kabupaten_id' => $res['kabupaten_id'] ?? null,
                ];
            });

            $allYears = $mappedData->pluck('tahun')->filter()->unique()->values()->toArray();
        } else {
            $cacheKey = 'calc_tipologi_summary_multi_year_all_regions_v2';

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
                        $dynamicProv = $this->tipologiSektorService->calculateTipologiProvinsi($prov->provinsi_id, $tahun);
                        if ($dynamicProv->isNotEmpty()) {
                            $c1 = $dynamicProv->where('kuadran', 'Kuadran I')->count();
                            $c2 = $dynamicProv->where('kuadran', 'Kuadran II')->count();
                            $c3 = $dynamicProv->where('kuadran', 'Kuadran III')->count();
                            $c4 = $dynamicProv->where('kuadran', 'Kuadran IV')->count();

                            $maxCount = max($c1, $c2, $c3, $c4);
                            $dominantKuadran = $maxCount === $c1 ? 'Kuadran I (Maju & Tumbuh Cepat)'
                                : ($maxCount === $c2 ? 'Kuadran II (Potensial / Berkembang)'
                                : ($maxCount === $c3 ? 'Kuadran III (Maju Tapi Tertekan)' : 'Kuadran IV (Relatif Tertinggal)'));

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
                                'c1_count' => $c1,
                                'c2_count' => $c2,
                                'c3_count' => $c3,
                                'c4_count' => $c4,
                                'status_dominan' => $dominantKuadran,
                                'is_provinsi' => true,
                            ];
                        }
                    }

                    // 2. Data Kabupaten/Kota di Bawahnya
                    foreach ($authorizedKabupatens as $kab) {
                        $dynamicKab = $this->tipologiSektorService->calculateTipologi($kab->kab_id, $tahun);
                        if ($dynamicKab->isNotEmpty()) {
                            $c1 = $dynamicKab->where('kuadran', 'Kuadran I')->count();
                            $c2 = $dynamicKab->where('kuadran', 'Kuadran II')->count();
                            $c3 = $dynamicKab->where('kuadran', 'Kuadran III')->count();
                            $c4 = $dynamicKab->where('kuadran', 'Kuadran IV')->count();

                            $maxCount = max($c1, $c2, $c3, $c4);
                            $dominantKuadran = $maxCount === $c1 ? 'Kuadran I (Maju & Tumbuh Cepat)'
                                : ($maxCount === $c2 ? 'Kuadran II (Potensial / Berkembang)'
                                : ($maxCount === $c3 ? 'Kuadran III (Maju Tapi Tertekan)' : 'Kuadran IV (Relatif Tertinggal)'));

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
                                'c1_count' => $c1,
                                'c2_count' => $c2,
                                'c3_count' => $c3,
                                'c4_count' => $c4,
                                'status_dominan' => $dominantKuadran,
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

        return view('operator.potensi_unggulan.tipologi.index', [
            'tipologiData' => $paginatedData,
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

            $sectorData = $this->tipologiSektorService->calculateTipologiProvinsi($provinsiId, $tahun);
        } else {
            $kabId = (int) $request->get('kabupaten_id', 1);
            $kabupaten = Kabupaten::with('provinsi')->find($kabId);
            $namaDaerah = $kabupaten ? strtoupper($kabupaten->nama_kabupaten) : 'KABUPATEN';
            $provName = $kabupaten && $kabupaten->provinsi ? strtoupper($kabupaten->provinsi->nama_provinsi) : 'SUMATERA UTARA';
            $namaPembanding = 'PDRB ' . $provName;

            $sectorData = $this->tipologiSektorService->calculateTipologi($kabId, $tahun);
        }

        $mappedSectors = $sectorData->map(function ($item) use ($tingkatWilayah, $namaDaerah, $namaPembanding, $tahun) {
            return [
                'tingkat_wilayah' => $tingkatWilayah,
                'daerah_analisis' => $namaDaerah,
                'daerah_pembanding' => $namaPembanding,
                'sektor' => $item['sektor']->nama_sektor ?? '-',
                'tahun' => $tahun,
                'nilai_ss' => $item['cij'],
                'nilai_lq' => $item['lq'],
                'kuadran' => $item['kuadran'],
                'tipologi' => "{$item['kuadran']} ({$item['kategori_sektor']})",
            ];
        });

        if ($search) {
            $searchLower = strtolower($search);
            $mappedSectors = $mappedSectors->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['sektor']), $searchLower) ||
                       str_contains(strtolower($row['tipologi']), $searchLower);
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

        return view('operator.potensi_unggulan.tipologi.show', [
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
            'nilai_lq' => 'required|numeric',
            'nilai_ss' => 'required|numeric',
        ]);

        $lq = $validated['nilai_lq'];
        $ss = $validated['nilai_ss'];

        if ($lq >= 1.0 && $ss >= 0) {
            $kuadran = 'Kuadran I';
            $kategoriSektor = 'Maju dan Tumbuh Cepat';
        } elseif ($lq < 1.0 && $ss >= 0) {
            $kuadran = 'Kuadran II';
            $kategoriSektor = 'Potensial / Cepat Berkembang';
        } elseif ($lq >= 1.0 && $ss < 0) {
            $kuadran = 'Kuadran III';
            $kategoriSektor = 'Maju tapi Tertekan';
        } else {
            $kuadran = 'Kuadran IV';
            $kategoriSektor = 'Relatif Tertinggal';
        }

        $daerahAnalisis = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? strtoupper($validated['provinsi']) 
            : strtoupper($validated['kabupaten'] ?? $validated['provinsi']);

        $daerahPembanding = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? 'PDB NASIONAL' 
            : 'PDRB ' . strtoupper($validated['provinsi']);

        AnalysisResult::create([
            'user_id' => Auth::id(),
            'type' => 'tipologi_sektor',
            'results' => array_merge($validated, [
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'tipologi' => "{$kuadran} ({$kategoriSektor})",
            ]),
        ]);

        Cache::flush();

        return redirect()->route('operator.tipologi.index')->with('success', 'Data simulasi Tipologi Sektor berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $item = AnalysisResult::where('type', 'tipologi_sektor')->findOrFail($id);

        $validated = $request->validate([
            'tingkat_wilayah' => 'required|string',
            'sektor' => 'required|string',
            'tahun' => 'required|numeric',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'nilai_lq' => 'required|numeric',
            'nilai_ss' => 'required|numeric',
        ]);

        $lq = $validated['nilai_lq'];
        $ss = $validated['nilai_ss'];

        if ($lq >= 1.0 && $ss >= 0) {
            $kuadran = 'Kuadran I';
            $kategoriSektor = 'Maju dan Tumbuh Cepat';
        } elseif ($lq < 1.0 && $ss >= 0) {
            $kuadran = 'Kuadran II';
            $kategoriSektor = 'Potensial / Cepat Berkembang';
        } elseif ($lq >= 1.0 && $ss < 0) {
            $kuadran = 'Kuadran III';
            $kategoriSektor = 'Maju tapi Tertekan';
        } else {
            $kuadran = 'Kuadran IV';
            $kategoriSektor = 'Relatif Tertinggal';
        }

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
                'tipologi' => "{$kuadran} ({$kategoriSektor})",
            ]),
        ]);

        Cache::flush();

        return redirect()->route('operator.tipologi.index')->with('success', 'Data simulasi Tipologi Sektor berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = AnalysisResult::where('type', 'tipologi_sektor')->findOrFail($id);
        $item->delete();

        Cache::flush();

        return redirect()->route('operator.tipologi.index')->with('success', 'Data simulasi Tipologi Sektor berhasil dihapus.');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'tipologi_sektor')->delete();

        Cache::flush();

        return redirect()->route('operator.tipologi.index')->with('success', 'Seluruh data simulasi Tipologi Sektor berhasil dihapus.');
    }

    public function syncFromDatabase()
    {
        Cache::flush();

        return redirect()->route('operator.tipologi.index')->with('success', 'Kalkulasi Tipologi Sektor berhasil diperbarui.');
    }
}

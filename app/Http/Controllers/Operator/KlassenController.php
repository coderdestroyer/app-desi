<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Services\TipologiKlassenService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KlassenController extends Controller
{
    protected TipologiKlassenService $klassenService;

    public function __construct(TipologiKlassenService $klassenService)
    {
        $this->klassenService = $klassenService;
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

        $cacheKey = 'calc_klassen_summary_multi_year_all_regions_v3';

        $allYears = Cache::remember('klassen_available_years', 3600, function () {
            $years = DB::table('pdrb_sumatera_kabupaten')
                ->select('tahun')
                ->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun');

            return $years->isEmpty() ? collect([2024, 2023, 2022, 2021, 2020]) : $years;
        });

        $mappedData = Cache::remember($cacheKey, 86400, function () use ($authorizedKabupatens, $allYears) {
            $this->klassenService->preloadPdrbData($allYears->toArray());

            $provinsiList = Provinsi::whereNotNull('latitude')->orderBy('nama_provinsi')->get();
            if ($provinsiList->isEmpty()) {
                $provinsiList = Provinsi::orderBy('nama_provinsi')->get();
            }

            $summaryRows = [];
            $idCounter = 1;

            foreach ($allYears as $tahun) {
                $tahunAwal = $tahun - 1;

                // 1. Data Provinsi Terlebih Dahulu (Prioritas Utama di Atas)
                foreach ($provinsiList as $prov) {
                    $dynamicProv = $this->klassenService->calculateKlassenProvinsi($prov->provinsi_id, (int)$tahun);
                    if ($dynamicProv->isNotEmpty()) {
                        $c1 = $dynamicProv->where('kuadran', 'Kuadran I')->count();
                        $c2 = $dynamicProv->where('kuadran', 'Kuadran II')->count();
                        $c3 = $dynamicProv->where('kuadran', 'Kuadran III')->count();
                        $c4 = $dynamicProv->where('kuadran', 'Kuadran IV')->count();

                        $maxCount = max($c1, $c2, $c3, $c4);
                        $dominantKuadran = $maxCount === $c1 ? 'Kuadran I (Sektor Maju & Tumbuh Pesat)'
                            : ($maxCount === $c2 ? 'Kuadran II (Sektor Maju tapi Tertekan)'
                            : ($maxCount === $c3 ? 'Kuadran III (Sektor Berkembang Cepat)' : 'Kuadran IV (Sektor Relatif Tertinggal)'));

                        $summaryRows[] = [
                            'id' => $idCounter++,
                            'tingkat_wilayah' => 'Provinsi',
                            'provinsi_id' => $prov->provinsi_id,
                            'kabupaten_id' => null,
                            'daerah_analisis' => strtoupper($prov->nama_provinsi),
                            'daerah_pembanding' => 'PDB NASIONAL',
                            'provinsi' => strtoupper($prov->nama_provinsi),
                            'kabupaten' => '-',
                            'tahun_awal' => $tahunAwal,
                            'tahun_akhir' => (int)$tahun,
                            'tahun' => "{$tahunAwal} - {$tahun}",
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
                    $dynamicKab = $this->klassenService->calculateKlassen($kab->kab_id, (int)$tahun);
                    if ($dynamicKab->isNotEmpty()) {
                        $c1 = $dynamicKab->where('kuadran', 'Kuadran I')->count();
                        $c2 = $dynamicKab->where('kuadran', 'Kuadran II')->count();
                        $c3 = $dynamicKab->where('kuadran', 'Kuadran III')->count();
                        $c4 = $dynamicKab->where('kuadran', 'Kuadran IV')->count();

                        $maxCount = max($c1, $c2, $c3, $c4);
                        $dominantKuadran = $maxCount === $c1 ? 'Kuadran I (Sektor Maju & Tumbuh Pesat)'
                            : ($maxCount === $c2 ? 'Kuadran II (Sektor Maju tapi Tertekan)'
                            : ($maxCount === $c3 ? 'Kuadran III (Sektor Berkembang Cepat)' : 'Kuadran IV (Sektor Relatif Tertinggal)'));

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
                            'tahun_awal' => $tahunAwal,
                            'tahun_akhir' => (int)$tahun,
                            'tahun' => "{$tahunAwal} - {$tahun}",
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
            $mappedData = $mappedData->filter(fn($row) => (int)($row['tahun_akhir'] ?? 0) === $thn);
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

        return view('operator.potensi_unggulan.klassen.index', [
            'klassenData' => $paginatedData,
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
        $tahunAwal = $tahun - 1;
        $search = $request->get('search');

        if ($tingkatWilayah === 'Provinsi') {
            $provinsiId = (int) $request->get('provinsi_id', 1);
            $provinsi = Provinsi::find($provinsiId);
            $namaDaerah = $provinsi ? strtoupper($provinsi->nama_provinsi) : 'PROVINSI';
            $namaPembanding = 'PDB NASIONAL';

            $sectorData = $this->klassenService->calculateKlassenProvinsi($provinsiId, $tahun);
        } else {
            $kabId = (int) $request->get('kabupaten_id', 1);
            $kabupaten = Kabupaten::with('provinsi')->find($kabId);
            $namaDaerah = $kabupaten ? strtoupper($kabupaten->nama_kabupaten) : 'KABUPATEN';
            $provName = $kabupaten && $kabupaten->provinsi ? strtoupper($kabupaten->provinsi->nama_provinsi) : 'SUMATERA UTARA';
            $namaPembanding = 'PDRB ' . $provName;

            $sectorData = $this->klassenService->calculateKlassen($kabId, $tahun);
        }

        $mappedSectors = $sectorData->map(function ($item) use ($tingkatWilayah, $namaDaerah, $namaPembanding, $tahun) {
            return [
                'tingkat_wilayah' => $tingkatWilayah,
                'daerah_analisis' => $namaDaerah,
                'daerah_pembanding' => $namaPembanding,
                'sektor' => $item['sektor']->nama_sektor ?? '-',
                'tahun' => $tahun,
                'ri' => $item['laju_pertumbuhan'],
                'r' => $item['laju_pertumbuhan_acuan'],
                'yi' => $item['kontribusi_pdrb'],
                'y' => $item['kontribusi_acuan'],
                'kuadran' => $item['kuadran'],
                'klasifikasi' => $item['klasifikasi_sektor'],
            ];
        });

        if ($search) {
            $searchLower = strtolower($search);
            $mappedSectors = $mappedSectors->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['sektor']), $searchLower) ||
                       str_contains(strtolower($row['klasifikasi']), $searchLower);
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

        return view('operator.potensi_unggulan.klassen.show', [
            'namaDaerah' => $namaDaerah,
            'namaPembanding' => $namaPembanding,
            'tingkatWilayah' => $tingkatWilayah,
            'tahunAwal' => $tahunAwal,
            'tahunAkhir' => $tahun,
            'sectorData' => $paginatedSectors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tingkat_wilayah' => 'required|string',
            'sektor' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'tahun' => 'required|array|min:2',
            'tahun.*' => 'required|numeric',
            'pdrb_sektor_analisis' => 'required|array|min:2',
            'total_pdrb_analisis' => 'required|array|min:2',
            'pdrb_sektor_pembanding' => 'required|array|min:2',
            'total_pdrb_pembanding' => 'required|array|min:2',
        ]);

        $yearsCount = count($validated['tahun']);
        $idxAwal = 0;
        $idxAkhir = $yearsCount - 1;

        $yAwal = $validated['pdrb_sektor_analisis'][$idxAwal];
        $yAkhir = $validated['pdrb_sektor_analisis'][$idxAkhir];
        $ri = $yAwal > 0 ? (($yAkhir - $yAwal) / $yAwal) * 100 : 0;

        $totAwal = $validated['total_pdrb_analisis'][$idxAwal];
        $totAkhir = $validated['total_pdrb_analisis'][$idxAkhir];
        $r = totAwal > 0 ? (($totAkhir - $totAwal) / $totAwal) * 100 : 0;

        $yi = $totAkhir > 0 ? ($yAkhir / $totAkhir) * 100 : 0;

        $pPembandingAkhir = $validated['pdrb_sektor_pembanding'][$idxAkhir];
        $totPembandingAkhir = $validated['total_pdrb_pembanding'][$idxAkhir];
        $y = $totPembandingAkhir > 0 ? ($pPembandingAkhir / $totPembandingAkhir) * 100 : 0;

        if ($ri >= $r && $yi >= $y) {
            $kuadran = 'Kuadran I';
            $klasifikasi = 'Sektor Maju dan Tumbuh Pesat';
        } elseif ($ri < $r && $yi >= $y) {
            $kuadran = 'Kuadran II';
            $klasifikasi = 'Sektor Maju tapi Tertekan';
        } elseif ($ri >= $r && $yi < $y) {
            $kuadran = 'Kuadran III';
            $klasifikasi = 'Sektor Berkembang Cepat / Potensial';
        } else {
            $kuadran = 'Kuadran IV';
            $klasifikasi = 'Sektor Relatif Tertinggal';
        }

        $daerahAnalisis = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? strtoupper($validated['provinsi']) 
            : strtoupper($validated['kabupaten'] ?? $validated['provinsi']);

        $daerahPembanding = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? 'PDB NASIONAL' 
            : 'PDRB ' . strtoupper($validated['provinsi']);

        AnalysisResult::create([
            'user_id' => Auth::id(),
            'type' => 'klassen',
            'results' => [
                'tingkat_wilayah' => $validated['tingkat_wilayah'],
                'sektor' => $validated['sektor'],
                'provinsi' => $validated['provinsi'],
                'kabupaten' => $validated['kabupaten'],
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'tahun_awal' => $validated['tahun'][$idxAwal],
                'tahun_akhir' => $validated['tahun'][$idxAkhir],
                'pdrb_sektor_analisis_awal' => $yAwal,
                'pdrb_sektor_analisis_akhir' => $yAkhir,
                'total_pdrb_analisis_awal' => $totAwal,
                'total_pdrb_analisis_akhir' => $totAkhir,
                'pdrb_sektor_pembanding_awal' => $validated['pdrb_sektor_pembanding'][$idxAwal],
                'pdrb_sektor_pembanding_akhir' => $pPembandingAkhir,
                'total_pdrb_pembanding_awal' => $validated['total_pdrb_pembanding'][$idxAwal],
                'total_pdrb_pembanding_akhir' => $totPembandingAkhir,
                'ri' => round($ri, 2),
                'r' => round($r, 2),
                'yi' => round($yi, 2),
                'y' => round($y, 2),
                'kuadran' => $kuadran,
                'klasifikasi' => $klasifikasi,
            ],
        ]);

        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Data simulasi Tipologi Klassen berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $item = AnalysisResult::where('type', 'klassen')->findOrFail($id);

        $validated = $request->validate([
            'tingkat_wilayah' => 'required|string',
            'sektor' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'tahun' => 'required|array|min:2',
            'tahun.*' => 'required|numeric',
            'pdrb_sektor_analisis' => 'required|array|min:2',
            'total_pdrb_analisis' => 'required|array|min:2',
            'pdrb_sektor_pembanding' => 'required|array|min:2',
            'total_pdrb_pembanding' => 'required|array|min:2',
        ]);

        $yearsCount = count($validated['tahun']);
        $idxAwal = 0;
        $idxAkhir = $yearsCount - 1;

        $yAwal = $validated['pdrb_sektor_analisis'][$idxAwal];
        $yAkhir = $validated['pdrb_sektor_analisis'][$idxAkhir];
        $ri = $yAwal > 0 ? (($yAkhir - $yAwal) / $yAwal) * 100 : 0;

        $totAwal = $validated['total_pdrb_analisis'][$idxAwal];
        $totAkhir = $validated['total_pdrb_analisis'][$idxAkhir];
        $r = totAwal > 0 ? (($totAkhir - $totAwal) / $totAwal) * 100 : 0;

        $yi = $totAkhir > 0 ? ($yAkhir / $totAkhir) * 100 : 0;

        $pPembandingAkhir = $validated['pdrb_sektor_pembanding'][$idxAkhir];
        $totPembandingAkhir = $validated['total_pdrb_pembanding'][$idxAkhir];
        $y = $totPembandingAkhir > 0 ? ($pPembandingAkhir / $totPembandingAkhir) * 100 : 0;

        if ($ri >= $r && $yi >= $y) {
            $kuadran = 'Kuadran I';
            $klasifikasi = 'Sektor Maju dan Tumbuh Pesat';
        } elseif ($ri < $r && $yi >= $y) {
            $kuadran = 'Kuadran II';
            $klasifikasi = 'Sektor Maju tapi Tertekan';
        } elseif ($ri >= $r && $yi < $y) {
            $kuadran = 'Kuadran III';
            $klasifikasi = 'Sektor Berkembang Cepat / Potensial';
        } else {
            $kuadran = 'Kuadran IV';
            $klasifikasi = 'Sektor Relatif Tertinggal';
        }

        $daerahAnalisis = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? strtoupper($validated['provinsi']) 
            : strtoupper($validated['kabupaten'] ?? $validated['provinsi']);

        $daerahPembanding = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? 'PDB NASIONAL' 
            : 'PDRB ' . strtoupper($validated['provinsi']);

        $item->update([
            'results' => [
                'tingkat_wilayah' => $validated['tingkat_wilayah'],
                'sektor' => $validated['sektor'],
                'provinsi' => $validated['provinsi'],
                'kabupaten' => $validated['kabupaten'],
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'tahun_awal' => $validated['tahun'][$idxAwal],
                'tahun_akhir' => $validated['tahun'][$idxAkhir],
                'pdrb_sektor_analisis_awal' => $yAwal,
                'pdrb_sektor_analisis_akhir' => $yAkhir,
                'total_pdrb_analisis_awal' => $totAwal,
                'total_pdrb_analisis_akhir' => $totAkhir,
                'pdrb_sektor_pembanding_awal' => $validated['pdrb_sektor_pembanding'][$idxAwal],
                'pdrb_sektor_pembanding_akhir' => $pPembandingAkhir,
                'total_pdrb_pembanding_awal' => $validated['total_pdrb_pembanding'][$idxAwal],
                'total_pdrb_pembanding_akhir' => $totPembandingAkhir,
                'ri' => round($ri, 2),
                'r' => round($r, 2),
                'yi' => round($yi, 2),
                'y' => round($y, 2),
                'kuadran' => $kuadran,
                'klasifikasi' => $klasifikasi,
            ],
        ]);

        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Data simulasi Tipologi Klassen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = AnalysisResult::where('type', 'klassen')->findOrFail($id);
        $item->delete();

        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Data simulasi Tipologi Klassen berhasil dihapus.');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'klassen')->delete();

        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Seluruh data simulasi Tipologi Klassen berhasil dihapus.');
    }

    public function syncFromDatabase()
    {
        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Kalkulasi Tipologi Klassen berhasil diperbarui.');
    }
}

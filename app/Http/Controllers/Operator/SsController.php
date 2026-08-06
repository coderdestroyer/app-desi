<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Services\SsaService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SsController extends Controller
{
    protected SsaService $ssaService;

    public function __construct(SsaService $ssaService)
    {
        $this->ssaService = $ssaService;
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

        $cacheKey = 'calc_ss_summary_multi_year_all_regions_v3';

        $allYears = Cache::remember('ss_available_years', 3600, function () {
            $years = DB::table('pdrb_sumatera_kabupaten')
                ->select('tahun')
                ->distinct()
                ->orderBy('tahun', 'desc')
                ->pluck('tahun');

            return $years->isEmpty() ? collect([2024, 2023, 2022, 2021, 2020]) : $years;
        });

        $mappedData = Cache::remember($cacheKey, 86400, function () use ($authorizedKabupatens, $allYears) {
            $this->ssaService->preloadPdrbData($allYears->toArray());

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
                    $dynamicProv = $this->ssaService->calculateSsaProvinsi($prov->provinsi_id, (int)$tahun);
                    if ($dynamicProv->isNotEmpty()) {
                        $cepatCount = $dynamicProv->where('kategori_pertumbuhan', 'Pertumbuhan Cepat')->count();
                        $lambatCount = $dynamicProv->where('kategori_pertumbuhan', 'Pertumbuhan Lambat')->count();
                        $dayaSaingTinggiCount = $dynamicProv->where('kategori_daya_saing', 'Daya Saing Baik')->count();

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
                            'sektor_cepat_count' => $cepatCount,
                            'sektor_lambat_count' => $lambatCount,
                            'daya_saing_tinggi_count' => $dayaSaingTinggiCount,
                            'status_dominan' => $cepatCount >= $lambatCount ? 'Dominan Pertumbuhan Cepat' : 'Dominan Pertumbuhan Lambat',
                            'is_provinsi' => true,
                        ];
                    }
                }

                // 2. Data Kabupaten/Kota di Bawahnya
                foreach ($authorizedKabupatens as $kab) {
                    $dynamicKab = $this->ssaService->calculateSsa($kab->kab_id, (int)$tahun);
                    if ($dynamicKab->isNotEmpty()) {
                        $cepatCount = $dynamicKab->where('kategori_pertumbuhan', 'Pertumbuhan Cepat')->count();
                        $lambatCount = $dynamicKab->where('kategori_pertumbuhan', 'Pertumbuhan Lambat')->count();
                        $dayaSaingTinggiCount = $dynamicKab->where('kategori_daya_saing', 'Daya Saing Baik')->count();

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
                            'sektor_cepat_count' => $cepatCount,
                            'sektor_lambat_count' => $lambatCount,
                            'daya_saing_tinggi_count' => $dayaSaingTinggiCount,
                            'status_dominan' => $cepatCount >= $lambatCount ? 'Dominan Pertumbuhan Cepat' : 'Dominan Pertumbuhan Lambat',
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

        return view('operator.potensi_unggulan.ss.index', [
            'ssData' => $paginatedData,
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

            $sectorData = $this->ssaService->calculateSsaProvinsi($provinsiId, $tahun);
        } else {
            $kabId = (int) $request->get('kabupaten_id', 1);
            $kabupaten = Kabupaten::with('provinsi')->find($kabId);
            $namaDaerah = $kabupaten ? strtoupper($kabupaten->nama_kabupaten) : 'KABUPATEN';
            $provName = $kabupaten && $kabupaten->provinsi ? strtoupper($kabupaten->provinsi->nama_provinsi) : 'SUMATERA UTARA';
            $namaPembanding = 'PDRB ' . $provName;

            $sectorData = $this->ssaService->calculateSsa($kabId, $tahun);
        }

        $mappedSectors = $sectorData->map(function ($item) use ($tingkatWilayah, $namaDaerah, $namaPembanding, $tahun, $tahunAwal) {
            return [
                'tingkat_wilayah' => $tingkatWilayah,
                'daerah_analisis' => $namaDaerah,
                'daerah_pembanding' => $namaPembanding,
                'sektor' => $item['sektor']->nama_sektor ?? '-',
                'tahun_awal' => $tahunAwal,
                'tahun_akhir' => $tahun,
                'rij' => $item['rij'],
                'rin' => $item['rin'],
                'rn' => $item['rn'],
                'nij' => $item['nij'],
                'mij' => $item['mij'],
                'cij' => $item['cij'],
                'dij' => $item['dij'],
                'status_pertumbuhan' => $item['kategori_pertumbuhan'],
                'status_daya_saing' => $item['kategori_daya_saing'],
            ];
        });

        if ($search) {
            $searchLower = strtolower($search);
            $mappedSectors = $mappedSectors->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['sektor']), $searchLower) ||
                       str_contains(strtolower($row['status_pertumbuhan']), $searchLower) ||
                       str_contains(strtolower($row['status_daya_saing']), $searchLower);
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

        return view('operator.potensi_unggulan.ss.show', [
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
            'tahun_awal' => 'required|numeric',
            'tahun_akhir' => 'required|numeric',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'pdrb_sektor_analisis_awal' => 'required|numeric',
            'pdrb_sektor_analisis_akhir' => 'required|numeric',
            'pdrb_sektor_pembanding_awal' => 'required|numeric',
            'total_pdrb_pembanding_akhir' => 'required|numeric',
            'total_pdrb_pembanding_awal' => 'required|numeric',
        ]);

        $yijAwal = $validated['pdrb_sektor_analisis_awal'];
        $yijAkhir = $validated['pdrb_sektor_analisis_akhir'];
        $yinAwal = $validated['pdrb_sektor_pembanding_awal'];
        $yinAkhir = $validated['total_pdrb_pembanding_akhir'];
        $ynAwal = $validated['total_pdrb_pembanding_awal'];
        $ynAkhir = $validated['total_pdrb_pembanding_akhir'];

        $rij = $yijAwal > 0 ? ($yijAkhir - $yijAwal) / $yijAwal : 0;
        $rin = yinAwal > 0 ? ($yinAkhir - $yinAwal) / $yinAwal : 0;
        $rn = ynAwal > 0 ? ($ynAkhir - $ynAwal) / $ynAwal : 0;

        $nij = $yijAwal * $rn;
        $mij = $yijAwal * ($rin - $rn);
        $cij = $yijAwal * ($rij - $rin);
        $dij = $nij + $mij + $cij;

        $statusPertumbuhan = $dij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat';
        $statusDayaSaing = $cij >= 0 ? 'Daya Saing Tinggi (Kompetitif)' : 'Daya Saing Rendah';

        $daerahAnalisis = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? strtoupper($validated['provinsi']) 
            : strtoupper($validated['kabupaten'] ?? $validated['provinsi']);

        $daerahPembanding = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? 'PDB NASIONAL' 
            : 'PDRB ' . strtoupper($validated['provinsi']);

        AnalysisResult::create([
            'user_id' => Auth::id(),
            'type' => 'shift_share',
            'results' => array_merge($validated, [
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'rn' => round($rn, 4),
                'rin' => round($rin, 4),
                'rij' => round($rij, 4),
                'nij' => round($nij, 2),
                'mij' => round($mij, 2),
                'cij' => round($cij, 2),
                'dij' => round($dij, 2),
                'status_pertumbuhan' => $statusPertumbuhan,
                'status_daya_saing' => $statusDayaSaing,
            ]),
        ]);

        Cache::flush();

        return redirect()->route('operator.ss.index')->with('success', 'Data simulasi Shift-Share berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $item = AnalysisResult::where('type', 'shift_share')->findOrFail($id);

        $validated = $request->validate([
            'tingkat_wilayah' => 'required|string',
            'sektor' => 'required|string',
            'tahun_awal' => 'required|numeric',
            'tahun_akhir' => 'required|numeric',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'pdrb_sektor_analisis_awal' => 'required|numeric',
            'pdrb_sektor_analisis_akhir' => 'required|numeric',
            'pdrb_sektor_pembanding_awal' => 'required|numeric',
            'total_pdrb_pembanding_akhir' => 'required|numeric',
            'total_pdrb_pembanding_awal' => 'required|numeric',
        ]);

        $yijAwal = $validated['pdrb_sektor_analisis_awal'];
        $yijAkhir = $validated['pdrb_sektor_analisis_akhir'];
        $yinAwal = $validated['pdrb_sektor_pembanding_awal'];
        $yinAkhir = $validated['total_pdrb_pembanding_akhir'];
        $ynAwal = $validated['total_pdrb_pembanding_awal'];
        $ynAkhir = $validated['total_pdrb_pembanding_akhir'];

        $rij = $yijAwal > 0 ? ($yijAkhir - $yijAwal) / $yijAwal : 0;
        $rin = yinAwal > 0 ? ($yinAkhir - $yinAwal) / $yinAwal : 0;
        $rn = ynAwal > 0 ? ($ynAkhir - $ynAwal) / $ynAwal : 0;

        $nij = $yijAwal * $rn;
        $mij = $yijAwal * ($rin - $rn);
        $cij = $yijAwal * ($rij - $rin);
        $dij = $nij + $mij + $cij;

        $statusPertumbuhan = $dij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat';
        $statusDayaSaing = $cij >= 0 ? 'Daya Saing Tinggi (Kompetitif)' : 'Daya Saing Rendah';

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
                'rn' => round($rn, 4),
                'rin' => round($rin, 4),
                'rij' => round($rij, 4),
                'nij' => round($nij, 2),
                'mij' => round($mij, 2),
                'cij' => round($cij, 2),
                'dij' => round($dij, 2),
                'status_pertumbuhan' => $statusPertumbuhan,
                'status_daya_saing' => $statusDayaSaing,
            ]),
        ]);

        Cache::flush();

        return redirect()->route('operator.ss.index')->with('success', 'Data simulasi Shift-Share berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = AnalysisResult::where('type', 'shift_share')->findOrFail($id);
        $item->delete();

        Cache::flush();

        return redirect()->route('operator.ss.index')->with('success', 'Data simulasi Shift-Share berhasil dihapus.');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'shift_share')->delete();

        Cache::flush();

        return redirect()->route('operator.ss.index')->with('success', 'Seluruh data simulasi Shift-Share berhasil dihapus.');
    }
}
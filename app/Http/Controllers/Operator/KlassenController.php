<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Models\SummaryKlassenResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class KlassenController extends Controller
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

        $allYears = Cache::remember('summary_klassen_available_years', 3600, function () {
            $years = SummaryKlassenResult::distinct()->orderBy('tahun_akhir', 'desc')->pluck('tahun_akhir');
            return $years->isEmpty() ? collect([2024, 2023, 2022, 2021, 2020]) : $years;
        });

        // Query Rekapitulasi Tipologi Klassen dari Tabel Summary
        $query = SummaryKlassenResult::with(['provinsi', 'kabupaten'])
            ->selectRaw('tingkat_wilayah, provinsi_id, kabupaten_id, tahun_awal, tahun_akhir,
                COUNT(CASE WHEN kuadran = \'Kuadran I\' THEN 1 END) as c1_count,
                COUNT(CASE WHEN kuadran = \'Kuadran II\' THEN 1 END) as c2_count,
                COUNT(CASE WHEN kuadran = \'Kuadran III\' THEN 1 END) as c3_count,
                COUNT(CASE WHEN kuadran = \'Kuadran IV\' THEN 1 END) as c4_count')
            ->groupBy('tingkat_wilayah', 'provinsi_id', 'kabupaten_id', 'tahun_awal', 'tahun_akhir')
            ->orderBy('tahun_akhir', 'desc')
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
            $query->where('tahun_akhir', (int)$request->tahun);
        }

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $query->where(function ($q) use ($search) {
                $q->whereHas('provinsi', function ($pq) use ($search) {
                    $pq->whereRaw('LOWER(nama_provinsi) LIKE ?', ["%{$search}%"]);
                })->orWhereHas('kabupaten', function ($kq) use ($search) {
                    $kq->whereRaw('LOWER(nama_kabupaten) LIKE ?', ["%{$search}%"]);
                })->orWhereRaw('CAST(tahun_akhir AS TEXT) LIKE ?', ["%{$search}%"]);
            });
        }

        $paginatedData = $query->paginate(15)->withQueryString();

        $idCounter = ($paginatedData->currentPage() - 1) * $paginatedData->perPage() + 1;
        $paginatedData->getCollection()->transform(function ($item) use (&$idCounter) {
            $isProv = $item->tingkat_wilayah === 'provinsi';
            $provName = strtoupper($item->provinsi->nama_provinsi ?? 'SUMATERA UTARA');
            $kabName = $item->kabupaten ? strtoupper($item->kabupaten->nama_kabupaten) : '-';
            $daerahAnalisis = $isProv ? $provName : $kabName;
            $daerahPembanding = $isProv ? 'PDB NASIONAL' : 'PDRB ' . $provName;

            $c1 = (int)$item->c1_count;
            $c2 = (int)$item->c2_count;
            $c3 = (int)$item->c3_count;
            $c4 = (int)$item->c4_count;

            $maxCount = max($c1, $c2, $c3, $c4);
            $dominantKuadran = $maxCount === $c1 ? 'Kuadran I (Sektor Maju & Tumbuh Pesat)'
                : ($maxCount === $c2 ? 'Kuadran II (Sektor Maju tapi Tertekan)'
                : ($maxCount === $c3 ? 'Kuadran III (Sektor Berkembang Cepat)' : 'Kuadran IV (Sektor Relatif Tertinggal)'));

            $item->id = $idCounter++;
            $item->tingkat_wilayah_label = $isProv ? 'Provinsi' : 'Kabupaten/Kota';
            $item->daerah_analisis = $daerahAnalisis;
            $item->daerah_pembanding = $daerahPembanding;
            $item->provinsi = $provName;
            $item->kabupaten = $kabName;
            $item->tahun_awal = $item->tahun_awal;
            $item->tahun_akhir = $item->tahun_akhir;
            $item->tahun = "{$item->tahun_awal} - {$item->tahun_akhir}";
            $item->c1_count = $c1;
            $item->c2_count = $c2;
            $item->c3_count = $c3;
            $item->c4_count = $c4;
            $item->status_dominan = $dominantKuadran;
            $item->is_provinsi = $isProv;
            return $item;
        });

        $userId = Auth::id();
        $editItem = null;
        if ($request->has('edit')) {
            $found = AnalysisResult::where('type', 'tipologi_klassen')
                ->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhereRaw("results->>'user_id' = ?", [(string)$userId]);
                })
                ->find((int)$request->edit);
            if ($found) {
                $editItem = array_merge(['id' => $found->id], $found->results ?? []);
            }
        }

        $simulasiList = AnalysisResult::where('type', 'tipologi_klassen')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereRaw("results->>'user_id' = ?", [(string)$userId]);
            })
            ->latest()
            ->paginate(10, ['*'], 'sim_page')
            ->withQueryString();

        $simulasiList->getCollection()->transform(function ($item) {
            return array_merge([
                'id' => $item->id,
                'title' => $item->title,
                'created_at' => $item->created_at,
            ], $item->results ?? []);
        });

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
                'html' => view('operator.potensi_unggulan.klassen.partials.table', [
                    'klassenData' => $paginatedData,
                ])->render(),
                'kabupatens' => $kabupatens,
                'provinsis' => $provinsis,
                'selectedProvinsiId' => $request->provinsi_id,
            ]);
        }

        return view('operator.potensi_unggulan.klassen.index', [
            'klassenData' => $paginatedData,
            'simulasiList' => $simulasiList,
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
        $isProv = strtolower($tingkatWilayah) === 'provinsi';

        $query = SummaryKlassenResult::with(['sektor', 'provinsi', 'kabupaten'])
            ->where('tahun_akhir', $tahun);

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
                })->orWhereRaw('LOWER(kuadran) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        $query->orderBy('sektor_id', 'asc');
        $paginatedSectors = $query->paginate(50)->withQueryString();

        $paginatedSectors->getCollection()->transform(function ($item) use ($tingkatWilayah, $namaDaerah, $namaPembanding, $tahun, $tahunAwal) {
            $ri = (float)$item->growth_daerah;
            $r = (float)$item->growth_pembanding;
            $yi = (float)$item->share_daerah;
            $y = (float)$item->share_pembanding;

            return [
                'tingkat_wilayah' => $tingkatWilayah,
                'daerah_analisis' => $namaDaerah,
                'daerah_pembanding' => $namaPembanding,
                'sektor' => $item->sektor->nama_sektor ?? '-',
                'tahun' => "{$tahunAwal} - {$tahun}",
                'ri' => $ri,
                'r' => $r,
                'yi' => $yi,
                'y' => $y,
                'laju_pertumbuhan' => $ri,
                'laju_pertumbuhan_acuan' => $r,
                'kontribusi_pdrb' => $yi,
                'kontribusi_acuan' => $y,
                'kuadran' => $item->kuadran,
                'klasifikasi' => $item->kategori_kuadran,
                'klasifikasi_sektor' => $item->kategori_kuadran,
            ];
        });

        return view('operator.potensi_unggulan.klassen.show', [
            'namaDaerah' => $namaDaerah,
            'namaPembanding' => $namaPembanding,
            'tingkatWilayah' => $tingkatWilayah,
            'tahun' => $tahun,
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
        ]);

        if ($request->has('tahun') && is_array($request->tahun) && count($request->tahun) >= 2) {
            $tahunArr = $request->tahun;
            $pdrbSektorArr = $request->pdrb_sektor_analisis ?? [];
            $totalPdrbArr = $request->total_pdrb_analisis ?? [];
            $pdrbPembandingArr = $request->pdrb_sektor_pembanding ?? [];
            $totalPembandingArr = $request->total_pdrb_pembanding ?? [];

            $tahunAwal = (int) ($tahunArr[0] ?? date('Y') - 1);
            $tahunAkhir = (int) ($tahunArr[count($tahunArr) - 1] ?? date('Y'));

            $yAwal = (float) str_replace(',', '.', str_replace('.', '', $pdrbSektorArr[0] ?? 0));
            $yAkhir = (float) str_replace(',', '.', str_replace('.', '', $pdrbSektorArr[count($pdrbSektorArr) - 1] ?? 0));

            $totalYAwal = (float) str_replace(',', '.', str_replace('.', '', $totalPdrbArr[0] ?? 0));
            $totalYAkhir = (float) str_replace(',', '.', str_replace('.', '', $totalPdrbArr[count($totalPdrbArr) - 1] ?? 0));

            $yPembandingAwal = (float) str_replace(',', '.', str_replace('.', '', $pdrbPembandingArr[0] ?? 0));
            $yPembandingAkhir = (float) str_replace(',', '.', str_replace('.', '', $pdrbPembandingArr[count($pdrbPembandingArr) - 1] ?? 0));

            $totalPembandingAwal = (float) str_replace(',', '.', str_replace('.', '', $totalPembandingArr[0] ?? 0));
            $totalPembandingAkhir = (float) str_replace(',', '.', str_replace('.', '', $totalPembandingArr[count($totalPembandingArr) - 1] ?? 0));

            $ri = ($yAwal > 0) ? (($yAkhir - $yAwal) / $yAwal) * 100 : 0;
            $rp = ($totalPembandingAwal > 0) ? (($totalPembandingAkhir - $totalPembandingAwal) / $totalPembandingAwal) * 100 : 0;
            $yi = ($totalYAkhir > 0) ? ($yAkhir / $totalYAkhir) * 100 : 0;
            $yp = ($totalPembandingAkhir > 0) ? ($yPembandingAkhir / $totalPembandingAkhir) * 100 : 0;
        } else {
            $tahunAwal = (int) ($request->tahun_awal ?? $request->tahun - 1 ?? date('Y') - 1);
            $tahunAkhir = (int) ($request->tahun_akhir ?? $request->tahun ?? date('Y'));
            $ri = (float) ($request->laju_pertumbuhan_daerah ?? 0);
            $rp = (float) ($request->laju_pertumbuhan_pembanding ?? 0);
            $yi = (float) ($request->kontribusi_daerah ?? 0);
            $yp = (float) ($request->kontribusi_pembanding ?? 0);
            $yAwal = 0; $yAkhir = 0; $totalYAwal = 0; $totalYAkhir = 0;
            $yPembandingAwal = 0; $yPembandingAkhir = 0; $totalPembandingAwal = 0; $totalPembandingAkhir = 0;
        }

        $r_i = $ri / 100;
        $r_p = $rp / 100;
        $y_i = $yi / 100;
        $y_p = $yp / 100;

        $kuadran = '';
        if ($r_i >= $r_p && $y_i >= $y_p) $kuadran = 'Kuadran I';
        elseif ($r_i < $r_p && $y_i >= $y_p) $kuadran = 'Kuadran II';
        elseif ($r_i >= $r_p && $y_i < $y_p) $kuadran = 'Kuadran III';
        else $kuadran = 'Kuadran IV';

        $klasifikasiMap = [
            'Kuadran I' => 'Sektor Maju dan Tumbuh Pesat',
            'Kuadran II' => 'Sektor Maju tapi Tertekan',
            'Kuadran III' => 'Sektor Berkembang Cepat / Potensial',
            'Kuadran IV' => 'Sektor Relatif Tertinggal',
        ];

        $daerahAnalisis = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? strtoupper($validated['provinsi']) 
            : strtoupper($validated['kabupaten'] ?? $validated['provinsi']);
            
        $daerahPembanding = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? 'PDB NASIONAL' 
            : 'PDRB ' . strtoupper($validated['provinsi']);

        AnalysisResult::create([
            'user_id' => Auth::id(),
            'type' => 'tipologi_klassen',
            'title' => 'Simulasi Tipologi Klassen ' . $daerahAnalisis . ' (' . $validated['sektor'] . ')',
            'results' => [
                'tingkat_wilayah' => $validated['tingkat_wilayah'],
                'provinsi' => $validated['provinsi'],
                'kabupaten' => $validated['kabupaten'] ?? null,
                'sektor' => $validated['sektor'],
                'tahun' => $tahunAkhir,
                'tahun_awal' => $tahunAwal,
                'tahun_akhir' => $tahunAkhir,
                'pdrb_sektor_analisis_awal' => $yAwal,
                'pdrb_sektor_analisis_akhir' => $yAkhir,
                'total_pdrb_analisis_awal' => $totalYAwal,
                'total_pdrb_analisis_akhir' => $totalYAkhir,
                'pdrb_sektor_pembanding_awal' => $yPembandingAwal,
                'pdrb_sektor_pembanding_akhir' => $yPembandingAkhir,
                'total_pdrb_pembanding_awal' => $totalPembandingAwal,
                'total_pdrb_pembanding_akhir' => $totalPembandingAkhir,
                'laju_pertumbuhan_daerah' => $ri,
                'laju_pertumbuhan_pembanding' => $rp,
                'kontribusi_daerah' => $yi,
                'kontribusi_pembanding' => $yp,
                'gi' => $ri,
                'gr' => $rp,
                'si' => $yi,
                'sr' => $yp,
                'r_i' => $r_i,
                'r_p' => $r_p,
                'y_i' => $y_i,
                'y_p' => $y_p,
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'kuadran' => $kuadran,
                'klasifikasi_sektor' => $klasifikasiMap[$kuadran] ?? $kuadran,
            ],
        ]);

        Cache::flush();

        return redirect()->route('operator.klassen.index', ['tab' => 'simulasi'])->with('tab', 'simulasi')->with('success', 'Data simulasi Tipologi Klassen berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $userId = Auth::id();
        $item = AnalysisResult::where('type', 'tipologi_klassen')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereRaw("results->>'user_id' = ?", [(string)$userId]);
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'tingkat_wilayah' => 'required|string',
            'sektor' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
        ]);

        if ($request->has('tahun') && is_array($request->tahun) && count($request->tahun) >= 2) {
            $tahunArr = $request->tahun;
            $pdrbSektorArr = $request->pdrb_sektor_analisis ?? [];
            $totalPdrbArr = $request->total_pdrb_analisis ?? [];
            $pdrbPembandingArr = $request->pdrb_sektor_pembanding ?? [];
            $totalPembandingArr = $request->total_pdrb_pembanding ?? [];

            $tahunAwal = (int) ($tahunArr[0] ?? date('Y') - 1);
            $tahunAkhir = (int) ($tahunArr[count($tahunArr) - 1] ?? date('Y'));

            $yAwal = (float) str_replace(',', '.', str_replace('.', '', $pdrbSektorArr[0] ?? 0));
            $yAkhir = (float) str_replace(',', '.', str_replace('.', '', $pdrbSektorArr[count($pdrbSektorArr) - 1] ?? 0));

            $totalYAwal = (float) str_replace(',', '.', str_replace('.', '', $totalPdrbArr[0] ?? 0));
            $totalYAkhir = (float) str_replace(',', '.', str_replace('.', '', $totalPdrbArr[count($totalPdrbArr) - 1] ?? 0));

            $yPembandingAwal = (float) str_replace(',', '.', str_replace('.', '', $pdrbPembandingArr[0] ?? 0));
            $yPembandingAkhir = (float) str_replace(',', '.', str_replace('.', '', $pdrbPembandingArr[count($pdrbPembandingArr) - 1] ?? 0));

            $totalPembandingAwal = (float) str_replace(',', '.', str_replace('.', '', $totalPembandingArr[0] ?? 0));
            $totalPembandingAkhir = (float) str_replace(',', '.', str_replace('.', '', $totalPembandingArr[count($totalPembandingArr) - 1] ?? 0));

            $ri = ($yAwal > 0) ? (($yAkhir - $yAwal) / $yAwal) * 100 : 0;
            $rp = ($totalPembandingAwal > 0) ? (($totalPembandingAkhir - $totalPembandingAwal) / $totalPembandingAwal) * 100 : 0;
            $yi = ($totalYAkhir > 0) ? ($yAkhir / $totalYAkhir) * 100 : 0;
            $yp = ($totalPembandingAkhir > 0) ? ($yPembandingAkhir / $totalPembandingAkhir) * 100 : 0;
        } else {
            $tahunAwal = (int) ($request->tahun_awal ?? $request->tahun - 1 ?? date('Y') - 1);
            $tahunAkhir = (int) ($request->tahun_akhir ?? $request->tahun ?? date('Y'));
            $ri = (float) ($request->laju_pertumbuhan_daerah ?? 0);
            $rp = (float) ($request->laju_pertumbuhan_pembanding ?? 0);
            $yi = (float) ($request->kontribusi_daerah ?? 0);
            $yp = (float) ($request->kontribusi_pembanding ?? 0);
            $yAwal = 0; $yAkhir = 0; $totalYAwal = 0; $totalYAkhir = 0;
            $yPembandingAwal = 0; $yPembandingAkhir = 0; $totalPembandingAwal = 0; $totalPembandingAkhir = 0;
        }

        $r_i = $ri / 100;
        $r_p = $rp / 100;
        $y_i = $yi / 100;
        $y_p = $yp / 100;

        $kuadran = '';
        if ($r_i >= $r_p && $y_i >= $y_p) $kuadran = 'Kuadran I';
        elseif ($r_i < $r_p && $y_i >= $y_p) $kuadran = 'Kuadran II';
        elseif ($r_i >= $r_p && $y_i < $y_p) $kuadran = 'Kuadran III';
        else $kuadran = 'Kuadran IV';

        $klasifikasiMap = [
            'Kuadran I' => 'Sektor Maju dan Tumbuh Pesat',
            'Kuadran II' => 'Sektor Maju tapi Tertekan',
            'Kuadran III' => 'Sektor Berkembang Cepat / Potensial',
            'Kuadran IV' => 'Sektor Relatif Tertinggal',
        ];

        $daerahAnalisis = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? strtoupper($validated['provinsi']) 
            : strtoupper($validated['kabupaten'] ?? $validated['provinsi']);
            
        $daerahPembanding = ($validated['tingkat_wilayah'] === 'Provinsi') 
            ? 'PDB NASIONAL' 
            : 'PDRB ' . strtoupper($validated['provinsi']);

        $item->update([
            'title' => 'Simulasi Tipologi Klassen ' . $daerahAnalisis . ' (' . $validated['sektor'] . ')',
            'results' => [
                'tingkat_wilayah' => $validated['tingkat_wilayah'],
                'provinsi' => $validated['provinsi'],
                'kabupaten' => $validated['kabupaten'] ?? null,
                'sektor' => $validated['sektor'],
                'tahun' => $tahunAkhir,
                'tahun_awal' => $tahunAwal,
                'tahun_akhir' => $tahunAkhir,
                'pdrb_sektor_analisis_awal' => $yAwal,
                'pdrb_sektor_analisis_akhir' => $yAkhir,
                'total_pdrb_analisis_awal' => $totalYAwal,
                'total_pdrb_analisis_akhir' => $totalYAkhir,
                'pdrb_sektor_pembanding_awal' => $yPembandingAwal,
                'pdrb_sektor_pembanding_akhir' => $yPembandingAkhir,
                'total_pdrb_pembanding_awal' => $totalPembandingAwal,
                'total_pdrb_pembanding_akhir' => $totalPembandingAkhir,
                'laju_pertumbuhan_daerah' => $ri,
                'laju_pertumbuhan_pembanding' => $rp,
                'kontribusi_daerah' => $yi,
                'kontribusi_pembanding' => $yp,
                'gi' => $ri,
                'gr' => $rp,
                'si' => $yi,
                'sr' => $yp,
                'r_i' => $r_i,
                'r_p' => $r_p,
                'y_i' => $y_i,
                'y_p' => $y_p,
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'kuadran' => $kuadran,
                'klasifikasi_sektor' => $klasifikasiMap[$kuadran] ?? $kuadran,
            ],
        ]);

        Cache::flush();

        return redirect()->route('operator.klassen.index', ['tab' => 'simulasi'])->with('tab', 'simulasi')->with('success', 'Data simulasi Tipologi Klassen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $userId = Auth::id();
        $item = AnalysisResult::where('type', 'tipologi_klassen')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereRaw("results->>'user_id' = ?", [(string)$userId]);
            })
            ->findOrFail($id);

        $item->delete();

        Cache::flush();

        return redirect()->route('operator.klassen.index', ['tab' => 'simulasi'])->with('tab', 'simulasi')->with('success', 'Data simulasi Tipologi Klassen berhasil dihapus.');
    }

    public function empty()
    {
        $userId = Auth::id();
        AnalysisResult::where('type', 'tipologi_klassen')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereRaw("results->>'user_id' = ?", [(string)$userId]);
            })
            ->delete();

        Cache::flush();

        return redirect()->route('operator.klassen.index', ['tab' => 'simulasi'])->with('tab', 'simulasi')->with('success', 'Seluruh data simulasi Tipologi Klassen berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (!is_array($data) || empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Data impor tidak valid atau kosong.'
            ], 422);
        }

        $grouped = [];
        foreach ($data as $row) {
            if (!is_array($row)) continue;
            $prov = $row['Provinsi'] ?? $row['provinsi'] ?? 'SUMATERA UTARA';
            $kab = $row['Kabupaten/Kota'] ?? $row['Kabupaten'] ?? $row['kabupaten'] ?? '-';
            $sektor = $row['Sektor'] ?? $row['sektor'] ?? '';
            if (empty($sektor)) continue;

            $key = strtoupper($prov) . '|' . strtoupper($kab) . '|' . strtoupper($sektor);
            $grouped[$key][] = $row;
        }

        $count = 0;
        foreach ($grouped as $key => $rows) {
            usort($rows, function($a, $b) {
                $tA = (int)($a['Tahun'] ?? $a['tahun'] ?? 0);
                $tB = (int)($b['Tahun'] ?? $b['tahun'] ?? 0);
                return $tA <=> $tB;
            });

            $firstRow = $rows[0];
            $prov = $firstRow['Provinsi'] ?? $firstRow['provinsi'] ?? 'SUMATERA UTARA';
            $kab = $firstRow['Kabupaten/Kota'] ?? $firstRow['Kabupaten'] ?? $firstRow['kabupaten'] ?? null;
            $sektor = $firstRow['Sektor'] ?? $firstRow['sektor'] ?? '';

            if (count($rows) >= 2) {
                $rowAwal = $rows[0];
                $rowAkhir = $rows[count($rows) - 1];

                $tahunAwal = (int) ($rowAwal['Tahun'] ?? $rowAwal['tahun'] ?? date('Y') - 1);
                $tahunAkhir = (int) ($rowAkhir['Tahun'] ?? $rowAkhir['tahun'] ?? date('Y'));

                $yAwal = (float) ($rowAwal['PDRB Sektor'] ?? $rowAwal['pdrb_sektor'] ?? 0);
                $yAkhir = (float) ($rowAkhir['PDRB Sektor'] ?? $rowAkhir['pdrb_sektor'] ?? 0);

                $totalYAwal = (float) ($rowAwal['Total PDRB'] ?? $rowAwal['total_pdrb'] ?? 0);
                $totalYAkhir = (float) ($rowAkhir['Total PDRB'] ?? $rowAkhir['total_pdrb'] ?? 0);

                $yPembandingAwal = (float) ($rowAwal['PDRB Sektor Pembanding'] ?? $rowAwal['pdrb_sektor_pembanding'] ?? 0);
                $yPembandingAkhir = (float) ($rowAkhir['PDRB Sektor Pembanding'] ?? $rowAkhir['pdrb_sektor_pembanding'] ?? 0);

                $totalPembandingAwal = (float) ($rowAwal['Total PDRB Pembanding'] ?? $rowAwal['total_pdrb_pembanding'] ?? 0);
                $totalPembandingAkhir = (float) ($rowAkhir['Total PDRB Pembanding'] ?? $rowAkhir['total_pdrb_pembanding'] ?? 0);

                $ri = ($yAwal > 0) ? (($yAkhir - $yAwal) / $yAwal) * 100 : 0;
                $rp = ($totalPembandingAwal > 0) ? (($totalPembandingAkhir - $totalPembandingAwal) / $totalPembandingAwal) * 100 : 0;
                $yi = ($totalYAkhir > 0) ? ($yAkhir / $totalYAkhir) * 100 : 0;
                $yp = ($totalPembandingAkhir > 0) ? ($yPembandingAkhir / $totalPembandingAkhir) * 100 : 0;
            } else {
                $singleRow = $rows[0];
                $tahunAwal = (int) ($singleRow['Tahun Awal'] ?? $singleRow['tahun_awal'] ?? date('Y') - 1);
                $tahunAkhir = (int) ($singleRow['Tahun'] ?? $singleRow['tahun'] ?? date('Y'));

                $ri = (float) ($singleRow['laju_pertumbuhan_daerah'] ?? $singleRow['r_i'] ?? 0);
                $rp = (float) ($singleRow['laju_pertumbuhan_pembanding'] ?? $singleRow['r_p'] ?? 0);
                $yi = (float) ($singleRow['kontribusi_daerah'] ?? $singleRow['y_i'] ?? 0);
                $yp = (float) ($singleRow['kontribusi_pembanding'] ?? $singleRow['y_p'] ?? 0);

                $yAwal = 0; $yAkhir = 0; $totalYAwal = 0; $totalYAkhir = 0;
                $yPembandingAwal = 0; $yPembandingAkhir = 0; $totalPembandingAwal = 0; $totalPembandingAkhir = 0;
            }

            $r_i = $ri / 100;
            $r_p = $rp / 100;
            $y_i = $yi / 100;
            $y_p = $yp / 100;

            $kuadran = '';
            if ($r_i >= $r_p && $y_i >= $y_p) $kuadran = 'Kuadran I';
            elseif ($r_i < $r_p && $y_i >= $y_p) $kuadran = 'Kuadran II';
            elseif ($r_i >= $r_p && $y_i < $y_p) $kuadran = 'Kuadran III';
            else $kuadran = 'Kuadran IV';

            $klasifikasiMap = [
                'Kuadran I' => 'Sektor Maju dan Tumbuh Pesat',
                'Kuadran II' => 'Sektor Maju tapi Tertekan',
                'Kuadran III' => 'Sektor Berkembang Cepat / Potensial',
                'Kuadran IV' => 'Sektor Relatif Tertinggal',
            ];

            $tingkatWilayah = ($kab && strtoupper($kab) !== 'PROVINSI' && $kab !== '-') ? 'Kabupaten/Kota' : 'Provinsi';

            $daerahAnalisis = ($tingkatWilayah === 'Provinsi') 
                ? strtoupper($prov) 
                : strtoupper($kab ?? $prov);
                
            $daerahPembanding = ($tingkatWilayah === 'Provinsi') 
                ? 'PDB NASIONAL' 
                : 'PDRB ' . strtoupper($prov);

            AnalysisResult::create([
                'user_id' => Auth::id(),
                'type' => 'tipologi_klassen',
                'title' => 'Simulasi Tipologi Klassen ' . $daerahAnalisis . ' (' . $sektor . ')',
                'results' => [
                    'tingkat_wilayah' => $tingkatWilayah,
                    'provinsi' => $prov,
                    'kabupaten' => $kab,
                    'sektor' => $sektor,
                    'tahun' => $tahunAkhir,
                    'tahun_awal' => $tahunAwal,
                    'tahun_akhir' => $tahunAkhir,
                    'pdrb_sektor_analisis_awal' => $yAwal,
                    'pdrb_sektor_analisis_akhir' => $yAkhir,
                    'total_pdrb_analisis_awal' => $totalYAwal,
                    'total_pdrb_analisis_akhir' => $totalYAkhir,
                    'pdrb_sektor_pembanding_awal' => $yPembandingAwal,
                    'pdrb_sektor_pembanding_akhir' => $yPembandingAkhir,
                    'total_pdrb_pembanding_awal' => $totalPembandingAwal,
                    'total_pdrb_pembanding_akhir' => $totalPembandingAkhir,
                    'laju_pertumbuhan_daerah' => $ri,
                    'laju_pertumbuhan_pembanding' => $rp,
                    'kontribusi_daerah' => $yi,
                    'kontribusi_pembanding' => $yp,
                    'gi' => $ri,
                    'gr' => $rp,
                    'si' => $yi,
                    'sr' => $yp,
                    'r_i' => $r_i,
                    'r_p' => $r_p,
                    'y_i' => $y_i,
                    'y_p' => $y_p,
                    'daerah_analisis' => $daerahAnalisis,
                    'daerah_pembanding' => $daerahPembanding,
                    'kuadran' => $kuadran,
                    'klasifikasi_sektor' => $klasifikasiMap[$kuadran] ?? $kuadran,
                ],
            ]);
            $count++;
        }

        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengimpor {$count} data simulasi Tipologi Klassen."
        ]);
    }
}

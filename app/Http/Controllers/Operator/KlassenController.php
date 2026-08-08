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

        $editItem = null;
        if ($request->has('edit')) {
            $editItem = AnalysisResult::where('type', 'tipologi_klassen')->find((int)$request->edit);
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
        $paginatedSectors = $query->paginate(20)->withQueryString();

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
            'tahun' => 'required|numeric',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'laju_pertumbuhan_daerah' => 'required|numeric',
            'laju_pertumbuhan_pembanding' => 'required|numeric',
            'kontribusi_daerah' => 'required|numeric',
            'kontribusi_pembanding' => 'required|numeric',
        ]);

        $r_i = $validated['laju_pertumbuhan_daerah'] / 100;
        $r_p = $validated['laju_pertumbuhan_pembanding'] / 100;
        $y_i = $validated['kontribusi_daerah'] / 100;
        $y_p = $validated['kontribusi_pembanding'] / 100;

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
            'results' => array_merge($validated, [
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'kuadran' => $kuadran,
                'klasifikasi_sektor' => $klasifikasiMap[$kuadran],
            ]),
        ]);

        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Data simulasi Tipologi Klassen berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $item = AnalysisResult::where('type', 'tipologi_klassen')->findOrFail($id);

        $validated = $request->validate([
            'tingkat_wilayah' => 'required|string',
            'sektor' => 'required|string',
            'tahun' => 'required|numeric',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'laju_pertumbuhan_daerah' => 'required|numeric',
            'laju_pertumbuhan_pembanding' => 'required|numeric',
            'kontribusi_daerah' => 'required|numeric',
            'kontribusi_pembanding' => 'required|numeric',
        ]);

        $r_i = $validated['laju_pertumbuhan_daerah'] / 100;
        $r_p = $validated['laju_pertumbuhan_pembanding'] / 100;
        $y_i = $validated['kontribusi_daerah'] / 100;
        $y_p = $validated['kontribusi_pembanding'] / 100;

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
            'results' => array_merge($validated, [
                'daerah_analisis' => $daerahAnalisis,
                'daerah_pembanding' => $daerahPembanding,
                'kuadran' => $kuadran,
                'klasifikasi_sektor' => $klasifikasiMap[$kuadran],
            ]),
        ]);

        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Data simulasi Tipologi Klassen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = AnalysisResult::where('type', 'tipologi_klassen')->findOrFail($id);
        $item->delete();

        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Data simulasi Tipologi Klassen berhasil dihapus.');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'tipologi_klassen')->delete();

        Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Seluruh data simulasi Tipologi Klassen berhasil dihapus.');
    }
}

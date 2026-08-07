<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Models\SummaryShiftShareResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SsController extends Controller
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

        $allYears = Cache::remember('summary_ss_available_years', 3600, function () {
            $years = SummaryShiftShareResult::distinct()->orderBy('tahun_akhir', 'desc')->pluck('tahun_akhir');
            return $years->isEmpty() ? collect([2024, 2023, 2022, 2021, 2020]) : $years;
        });

        // Query Rekapitulasi Shift Share dari Tabel Summary
        $query = SummaryShiftShareResult::with(['provinsi', 'kabupaten'])
            ->selectRaw('tingkat_wilayah, provinsi_id, kabupaten_id, tahun_awal, tahun_akhir,
                SUM(n_nij) as total_n, SUM(c_cij) as total_c, SUM(s_sij) as total_s, SUM(d_dij) as total_d,
                COUNT(CASE WHEN d_dij >= 0 THEN 1 END) as cepat_count,
                COUNT(CASE WHEN d_dij < 0 THEN 1 END) as lambat_count,
                COUNT(CASE WHEN s_sij >= 0 THEN 1 END) as kompetitif_count,
                COUNT(CASE WHEN s_sij < 0 THEN 1 END) as non_kompetitif_count')
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

            $dijTotal = (float)$item->total_d;
            $cijTotal = (float)$item->total_s; // Differential Shift = Competitiveness

            $kategoriPertumbuhan = $dijTotal >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat';
            $kategoriDayaSaing = $cijTotal >= 0 ? 'Daya Saing Baik' : 'Tidak Dapat Bersaing';

            $item->id = $idCounter++;
            $item->tingkat_wilayah_label = $isProv ? 'Provinsi' : 'Kabupaten/Kota';
            $item->daerah_analisis = $daerahAnalisis;
            $item->daerah_pembanding = $daerahPembanding;
            $item->provinsi = $provName;
            $item->kabupaten = $kabName;
            $item->tahun_awal = $item->tahun_awal;
            $item->tahun_akhir = $item->tahun_akhir;
            $item->tahun = "{$item->tahun_awal} - {$item->tahun_akhir}";
            $item->sektor_cepat_count = (int)$item->cepat_count;
            $item->sektor_lambat_count = (int)$item->lambat_count;
            $item->daya_saing_tinggi_count = (int)$item->kompetitif_count;
            $item->total_shift = $dijTotal;
            $item->kategori_pertumbuhan = $kategoriPertumbuhan;
            $item->kategori_daya_saing = $kategoriDayaSaing;
            $item->is_provinsi = $isProv;
            return $item;
        });

        $editItem = null;
        if ($request->has('edit')) {
            $editItem = AnalysisResult::where('type', 'shift_share')->find((int)$request->edit);
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
        $isProv = strtolower($tingkatWilayah) === 'provinsi';

        $query = SummaryShiftShareResult::with(['sektor', 'provinsi', 'kabupaten'])
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
                });
            });
        }

        $query->orderBy('sektor_id', 'asc');
        $paginatedSectors = $query->paginate(20)->withQueryString();

        $paginatedSectors->getCollection()->transform(function ($item) use ($tingkatWilayah, $namaDaerah, $namaPembanding, $tahun, $tahunAwal) {
            $nij = (float)$item->n_nij;
            $mij = (float)$item->c_cij; // Proportional Shift
            $cij = (float)$item->s_sij; // Differential Shift
            $dij = (float)$item->d_dij; // Net Change

            return [
                'tingkat_wilayah' => $tingkatWilayah,
                'daerah_analisis' => $namaDaerah,
                'daerah_pembanding' => $namaPembanding,
                'sektor' => $item->sektor->nama_sektor ?? '-',
                'tahun' => "{$tahunAwal} - {$tahun}",
                'nij' => $nij,
                'mij' => $mij,
                'cij' => $cij,
                'dij' => $dij,
                'komponen_n' => $nij,
                'komponen_p' => $mij,
                'komponen_d' => $cij,
                'total_shift' => $dij,
                'status_pertumbuhan' => $dij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat',
                'status_daya_saing' => $cij >= 0 ? 'Daya Saing Tinggi' : 'Daya Saing Rendah',
                'kategori_pertumbuhan' => $dij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat',
                'kategori_daya_saing' => $cij >= 0 ? 'Daya Saing Baik' : 'Tidak Dapat Bersaing',
            ];
        });

        return view('operator.potensi_unggulan.ss.show', [
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
            'komponen_n' => 'required|numeric',
            'komponen_p' => 'required|numeric',
            'komponen_d' => 'required|numeric',
        ]);

        $nij = (float)$validated['komponen_n'];
        $mij = (float)$validated['komponen_p'];
        $cij = (float)$validated['komponen_d'];
        $dij = $nij + $mij + $cij;

        $kategoriPertumbuhan = $dij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat';
        $kategoriDayaSaing = $cij >= 0 ? 'Daya Saing Baik' : 'Tidak Dapat Bersaing';

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
                'nij' => $nij,
                'mij' => $mij,
                'cij' => $cij,
                'dij' => $dij,
                'total_shift' => $dij,
                'kategori_pertumbuhan' => $kategoriPertumbuhan,
                'kategori_daya_saing' => $kategoriDayaSaing,
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
            'tahun' => 'required|numeric',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'komponen_n' => 'required|numeric',
            'komponen_p' => 'required|numeric',
            'komponen_d' => 'required|numeric',
        ]);

        $nij = (float)$validated['komponen_n'];
        $mij = (float)$validated['komponen_p'];
        $cij = (float)$validated['komponen_d'];
        $dij = $nij + $mij + $cij;

        $kategoriPertumbuhan = $dij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat';
        $kategoriDayaSaing = $cij >= 0 ? 'Daya Saing Baik' : 'Tidak Dapat Bersaing';

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
                'nij' => $nij,
                'mij' => $mij,
                'cij' => $cij,
                'dij' => $dij,
                'total_shift' => $dij,
                'kategori_pertumbuhan' => $kategoriPertumbuhan,
                'kategori_daya_saing' => $kategoriDayaSaing,
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
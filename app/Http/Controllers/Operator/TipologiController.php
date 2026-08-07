<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Models\SummaryTipologiSektorResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TipologiController extends Controller
{
    private function getAuthorizedKabupatens($user)
    {
        return Kabupaten::orderBy('nama_kabupaten')->get();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $authorizedKabupatens = $this->getAuthorizedKabupatens($user);

        $allYears = SummaryTipologiSektorResult::distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        if ($allYears->isEmpty()) {
            $allYears = collect([2024, 2023, 2022, 2021, 2020]);
        }

        // Query Rekapitulasi Tipologi Sektor dari Tabel Summary
        $query = SummaryTipologiSektorResult::with(['provinsi', 'kabupaten'])
            ->selectRaw('tingkat_wilayah, provinsi_id, kabupaten_id, tahun,
                COUNT(CASE WHEN klasifikasi_sektor = \'Maju dan Tumbuh Cepat\' THEN 1 END) as c1_count,
                COUNT(CASE WHEN klasifikasi_sektor = \'Potensial / Cepat Berkembang\' THEN 1 END) as c2_count,
                COUNT(CASE WHEN klasifikasi_sektor = \'Maju tapi Tertekan\' THEN 1 END) as c3_count,
                COUNT(CASE WHEN klasifikasi_sektor = \'Relatif Tertinggal\' THEN 1 END) as c4_count')
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
            $dominantKuadran = $maxCount === $c1 ? 'Kuadran I (Maju dan Tumbuh Cepat)'
                : ($maxCount === $c2 ? 'Kuadran II (Potensial / Cepat Berkembang)'
                : ($maxCount === $c3 ? 'Kuadran III (Maju tapi Tertekan)' : 'Kuadran IV (Relatif Tertinggal)'));

            $item->id = $idCounter++;
            $item->tingkat_wilayah_label = $isProv ? 'Provinsi' : 'Kabupaten/Kota';
            $item->daerah_analisis = $daerahAnalisis;
            $item->daerah_pembanding = $daerahPembanding;
            $item->provinsi = $provName;
            $item->kabupaten = $kabName;
            $item->tahun = (int)$item->tahun;
            $item->status_dominan = $dominantKuadran;
            $item->is_provinsi = $isProv;
            return $item;
        });

        $editItem = null;
        if ($request->has('edit')) {
            $editItem = AnalysisResult::where('type', 'tipologi_sektor')->find((int)$request->edit);
        }

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
        $isProv = strtolower($tingkatWilayah) === 'provinsi';

        $query = SummaryTipologiSektorResult::with(['sektor', 'provinsi', 'kabupaten'])
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
                })->orWhereRaw('LOWER(klasifikasi_sektor) LIKE ?', ["%{$searchLower}%"]);
            });
        }

        $query->orderBy('sektor_id', 'asc');
        $paginatedSectors = $query->paginate(20)->withQueryString();

        $paginatedSectors->getCollection()->transform(function ($item) use ($tingkatWilayah, $namaDaerah, $namaPembanding, $tahun) {
            $lq = (float)$item->nilai_lq;
            $cij = (float)$item->shift_share_net;

            $kuadran = '';
            if ($lq >= 1 && $cij >= 0) $kuadran = 'Kuadran I';
            elseif ($lq < 1 && $cij >= 0) $kuadran = 'Kuadran II';
            elseif ($lq >= 1 && $cij < 0) $kuadran = 'Kuadran III';
            else $kuadran = 'Kuadran IV';

            return [
                'tingkat_wilayah' => $tingkatWilayah,
                'daerah_analisis' => $namaDaerah,
                'daerah_pembanding' => $namaPembanding,
                'sektor' => $item->sektor->nama_sektor ?? '-',
                'tahun' => $tahun,
                'lq' => $lq,
                'cij' => $cij,
                'kuadran' => $kuadran,
                'kategori_sektor' => $item->klasifikasi_sektor,
            ];
        });

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
            'shift_share_net' => 'required|numeric',
        ]);

        $lq = (float)$validated['nilai_lq'];
        $cij = (float)$validated['shift_share_net'];

        $kuadran = '';
        if ($lq >= 1 && $cij >= 0) $kuadran = 'Kuadran I';
        elseif ($lq < 1 && $cij >= 0) $kuadran = 'Kuadran II';
        elseif ($lq >= 1 && $cij < 0) $kuadran = 'Kuadran III';
        else $kuadran = 'Kuadran IV';

        $kategoriMap = [
            'Kuadran I' => 'Maju dan Tumbuh Cepat',
            'Kuadran II' => 'Potensial / Cepat Berkembang',
            'Kuadran III' => 'Maju tapi Tertekan',
            'Kuadran IV' => 'Relatif Tertinggal',
        ];

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
                'lq' => $lq,
                'cij' => $cij,
                'kuadran' => $kuadran,
                'kategori_sektor' => $kategoriMap[$kuadran] ?? $kuadran,
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
            'shift_share_net' => 'required|numeric',
        ]);

        $lq = (float)$validated['nilai_lq'];
        $cij = (float)$validated['shift_share_net'];

        $kuadran = '';
        if ($lq >= 1 && $cij >= 0) $kuadran = 'Kuadran I';
        elseif ($lq < 1 && $cij >= 0) $kuadran = 'Kuadran II';
        elseif ($lq >= 1 && $cij < 0) $kuadran = 'Kuadran III';
        else $kuadran = 'Kuadran IV';

        $kategoriMap = [
            'Kuadran I' => 'Maju dan Tumbuh Cepat',
            'Kuadran II' => 'Potensial / Cepat Berkembang',
            'Kuadran III' => 'Maju tapi Tertekan',
            'Kuadran IV' => 'Relatif Tertinggal',
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
                'lq' => $lq,
                'cij' => $cij,
                'kuadran' => $kuadran,
                'kategori_sektor' => $kategoriMap[$kuadran] ?? $kuadran,
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
}

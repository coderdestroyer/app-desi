<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Sektor;
use App\Services\TipologiKlassenService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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
        if ($user->isAdmin()) {
            return Kabupaten::orderBy('nama_kabupaten')->get();
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('user_wilayah_scopes')) {
            return Kabupaten::orderBy('nama_kabupaten')->get();
        }

        $scope = \App\Models\UserWilayahScope::where('user_id', $user->id)->first();
        if (!$scope) {
            return Kabupaten::orderBy('nama_kabupaten')->get();
        }

        if ($scope->kabupaten_id) {
            return Kabupaten::where('kab_id', $scope->kabupaten_id)->get();
        }

        if ($scope->provinsi_id) {
            return Kabupaten::where('provinsi_id', $scope->provinsi_id)->orderBy('nama_kabupaten')->get();
        }

        return Kabupaten::orderBy('nama_kabupaten')->get();
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $authorizedKabupatens = $this->getAuthorizedKabupatens($user);
        $authorizedIds = $authorizedKabupatens->pluck('kab_id')->toArray();

        $savedResults = AnalysisResult::where('type', 'tipologi_klassen')->orderBy('id', 'desc')->get();

        if ($savedResults->isNotEmpty()) {
            $mappedData = $savedResults->map(function ($item) {
                $res = $item->results ?? [];
                return [
                    'id' => $item->id,
                    'tingkat_wilayah' => $res['tingkat_wilayah'] ?? 'Kabupaten/Kota',
                    'daerah_analisis' => $res['daerah_analisis'] ?? '-',
                    'daerah_pembanding' => $res['daerah_pembanding'] ?? '-',
                    'provinsi' => $res['provinsi'] ?? '-',
                    'kabupaten' => $res['kabupaten'] ?? '-',
                    'sektor' => $res['sektor'] ?? '-',
                    'tahun_awal' => $res['tahun_awal'] ?? '-',
                    'tahun_akhir' => $res['tahun_akhir'] ?? '-',
                    'pdrb_sektor_analisis_awal' => $res['pdrb_sektor_analisis_awal'] ?? 0,
                    'pdrb_sektor_analisis_akhir' => $res['pdrb_sektor_analisis_akhir'] ?? 0,
                    'total_pdrb_analisis_awal' => $res['total_pdrb_analisis_awal'] ?? 0,
                    'total_pdrb_analisis_akhir' => $res['total_pdrb_analisis_akhir'] ?? 0,
                    'pdrb_sektor_pembanding_awal' => $res['pdrb_sektor_pembanding_awal'] ?? 0,
                    'pdrb_sektor_pembanding_akhir' => $res['pdrb_sektor_pembanding_akhir'] ?? 0,
                    'total_pdrb_pembanding_awal' => $res['total_pdrb_pembanding_awal'] ?? 0,
                    'total_pdrb_pembanding_akhir' => $res['total_pdrb_pembanding_akhir'] ?? 0,
                    'ri' => number_format((float)($res['ri'] ?? 0), 3, '.', ''),
                    'r' => number_format((float)($res['r'] ?? 0), 3, '.', ''),
                    'yi' => number_format((float)($res['yi'] ?? 0), 3, '.', ''),
                    'y' => number_format((float)($res['y'] ?? 0), 3, '.', ''),
                    'kuadran' => $res['kuadran'] ?? '-',
                    'klasifikasi' => $res['klasifikasi'] ?? '-',
                    'riwayat' => 'Diperbarui ' . $item->updated_at->format('d-m-Y'),
                ];
            });
        } else {
            $maxTahun = DB::table('pdrb_sumatera_kabupaten')->whereIn('kabupaten_id', $authorizedIds)->max('tahun') ?? 2024;
            $selectedTahun = $request->has('tahun') && !empty($request->tahun) ? (int)$request->tahun : (int)$maxTahun;
            $cacheKey = 'calc_klassen_' . md5(implode('_', $authorizedIds) . '_' . $selectedTahun);

            $mappedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($authorizedKabupatens, $selectedTahun) {
                $mappedRows = [];
                $idCounter = 1;

                foreach ($authorizedKabupatens as $kab) {
                    $dynamicKlassen = $this->klassenService->calculateKlassen($kab->kab_id, $selectedTahun);
                    foreach ($dynamicKlassen as $item) {
                        $mappedRows[] = [
                            'id' => $idCounter++,
                            'tingkat_wilayah' => 'Kabupaten/Kota',
                            'daerah_analisis' => strtoupper($kab->nama_kabupaten),
                            'daerah_pembanding' => 'SUMATERA UTARA',
                            'provinsi' => 'SUMATERA UTARA',
                            'kabupaten' => strtoupper($kab->nama_kabupaten),
                            'sektor' => $item['sektor']->nama_sektor ?? '-',
                            'tahun_awal' => $selectedTahun - 1,
                            'tahun_akhir' => $selectedTahun,
                            'pdrb_sektor_analisis_awal' => 0,
                            'pdrb_sektor_analisis_akhir' => 0,
                            'total_pdrb_analisis_awal' => 0,
                            'total_pdrb_analisis_akhir' => 0,
                            'pdrb_sektor_pembanding_awal' => 0,
                            'pdrb_sektor_pembanding_akhir' => 0,
                            'total_pdrb_pembanding_awal' => 0,
                            'total_pdrb_pembanding_akhir' => 0,
                            'ri' => number_format($item['laju_pertumbuhan'] ?? 0, 3, '.', ''),
                            'r' => number_format($item['laju_pertumbuhan_acuan'] ?? 0, 3, '.', ''),
                            'yi' => number_format($item['kontribusi_pdrb'] ?? 0, 3, '.', ''),
                            'y' => number_format($item['kontribusi_acuan'] ?? 0, 3, '.', ''),
                            'kuadran' => $item['kuadran'] ?? 'Kuadran IV',
                            'klasifikasi' => $item['klasifikasi_sektor'] ?? 'Sektor Relatif Tertinggal',
                            'riwayat' => 'Kalkulasi Otomatis',
                        ];
                    }
                }

                return collect($mappedRows);
            });
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = strtolower($request->search);
            $mappedData = $mappedData->filter(function ($row) use ($search) {
                return str_contains(strtolower($row['daerah_analisis']), $search) ||
                       str_contains(strtolower($row['sektor']), $search) ||
                       str_contains((string)$row['tahun_akhir'], $search);
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
        ))->onEachSide(1);

        return view('operator.potensi_unggulan.klassen.index', [
            'klassenData' => $paginatedData,
            'editItem' => $editItem,
            'editData' => $editItem,
        ]);
    }

    private function calculateKlassenData(Request $request)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'sektor' => 'required|string',
            'tahun_awal' => 'required|numeric',
            'tahun_akhir' => 'required|numeric',
            'pdrb_sektor_analisis_awal' => 'required',
            'pdrb_sektor_analisis_akhir' => 'required',
            'total_pdrb_analisis_awal' => 'required',
            'total_pdrb_analisis_akhir' => 'required',
            'pdrb_sektor_pembanding_awal' => 'required',
            'pdrb_sektor_pembanding_akhir' => 'required',
            'total_pdrb_pembanding_awal' => 'required',
            'total_pdrb_pembanding_akhir' => 'required',
        ]);

        $daerah_analisis = $request->tingkat_wilayah === 'Provinsi' ? $request->provinsi : $request->kabupaten;
        $daerah_pembanding = $request->tingkat_wilayah === 'Provinsi' ? 'Nasional' : $request->provinsi;

        $yij_awal = $this->parseNumber($request->pdrb_sektor_analisis_awal);
        $yij_akhir = $this->parseNumber($request->pdrb_sektor_analisis_akhir);
        $yj_awal = $this->parseNumber($request->total_pdrb_analisis_awal);
        $yj_akhir = $this->parseNumber($request->total_pdrb_analisis_akhir);

        $yin_awal = $this->parseNumber($request->pdrb_sektor_pembanding_awal);
        $yin_akhir = $this->parseNumber($request->pdrb_sektor_pembanding_akhir);
        $yn_awal = $this->parseNumber($request->total_pdrb_pembanding_awal);
        $yn_akhir = $this->parseNumber($request->total_pdrb_pembanding_akhir);

        $ri = $yij_awal > 0 ? ($yij_akhir - $yij_awal) / $yij_awal : 0;
        $r = $yin_awal > 0 ? ($yin_akhir - $yin_awal) / $yin_awal : 0;
        $yi = $yj_akhir > 0 ? $yij_akhir / $yj_akhir : 0;
        $y = $yn_akhir > 0 ? $yin_akhir / $yn_akhir : 0;

        if ($yi > $y && $ri > $r) {
            $kuadran = 'Kuadran I';
            $klasifikasi = 'Sektor Maju dan Tumbuh Cepat';
        } elseif ($yi > $y && $ri < $r) {
            $kuadran = 'Kuadran II';
            $klasifikasi = 'Sektor Maju tapi Tertekan';
        } elseif ($yi < $y && $ri > $r) {
            $kuadran = 'Kuadran III';
            $klasifikasi = 'Sektor Potensial atau Cepat Berkembang';
        } else {
            $kuadran = 'Kuadran IV';
            $klasifikasi = 'Sektor Relatif Tertinggal';
        }

        return [
            'tingkat_wilayah' => $request->tingkat_wilayah,
            'provinsi' => $request->provinsi,
            'kabupaten' => $request->tingkat_wilayah === 'Provinsi' ? '-' : ($request->kabupaten ?? '-'),
            'daerah_analisis' => $daerah_analisis,
            'daerah_pembanding' => $daerah_pembanding,
            'sektor' => $request->sektor,
            'tahun_awal' => $request->tahun_awal,
            'tahun_akhir' => $request->tahun_akhir,
            'pdrb_sektor_analisis_awal' => $yij_awal,
            'pdrb_sektor_analisis_akhir' => $yij_akhir,
            'total_pdrb_analisis_awal' => $yj_awal,
            'total_pdrb_analisis_akhir' => $yj_akhir,
            'pdrb_sektor_pembanding_awal' => $yin_awal,
            'pdrb_sektor_pembanding_akhir' => $yin_akhir,
            'total_pdrb_pembanding_awal' => $yn_awal,
            'total_pdrb_pembanding_akhir' => $yn_akhir,
            'ri' => $ri,
            'r' => $r,
            'yi' => $yi,
            'y' => $y,
            'kuadran' => $kuadran,
            'klasifikasi' => $klasifikasi,
        ];
    }

    public function store(Request $request)
    {
        $newData = $this->calculateKlassenData($request);

        if (!$newData) {
            return back()->with('error', 'Semua form PDRB wajib diisi dengan angka valid!');
        }

        AnalysisResult::create([
            'type' => 'tipologi_klassen',
            'title' => "Simulasi Klassen - {$newData['daerah_analisis']} ({$newData['tahun_awal']}-{$newData['tahun_akhir']})",
            'description' => "Perhitungan Tipologi Klassen Sektor {$newData['sektor']} untuk {$newData['daerah_analisis']}",
            'results' => $newData,
        ]);

        OperatorController::logActivity('Analisis Klassen', 'ditambah', "Menambah data Tipologi Klassen {$newData['daerah_analisis']}");
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Data perhitungan Tipologi Klassen berhasil disimpan secara permanen!');
    }

    public function update(Request $request, $id)
    {
        $res = AnalysisResult::where('type', 'tipologi_klassen')->find($id);
        if (!$res) {
            return redirect()->route('operator.klassen.index')->with('error', 'Data tidak ditemukan!');
        }

        $updatedData = $this->calculateKlassenData($request);

        if (!$updatedData) {
            return back()->with('error', 'Semua form PDRB wajib diisi meggunakan angka valid!');
        }

        $res->update([
            'title' => "Simulasi Klassen - {$updatedData['daerah_analisis']} ({$updatedData['tahun_awal']}-{$updatedData['tahun_akhir']})",
            'description' => "Perhitungan Tipologi Klassen Sektor {$updatedData['sektor']} untuk {$updatedData['daerah_analisis']}",
            'results' => $updatedData,
        ]);

        OperatorController::logActivity('Analisis Klassen', 'diubah', "Mengubah data Tipologi Klassen {$updatedData['daerah_analisis']}");
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('operator.klassen.index')->with('success', 'Data perhitungan Tipologi Klassen berhasil diperbarui secara permanen!');
    }

    public function destroy($id)
    {
        $res = AnalysisResult::where('type', 'tipologi_klassen')->find($id);

        if ($res) {
            $daerah = $res->results['daerah_analisis'] ?? 'Daerah';
            $res->delete();
            OperatorController::logActivity('Analisis Klassen', 'dihapus', "Menghapus data Tipologi Klassen {$daerah}");
            \Illuminate\Support\Facades\Cache::flush();
        }

        return back()->with('success', 'Data perhitungan Tipologi Klassen berhasil dihapus secara permanen!');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'tipologi_klassen')->delete();
        OperatorController::logActivity('Analisis Klassen', 'dihapus', "Menghapus semua data Tipologi Klassen");
        \Illuminate\Support\Facades\Cache::flush();
        return back()->with('success', 'Semua data perhitungan Tipologi Klassen berhasil dihapus secara permanen!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (!empty($ids)) {
            $count = count($ids);
            AnalysisResult::where('type', 'tipologi_klassen')->whereIn('id', $ids)->delete();
            OperatorController::logActivity('Analisis Klassen', 'dihapus', "Menghapus {$count} data Tipologi Klassen secara massal");
            \Illuminate\Support\Facades\Cache::flush();
            return back()->with('success', "{$count} data Tipologi Klassen berhasil dihapus secara massal!");
        }
        return back()->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Sektor;
use App\Services\SsaService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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

        $savedResults = AnalysisResult::where('type', 'shift_share')->orderBy('id', 'desc')->get();

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
                    'pdrb_sektor_pembanding_awal' => $res['pdrb_sektor_pembanding_awal'] ?? 0,
                    'pdrb_sektor_pembanding_akhir' => $res['pdrb_sektor_pembanding_akhir'] ?? 0,
                    'total_pdrb_pembanding_awal' => $res['total_pdrb_pembanding_awal'] ?? 0,
                    'total_pdrb_pembanding_akhir' => $res['total_pdrb_pembanding_akhir'] ?? 0,
                    'rij' => number_format((float)($res['rij'] ?? 0), 4, '.', ''),
                    'rin' => number_format((float)($res['rin'] ?? 0), 4, '.', ''),
                    'rn' => number_format((float)($res['rn'] ?? 0), 4, '.', ''),
                    'nij' => $res['nij'] ?? 0,
                    'mij' => $res['mij'] ?? 0,
                    'cij' => $res['cij'] ?? 0,
                    'dij' => $res['dij'] ?? 0,
                    'status_pertumbuhan' => $res['status_pertumbuhan'] ?? '-',
                    'status_daya_saing' => $res['status_daya_saing'] ?? '-',
                    'riwayat' => 'Diperbarui ' . $item->updated_at->format('d-m-Y'),
                ];
            });
        } else {
            $maxTahun = DB::table('pdrb_sumatera_kabupaten')->whereIn('kabupaten_id', $authorizedIds)->max('tahun') ?? 2024;
            $selectedTahun = $request->has('tahun') && !empty($request->tahun) ? (int)$request->tahun : (int)$maxTahun;
            $cacheKey = 'calc_ss_' . md5(implode('_', $authorizedIds) . '_' . $selectedTahun);

            $mappedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($authorizedKabupatens, $selectedTahun) {
                $mappedRows = [];
                $idCounter = 1;

                foreach ($authorizedKabupatens as $kab) {
                    $dynamicSsa = $this->ssaService->calculateSsa($kab->kab_id, $selectedTahun);
                    foreach ($dynamicSsa as $item) {
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
                            'pdrb_sektor_pembanding_awal' => 0,
                            'pdrb_sektor_pembanding_akhir' => 0,
                            'total_pdrb_pembanding_awal' => 0,
                            'total_pdrb_pembanding_akhir' => 0,
                            'rij' => number_format($item['rij'], 4, '.', ''),
                            'rin' => number_format($item['rin'], 4, '.', ''),
                            'rn' => number_format($item['rn'], 4, '.', ''),
                            'nij' => $item['nij'],
                            'mij' => $item['mij'],
                            'cij' => $item['cij'],
                            'dij' => $item['dij'],
                            'status_pertumbuhan' => $item['kategori_pertumbuhan'],
                            'status_daya_saing' => $item['kategori_daya_saing'],
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

        return view('operator.potensi_unggulan.ss.index', [
            'ssData' => $paginatedData,
            'editItem' => $editItem,
            'editData' => $editItem,
        ]);
    }

    private function calculateSSData(Request $request)
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
            'pdrb_sektor_pembanding_awal' => 'required',
            'pdrb_sektor_pembanding_akhir' => 'required',
            'total_pdrb_pembanding_awal' => 'required',
            'total_pdrb_pembanding_akhir' => 'required',
        ]);

        $daerah_analisis = $request->tingkat_wilayah === 'Provinsi' ? $request->provinsi : $request->kabupaten;
        $daerah_pembanding = $request->tingkat_wilayah === 'Provinsi' ? 'Nasional' : $request->provinsi;

        $yij_awal = $this->parseNumber($request->pdrb_sektor_analisis_awal);
        $yij_akhir = $this->parseNumber($request->pdrb_sektor_analisis_akhir);
        $yin_awal = $this->parseNumber($request->pdrb_sektor_pembanding_awal);
        $yin_akhir = $this->parseNumber($request->pdrb_sektor_pembanding_akhir);
        $yn_awal = $this->parseNumber($request->total_pdrb_pembanding_awal);
        $yn_akhir = $this->parseNumber($request->total_pdrb_pembanding_akhir);

        $rij = $yij_awal > 0 ? ($yij_akhir - $yij_awal) / $yij_awal : 0;
        $rin = $yin_awal > 0 ? ($yin_akhir - $yin_awal) / $yin_awal : 0;
        $rn = $yn_awal > 0 ? ($yn_akhir - $yn_awal) / $yn_awal : 0;

        $nij = round($yij_awal * $rn, 2);
        $mij = round($yij_awal * ($rin - $rn), 2);
        $cij = round($yij_awal * ($rij - $rin), 2);
        $dij = round($nij + $mij + $cij, 2);

        $status_pertumbuhan = $mij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat';
        $status_daya_saing = $cij >= 0 ? 'Daya Saing Tinggi (Kompetitif)' : 'Daya Saing Rendah';

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
            'pdrb_sektor_pembanding_awal' => $yin_awal,
            'pdrb_sektor_pembanding_akhir' => $yin_akhir,
            'total_pdrb_pembanding_awal' => $yn_awal,
            'total_pdrb_pembanding_akhir' => $yn_akhir,
            'rij' => $rij,
            'rin' => $rin,
            'rn' => $rn,
            'nij' => $nij,
            'mij' => $mij,
            'cij' => $cij,
            'dij' => $dij,
            'status_pertumbuhan' => $status_pertumbuhan,
            'status_daya_saing' => $status_daya_saing,
        ];
    }

    public function store(Request $request)
    {
        $newData = $this->calculateSSData($request);

        if (!$newData) {
            return back()->with('error', 'Semua form PDRB wajib diisi dengan angka valid!');
        }

        AnalysisResult::create([
            'type' => 'shift_share',
            'title' => "Simulasi Shift-Share - {$newData['daerah_analisis']} ({$newData['tahun_awal']}-{$newData['tahun_akhir']})",
            'description' => "Perhitungan Shift Share Sektor {$newData['sektor']} untuk {$newData['daerah_analisis']}",
            'results' => $newData,
        ]);

        OperatorController::logActivity('Analisis SS', 'ditambah', "Menambah data Shift Share {$newData['daerah_analisis']}");
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('operator.ss.index')->with('success', 'Data perhitungan Shift-Share berhasil disimpan secara permanen!');
    }

    public function update(Request $request, $id)
    {
        $res = AnalysisResult::where('type', 'shift_share')->find($id);
        if (!$res) {
            return redirect()->route('operator.ss.index')->with('error', 'Data tidak ditemukan!');
        }

        $updatedData = $this->calculateSSData($request);

        if (!$updatedData) {
            return back()->with('error', 'Semua form PDRB wajib diisi dengan angka valid!');
        }

        $res->update([
            'title' => "Simulasi Shift-Share - {$updatedData['daerah_analisis']} ({$updatedData['tahun_awal']}-{$updatedData['tahun_akhir']})",
            'description' => "Perhitungan Shift Share Sektor {$updatedData['sektor']} untuk {$updatedData['daerah_analisis']}",
            'results' => $updatedData,
        ]);

        OperatorController::logActivity('Analisis SS', 'diubah', "Mengubah data Shift Share {$updatedData['daerah_analisis']}");
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('operator.ss.index')->with('success', 'Data perhitungan Shift-Share berhasil diperbarui secara permanen!');
    }

    public function destroy($id)
    {
        $res = AnalysisResult::where('type', 'shift_share')->find($id);

        if ($res) {
            $daerah = $res->results['daerah_analisis'] ?? 'Daerah';
            $res->delete();
            OperatorController::logActivity('Analisis SS', 'dihapus', "Menghapus data Shift Share {$daerah}");
            \Illuminate\Support\Facades\Cache::flush();
        }

        return back()->with('success', 'Data perhitungan Shift-Share berhasil dihapus secara permanen!');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'shift_share')->delete();
        OperatorController::logActivity('Analisis SS', 'dihapus', "Menghapus semua data Shift Share");
        \Illuminate\Support\Facades\Cache::flush();
        return back()->with('success', 'Semua data perhitungan Shift-Share berhasil dihapus secara permanen!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (!empty($ids)) {
            $count = count($ids);
            AnalysisResult::where('type', 'shift_share')->whereIn('id', $ids)->delete();
            OperatorController::logActivity('Analisis SS', 'dihapus', "Menghapus {$count} data Shift Share secara massal");
            \Illuminate\Support\Facades\Cache::flush();
            return back()->with('success', "{$count} data Shift Share berhasil dihapus secara massal!");
        }
        return back()->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
    }
}
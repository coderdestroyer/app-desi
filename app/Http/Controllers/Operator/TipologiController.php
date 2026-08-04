<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Sektor;
use App\Services\TipologiSektorService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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

        $savedResults = AnalysisResult::where('type', 'tipologi_sektor')->orderBy('id', 'desc')->get();

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
                    'tahun' => $res['tahun'] ?? '-',
                    'nilai_ss' => $res['nilai_ss'] ?? 0,
                    'nilai_lq' => $res['nilai_lq'] ?? 0,
                    'tipologi' => $res['tipologi'] ?? '-',
                    'riwayat' => 'Diperbarui ' . $item->updated_at->format('d-m-Y'),
                ];
            });
        } else {
            $maxTahun = DB::table('pdrb_sumatera_kabupaten')->whereIn('kabupaten_id', $authorizedIds)->max('tahun') ?? 2024;
            $selectedTahun = $request->has('tahun') && !empty($request->tahun) ? (int)$request->tahun : (int)$maxTahun;
            $cacheKey = 'calc_tipologi_' . md5(implode('_', $authorizedIds) . '_' . $selectedTahun);

            $mappedData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 86400, function () use ($authorizedKabupatens, $selectedTahun) {
                $mappedRows = [];
                $idCounter = 1;

                foreach ($authorizedKabupatens as $kab) {
                    $dynamicTipologi = $this->tipologiSektorService->calculateTipologi($kab->kab_id, $selectedTahun);
                    foreach ($dynamicTipologi as $item) {
                        $mappedRows[] = [
                            'id' => $idCounter++,
                            'tingkat_wilayah' => 'Kabupaten/Kota',
                            'daerah_analisis' => strtoupper($kab->nama_kabupaten),
                            'daerah_pembanding' => 'SUMATERA UTARA',
                            'provinsi' => 'SUMATERA UTARA',
                            'kabupaten' => strtoupper($kab->nama_kabupaten),
                            'sektor' => $item['sektor']->nama_sektor ?? '-',
                            'tahun' => $item['tahun'],
                            'nilai_ss' => $item['cij'],
                            'nilai_lq' => $item['lq'],
                            'tipologi' => "{$item['kuadran']} ({$item['kategori_sektor']})",
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
        ))->onEachSide(1);

        return view('operator.potensi_unggulan.tipologi.index', [
            'tipologiData' => $paginatedData,
            'editItem' => $editItem,
            'editData' => $editItem,
        ]);
    }

    private function calculateTipologiData(Request $request)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'sektor' => 'required|string',
            'tahun' => 'required|numeric',
            'nilai_lq' => 'required',
            'nilai_ss' => 'required',
        ]);

        $daerah_analisis = $request->tingkat_wilayah === 'Provinsi' ? $request->provinsi : $request->kabupaten;
        $daerah_pembanding = $request->tingkat_wilayah === 'Provinsi' ? 'Nasional' : $request->provinsi;

        $lq = $this->parseNumber($request->nilai_lq);
        $ss = $this->parseNumber($request->nilai_ss);

        if ($lq >= 1 && $ss >= 0) {
            $tipologi = 'Kuadran I (Maju dan Tumbuh Cepat)';
        } elseif ($lq < 1 && $ss >= 0) {
            $tipologi = 'Kuadran II (Potensial / Cepat Berkembang)';
        } elseif ($lq >= 1 && $ss < 0) {
            $tipologi = 'Kuadran III (Maju tapi Tertekan)';
        } else {
            $tipologi = 'Kuadran IV (Relatif Tertinggal)';
        }

        return [
            'tingkat_wilayah' => $request->tingkat_wilayah,
            'provinsi' => $request->provinsi,
            'kabupaten' => $request->tingkat_wilayah === 'Provinsi' ? '-' : ($request->kabupaten ?? '-'),
            'daerah_analisis' => $daerah_analisis,
            'daerah_pembanding' => $daerah_pembanding,
            'sektor' => $request->sektor,
            'tahun' => $request->tahun,
            'nilai_lq' => $lq,
            'nilai_ss' => $ss,
            'tipologi' => $tipologi,
        ];
    }

    public function store(Request $request)
    {
        $newData = $this->calculateTipologiData($request);

        if (!$newData) {
            return back()->with('error', 'Form wajib diisi dengan nilai valid!');
        }

        AnalysisResult::create([
            'type' => 'tipologi_sektor',
            'title' => "Simulasi Tipologi - {$newData['daerah_analisis']} ({$newData['tahun']})",
            'description' => "Analisis Tipologi Sektor {$newData['sektor']} untuk {$newData['daerah_analisis']}",
            'results' => $newData,
        ]);

        OperatorController::logActivity('Analisis Tipologi', 'ditambah', "Menambah data Tipologi Sektor {$newData['daerah_analisis']}");
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('operator.tipologi.index')->with('success', 'Data perhitungan Tipologi Sektor berhasil disimpan secara permanen!');
    }

    public function update(Request $request, $id)
    {
        $res = AnalysisResult::where('type', 'tipologi_sektor')->find($id);
        if (!$res) {
            return redirect()->route('operator.tipologi.index')->with('error', 'Data tidak ditemukan!');
        }

        $updatedData = $this->calculateTipologiData($request);

        if (!$updatedData) {
            return back()->with('error', 'Form wajib diisi dengan nilai valid!');
        }

        $res->update([
            'title' => "Simulasi Tipologi - {$updatedData['daerah_analisis']} ({$updatedData['tahun']})",
            'description' => "Analisis Tipologi Sektor {$updatedData['sektor']} untuk {$updatedData['daerah_analisis']}",
            'results' => $updatedData,
        ]);

        OperatorController::logActivity('Analisis Tipologi', 'diubah', "Mengubah data Tipologi Sektor {$updatedData['daerah_analisis']}");
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('operator.tipologi.index')->with('success', 'Data perhitungan Tipologi Sektor berhasil diperbarui secara permanen!');
    }

    public function destroy($id)
    {
        $res = AnalysisResult::where('type', 'tipologi_sektor')->find($id);

        if ($res) {
            $daerah = $res->results['daerah_analisis'] ?? 'Daerah';
            $res->delete();
            OperatorController::logActivity('Analisis Tipologi', 'dihapus', "Menghapus data Tipologi Sektor {$daerah}");
            \Illuminate\Support\Facades\Cache::flush();
        }

        return back()->with('success', 'Data perhitungan Tipologi Sektor berhasil dihapus secara permanen!');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'tipologi_sektor')->delete();
        OperatorController::logActivity('Analisis Tipologi', 'dihapus', "Menghapus semua data Tipologi Sektor");
        \Illuminate\Support\Facades\Cache::flush();
        return back()->with('success', 'Semua data perhitungan Tipologi Sektor berhasil dihapus secara permanen!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (!empty($ids)) {
            $count = count($ids);
            AnalysisResult::where('type', 'tipologi_sektor')->whereIn('id', $ids)->delete();
            OperatorController::logActivity('Analisis Tipologi', 'dihapus', "Menghapus {$count} data Tipologi Sektor secara massal");
            \Illuminate\Support\Facades\Cache::flush();
            return back()->with('success', "{$count} data Tipologi Sektor berhasil dihapus secara massal!");
        }
        return back()->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
    }
}

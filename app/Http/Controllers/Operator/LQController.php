<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\AnalysisResult;
use App\Models\Kabupaten;
use App\Models\Sektor;
use App\Services\LqService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LqController extends Controller
{
    protected LqService $lqService;

    public function __construct(LqService $lqService)
    {
        $this->lqService = $lqService;
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

        $savedResults = AnalysisResult::where('type', 'lq')->orderBy('id', 'desc')->get();

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
                    'pdrb_sektor_analisis' => $res['pdrb_sektor_analisis'] ?? 0,
                    'total_pdrb_analisis' => $res['total_pdrb_analisis'] ?? 0,
                    'pdrb_sektor_pembanding' => $res['pdrb_sektor_pembanding'] ?? 0,
                    'total_pdrb_pembanding' => $res['total_pdrb_pembanding'] ?? 0,
                    'nilai_lq' => $res['nilai_lq'] ?? 0,
                    'keterangan' => $res['keterangan'] ?? '-',
                    'kategori' => $res['kategori'] ?? '-',
                    'riwayat' => 'Diperbarui ' . $item->updated_at->format('d-m-Y'),
                ];
            });
        } else {
            // Default ke Tahun Terbaru untuk performa kilat (< 50ms)
            $maxTahun = DB::table('pdrb_sumatera_kabupaten')->whereIn('kabupaten_id', $authorizedIds)->max('tahun') ?? 2024;
            $selectedTahun = $request->has('tahun') && !empty($request->tahun) ? (int)$request->tahun : (int)$maxTahun;

            $mappedRows = [];
            $idCounter = 1;

            foreach ($authorizedKabupatens as $kab) {
                $dynamicLq = $this->lqService->calculateLq($kab->kab_id, $selectedTahun);
                foreach ($dynamicLq as $item) {
                    $mappedRows[] = [
                        'id' => $idCounter++,
                        'tingkat_wilayah' => 'Kabupaten/Kota',
                        'daerah_analisis' => strtoupper($kab->nama_kabupaten),
                        'daerah_pembanding' => 'SUMATERA UTARA',
                        'provinsi' => 'SUMATERA UTARA',
                        'kabupaten' => strtoupper($kab->nama_kabupaten),
                        'sektor' => $item['sektor']->nama_sektor ?? '-',
                        'tahun' => $item['tahun'],
                        'pdrb_sektor_analisis' => $item['persen_kabupaten'],
                        'total_pdrb_analisis' => 100,
                        'pdrb_sektor_pembanding' => $item['persen_provinsi'],
                        'total_pdrb_pembanding' => 100,
                        'nilai_lq' => $item['nilai_lq'],
                        'keterangan' => $item['kategori'] === 'Basis'
                            ? 'Sektor Unggulan (LQ >= 1). Peranannya di daerah lebih dominan dibanding rata-rata acuan.'
                            : 'Sektor Non-Unggulan (LQ < 1). Peranannya lebih rendah dibanding rata-rata acuan.',
                        'kategori' => strtoupper($item['kategori']),
                        'riwayat' => 'Kalkulasi Otomatis',
                    ];
                }
            }

            $mappedData = collect($mappedRows);
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

        return view('operator.potensi_unggulan.lq.index', [
            'lqData' => $paginatedData,
            'editItem' => $editItem,
            'editData' => $editItem,
        ]);
    }

    private function calculateLQData(Request $request)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'sektor' => 'required|string',
            'tahun' => 'required|numeric',
            'pdrb_sektor_analisis' => 'required',
            'total_pdrb_analisis' => 'required',
            'pdrb_sektor_pembanding' => 'required',
            'total_pdrb_pembanding' => 'required',
        ]);

        $daerah_analisis = $request->tingkat_wilayah === 'Provinsi' ? $request->provinsi : $request->kabupaten;
        $daerah_pembanding = $request->tingkat_wilayah === 'Provinsi' ? 'Nasional' : $request->provinsi;

        $xij = $this->parseNumber($request->pdrb_sektor_analisis);
        $rvj = $this->parseNumber($request->total_pdrb_analisis);
        $xi = $this->parseNumber($request->pdrb_sektor_pembanding);
        $rvi = $this->parseNumber($request->total_pdrb_pembanding);

        if ($rvj <= 0 || $rvi <= 0) {
            return null;
        }

        $pembagi = $xi / $rvi;
        
        if ($pembagi > 0) {
            $lq = round(($xij / $rvj) / $pembagi, 2);
        } else {
            $lq = $xij > 0 ? 99.99 : 0; 
        }

        if ($lq > 1) {
            $kategori = 'BASIS';
            $keterangan = 'Menunjukkan bahwa indikator dengan LQ > 1, yaitu sektor unggulan (surplus). Peranannya di daerah lebih dominan dibanding rata-rata referensi, sehingga berpotensi besar untuk diekspor.';
        } elseif ($lq < 1) {
            $kategori = 'NON-BASIS';
            $keterangan = 'Menunjukkan bahwa indikator dengan LQ < 1, yaitu sektor non-unggulan (defisit). Belum mampu memenuhi kebutuhan daerah karena peranannya lebih rendah dari referensi, sehingga memerlukan impor.';
        } else {
            $kategori = 'SEIMBANG';
            $keterangan = 'Menunjukkan bahwa indikator dengan LQ = 1, yaitu sektor berimbang. Produktivitas setara dengan referensi. Mampu memenuhi kebutuhan daerah sendiri namun belum layak diekspor.';
        }

        return [
            'tingkat_wilayah' => $request->tingkat_wilayah,
            'provinsi' => $request->provinsi,
            'kabupaten' => $request->tingkat_wilayah === 'Provinsi' ? '-' : ($request->kabupaten ?? '-'),
            'daerah_analisis' => $daerah_analisis,
            'daerah_pembanding' => $daerah_pembanding,
            'sektor' => $request->sektor,
            'tahun' => $request->tahun,
            'pdrb_sektor_analisis' => $xij,
            'total_pdrb_analisis' => $rvj,
            'pdrb_sektor_pembanding' => $xi,
            'total_pdrb_pembanding' => $rvi,
            'nilai_lq' => $lq,
            'keterangan' => $keterangan,
            'kategori' => $kategori,
        ];
    }

    public function store(Request $request)
    {
        $newData = $this->calculateLQData($request);

        if (!$newData) {
            return back()->with('error', 'Semua form PDRB wajib diisi dengan angka valid!');
        }

        AnalysisResult::create([
            'type' => 'lq',
            'title' => "Simulasi LQ - {$newData['daerah_analisis']} ({$newData['tahun']})",
            'description' => "Perhitungan LQ Sektor {$newData['sektor']} untuk {$newData['daerah_analisis']}",
            'results' => $newData,
        ]);

        OperatorController::logActivity('Analisis LQ', 'ditambah', "Menambah data perhitungan LQ untuk daerah {$newData['daerah_analisis']} tahun {$newData['tahun']}");

        return redirect()->route('operator.lq.index')->with('success', 'Data perhitungan LQ berhasil disimpan secara permanen!');
    }

    public function update(Request $request, $id)
    {
        $res = AnalysisResult::where('type', 'lq')->find($id);
        if (!$res) {
            return redirect()->route('operator.lq.index')->with('error', 'Data tidak ditemukan!');
        }

        $updatedData = $this->calculateLQData($request);

        if (!$updatedData) {
            return back()->with('error', 'Semua form PDRB wajib diisi dengan angka valid!');
        }

        $res->update([
            'title' => "Simulasi LQ - {$updatedData['daerah_analisis']} ({$updatedData['tahun']})",
            'description' => "Perhitungan LQ Sektor {$updatedData['sektor']} untuk {$updatedData['daerah_analisis']}",
            'results' => $updatedData,
        ]);

        OperatorController::logActivity('Analisis LQ', 'diubah', "Mengubah data perhitungan LQ daerah {$updatedData['daerah_analisis']}");

        return redirect()->route('operator.lq.index')->with('success', 'Data perhitungan LQ berhasil diperbarui secara permanen!');
    }

    public function destroy($id)
    {
        $res = AnalysisResult::where('type', 'lq')->find($id);
        
        if ($res) {
            $daerah = $res->results['daerah_analisis'] ?? 'Daerah';
            $res->delete();
            OperatorController::logActivity('Analisis LQ', 'dihapus', "Menghapus data perhitungan LQ daerah {$daerah}");
        }

        return back()->with('success', 'Data perhitungan LQ berhasil dihapus secara permanen!');
    }

    public function empty()
    {
        AnalysisResult::where('type', 'lq')->delete();
        OperatorController::logActivity('Analisis LQ', 'dihapus', "Menghapus semua data perhitungan LQ");
        return back()->with('success', 'Semua data perhitungan LQ berhasil dihapus secara permanen!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (!empty($ids)) {
            $count = count($ids);
            AnalysisResult::where('type', 'lq')->whereIn('id', $ids)->delete();
            OperatorController::logActivity('Analisis LQ', 'dihapus', "Menghapus {$count} data perhitungan LQ secara massal");
            return back()->with('success', "{$count} data perhitungan LQ berhasil dihapus secara massal!");
        }
        return back()->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
    }
}

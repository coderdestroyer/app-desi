<?php

// Controller untuk mengelola analisis Tipologi Sektor bagi Operator
namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Tipologi;
use App\Models\Sektor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TipologiController extends Controller
{
    private function mapDbToView($items)
    {
        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'tingkat_wilayah' => $item->tingkat_wilayah,
                'daerah_analisis' => $item->daerah_analisis,
                'daerah_pembanding' => $item->daerah_pembanding,
                'provinsi' => $item->tingkat_wilayah === 'Provinsi' ? $item->daerah_analisis : $item->daerah_pembanding,
                'kabupaten' => $item->tingkat_wilayah === 'Provinsi' ? '' : $item->daerah_analisis,
                'sektor' => $item->sektor->nama_sektor ?? '-',
                'tahun' => $item->tahun_akhir, // Hanya menggunakan tahun akhir
                'nilai_ss' => $item->nilai_ss,
                'nilai_lq' => $item->nilai_lq,
                'tipologi' => $item->tipologi,
                'riwayat' => 'Diperbarui ' . $item->updated_at->format('d-m-Y'),
            ];
        })->toArray();
    }

    public function index(Request $request)
    {
        $query = Tipologi::with('sektor');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('daerah_analisis', 'like', "%{$search}%")
                    ->orWhere('daerah_pembanding', 'like', "%{$search}%")
                    ->orWhereHas('sektor', function ($qSektor) use ($search) {
                        $qSektor->where('nama_sektor', 'like', "%{$search}%");
                    });
            });
        }

        $rawDbData = $query->orderBy('created_at', 'desc')->orderBy('id', 'asc')->get();
        $tipologiData = collect($this->mapDbToView($rawDbData));

        $editItem = null;
        if ($request->has('edit')) {
            $editItem = $tipologiData->firstWhere('id', $request->edit);
        }

        $perPage = 10;
        $page = $request->get('page', 1);
        $paginatedData = (new LengthAwarePaginator(
            $tipologiData->forPage($page, $perPage),
            $tipologiData->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        ))->onEachSide(1);

        return view('operator.potensi_unggulan.tipologi.index', [
            'tipologiData' => $paginatedData,
            'editItem' => $editItem,
        ]);
    }

    // Fungsi bantu untuk parsing string angka dari Excel
    protected function parseExcelNumber($val)
    {
        if (is_numeric($val))
            return (float) $val;

        $val = trim((string) $val);
        $commaCount = substr_count($val, ',');
        $dotCount = substr_count($val, '.');

        if ($dotCount > 0 && $commaCount > 0) {
            $lastComma = strrpos($val, ',');
            $lastDot = strrpos($val, '.');
            if ($lastComma > $lastDot) {
                // Format Indonesia: 1.234.567,89
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } else {
                // Format US: 1,234,567.89
                $val = str_replace(',', '', $val);
            }
        } elseif ($dotCount > 1) {
            // Format Indonesia: 1.234.567
            $val = str_replace('.', '', $val);
        } elseif ($commaCount > 1) {
            // Format US: 1,234,567
            $val = str_replace(',', '', $val);
        } elseif ($commaCount == 1) {
            // Format Indonesia: 1,23 (koma sebagai desimal)
            $val = str_replace(',', '.', $val);
        }

        $val = preg_replace('/[^0-9\.\-]/', '', $val);
        return (float) $val;
    }

    // Menghitung Tipologi Sektor berdasarkan LQ dan SS.
    private function calculateTipologiData($item)
    {
        $dij = $this->parseExcelNumber($item['nilai_ss'] ?? 0);
        $lq = $this->parseExcelNumber($item['nilai_lq'] ?? 0);

        // 3. MENENTUKAN TIPOLOGI SEKTOR
        if ($lq >= 1 && $dij > 0) {
            $tipologi = 'I'; // Kuadran I: Sektor Cepat Maju dan Cepat Tumbuh
        } elseif ($lq < 1 && $dij > 0) {
            $tipologi = 'II'; // Kuadran II: Sektor Potensial
        } elseif ($lq >= 1 && $dij <= 0) {
            $tipologi = 'III'; // Kuadran III: Sektor Maju tetapi Tertekan
        } else {
            $tipologi = 'IV'; // Kuadran IV: Sektor Relatif Tertinggal
        }

        $daerah_analisis = ($item['tingkat_wilayah'] ?? 'Kabupaten/Kota') === 'Provinsi' ? $item['provinsi'] : $item['kabupaten'];
        $daerah_pembanding = ($item['tingkat_wilayah'] ?? 'Kabupaten/Kota') === 'Provinsi' ? 'Nasional' : $item['provinsi'];

        return [
            'tingkat_wilayah' => $item['tingkat_wilayah'] ?? 'Kabupaten/Kota',
            'provinsi' => $item['provinsi'],
            'kabupaten' => ($item['tingkat_wilayah'] ?? 'Kabupaten/Kota') === 'Provinsi' ? '-' : ($item['kabupaten'] ?? '-'),
            'daerah_analisis' => $daerah_analisis,
            'daerah_pembanding' => $daerah_pembanding,
            'sektor' => $item['sektor'],
            'tahun_awal' => $item['tahun_awal'],
            'tahun_akhir' => $item['tahun_akhir'],

            // Kolom PDRB diset 0 karena tidak digunakan lagi
            'pdrb_sektor_analisis_awal' => 0,
            'pdrb_sektor_analisis_akhir' => 0,
            'total_pdrb_analisis_awal' => 0,
            'total_pdrb_analisis_akhir' => 0,
            'pdrb_sektor_pembanding_awal' => 0,
            'pdrb_sektor_pembanding_akhir' => 0,
            'total_pdrb_pembanding_awal' => 0,
            'total_pdrb_pembanding_akhir' => 0,

            'nilai_ss' => $dij,
            'nilai_lq' => $lq,
            'tipologi' => $tipologi,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'sektor' => 'required|string',
            'tahun_awal' => 'required|numeric',
            'tahun_akhir' => 'required|numeric',
            'nilai_ss' => 'required',
            'nilai_lq' => 'required',
        ]);

        $data = $this->calculateTipologiData($request->all());
        if (!$data) {
            return back()->with('error', 'Terjadi kesalahan perhitungan.');
        }

        $sektorModel = Sektor::firstOrCreate(['nama_sektor' => $data['sektor']]);

        Tipologi::create([
            'user_id' => Auth::id() ?? 1,
            'sektor_id' => $sektorModel->sektor_id,
            'tingkat_wilayah' => $data['tingkat_wilayah'],
            'daerah_analisis' => $data['daerah_analisis'],
            'daerah_pembanding' => $data['daerah_pembanding'],
            'tahun_awal' => $data['tahun_awal'],
            'tahun_akhir' => $data['tahun_akhir'],
            'pdrb_sektor_analisis_awal' => $data['pdrb_sektor_analisis_awal'],
            'pdrb_sektor_analisis_akhir' => $data['pdrb_sektor_analisis_akhir'],
            'total_pdrb_analisis_awal' => $data['total_pdrb_analisis_awal'],
            'total_pdrb_analisis_akhir' => $data['total_pdrb_analisis_akhir'],
            'pdrb_sektor_pembanding_awal' => $data['pdrb_sektor_pembanding_awal'],
            'pdrb_sektor_pembanding_akhir' => $data['pdrb_sektor_pembanding_akhir'],
            'total_pdrb_pembanding_awal' => $data['total_pdrb_pembanding_awal'],
            'total_pdrb_pembanding_akhir' => $data['total_pdrb_pembanding_akhir'],
            'nilai_ss' => $data['nilai_ss'],
            'nilai_lq' => $data['nilai_lq'],
            'tipologi' => $data['tipologi']
        ]);

        OperatorController::logActivity('Analisis Tipologi', 'ditambah', "Menambahkan data perhitungan Analisis Tipologi Sektor untuk sektor {$data['sektor']}.");

        return back()->with('success', 'Perhitungan Tipologi Sektor berhasil disimpan secara permanen!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'sektor' => 'required|string',
            'tahun_awal' => 'required|numeric',
            'tahun_akhir' => 'required|numeric',
            'nilai_ss' => 'required',
            'nilai_lq' => 'required',
        ]);

        $tipologi = Tipologi::find($id);
        if (!$tipologi) {
            return redirect()->route('operator.tipologi.index')->with('error', 'Data tidak ditemukan!');
        }

        $data = $this->calculateTipologiData($request->all());
        if (!$data) {
            return back()->with('error', 'Terjadi kesalahan perhitungan.');
        }

        $sektorModel = Sektor::firstOrCreate(['nama_sektor' => $data['sektor']]);

        $tipologi->update([
            'sektor_id' => $sektorModel->sektor_id,
            'tingkat_wilayah' => $data['tingkat_wilayah'],
            'daerah_analisis' => $data['daerah_analisis'],
            'daerah_pembanding' => $data['daerah_pembanding'],
            'tahun_awal' => $data['tahun_awal'],
            'tahun_akhir' => $data['tahun_akhir'],
            'pdrb_sektor_analisis_awal' => 0,
            'pdrb_sektor_analisis_akhir' => 0,
            'total_pdrb_analisis_awal' => 0,
            'total_pdrb_analisis_akhir' => 0,
            'pdrb_sektor_pembanding_awal' => 0,
            'pdrb_sektor_pembanding_akhir' => 0,
            'total_pdrb_pembanding_awal' => 0,
            'total_pdrb_pembanding_akhir' => 0,
            'nilai_ss' => $data['nilai_ss'],
            'nilai_lq' => $data['nilai_lq'],
            'tipologi' => $data['tipologi']
        ]);

        OperatorController::logActivity('Analisis Tipologi', 'diperbarui', "Memperbarui data perhitungan Analisis Tipologi Sektor untuk sektor {$data['sektor']}.");

        return redirect()->route('operator.tipologi.index')->with('success', 'Data perhitungan Tipologi Sektor berhasil diperbarui secara permanen!');
    }

    public function destroy($id)
    {
        $tipologi = Tipologi::find($id);
        if ($tipologi) {
            $daerah = $tipologi->daerah_analisis;
            $tipologi->delete();
            OperatorController::logActivity('Tipologi Sektor', 'dihapus', "Menghapus data Tipologi daerah {$daerah}.");
        }

        return back()->with('success', 'Data Tipologi berhasil dihapus secara permanen!');
    }

    public function empty()
    {
        Tipologi::truncate();
        OperatorController::logActivity('Analisis Tipologi Sektor', 'dihapus', "Menghapus semua data perhitungan Tipologi Sektor");
        return back()->with('success', 'Semua data perhitungan Tipologi Sektor berhasil dihapus secara permanen!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (!empty($ids)) {
            $count = count($ids);
            Tipologi::whereIn('id', $ids)->delete();
            OperatorController::logActivity('Analisis Tipologi Sektor', 'dihapus', "Menghapus {$count} data perhitungan Tipologi Sektor secara massal");
            return back()->with('success', "{$count} data perhitungan Tipologi Sektor berhasil dihapus secara massal!");
        }
        return back()->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
    }

    public function import(Request $request)
    {
        set_time_limit(300);
        $payload = $request->json()->all();
        if (!$payload || !is_array($payload)) {
            return response()->json(['success' => false, 'message' => 'Format data tidak valid.']);
        }

        $successCount = 0;

        // Preload all sectors in memory
        $sektorsCache = Sektor::all()->pluck('sektor_id', 'nama_sektor')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])->toArray();

        \Illuminate\Support\Facades\DB::transaction(function () use ($payload, &$successCount, &$sektorsCache) {
            foreach ($payload as $rawItem) {
                // Normalize keys (lowercase and remove spaces)
                $item = [];
                foreach ($rawItem as $key => $val) {
                    $normalizedKey = str_replace([' ', '_'], '', strtolower(trim($key)));
                    $item[$normalizedKey] = $val;
                }

                $hasProvinsi = isset($item['provinsi']) || isset($item['kodeprovinsi']) || isset($item['kodewilayah']);

                // Also accept 'sektor' and 'tahun_awal', 'tahun_akhir', 'nilailq', 'nilaiss'
                if (!$hasProvinsi || !isset($item['sektor']) || !isset($item['tahunawal']) || !isset($item['tahunakhir']) || !isset($item['nilailq']) || !isset($item['nilaiss'])) {
                    continue;
                }

                $resolved = $this->resolveRegionNames($rawItem); // Use rawItem for resolving because it expects the original keys
                $provinsi = $resolved['provinsi'];
                $kabupaten = $resolved['kabupaten'];

                $tingkat = ($kabupaten != '-' && $kabupaten != '') ? 'Kabupaten/Kota' : 'Provinsi';

                $sektorName = $this->resolveSektorName($rawItem);
                $sektorKey = strtolower(trim($sektorName));

                if (isset($sektorsCache[$sektorKey])) {
                    $sektorId = $sektorsCache[$sektorKey];
                } else {
                    $sektorModel = Sektor::create(['nama_sektor' => $sektorName]);
                    $sektorsCache[$sektorKey] = $sektorModel->sektor_id;
                    $sektorId = $sektorModel->sektor_id;
                }

                $mappedItem = [
                    'tingkat_wilayah' => $tingkat,
                    'provinsi' => $provinsi,
                    'kabupaten' => $kabupaten,
                    'sektor' => $sektorName,
                    'tahun_awal' => $item['tahunawal'],
                    'tahun_akhir' => $item['tahunakhir'],
                    'nilai_lq' => $item['nilailq'],
                    'nilai_ss' => $item['nilaiss'],
                ];

                $newData = $this->calculateTipologiData($mappedItem);

                if ($newData) {
                    Tipologi::create([
                        'user_id' => Auth::id() ?? 1,
                        'sektor_id' => $sektorId,
                        'tingkat_wilayah' => $newData['tingkat_wilayah'],
                        'daerah_analisis' => $newData['daerah_analisis'],
                        'daerah_pembanding' => $newData['daerah_pembanding'],
                        'tahun_awal' => $newData['tahun_awal'],
                        'tahun_akhir' => $newData['tahun_akhir'],
                        'pdrb_sektor_analisis_awal' => 0,
                        'pdrb_sektor_analisis_akhir' => 0,
                        'total_pdrb_analisis_awal' => 0,
                        'total_pdrb_analisis_akhir' => 0,
                        'pdrb_sektor_pembanding_awal' => 0,
                        'pdrb_sektor_pembanding_akhir' => 0,
                        'total_pdrb_pembanding_awal' => 0,
                        'total_pdrb_pembanding_akhir' => 0,
                        'nilai_ss' => $newData['nilai_ss'],
                        'nilai_lq' => $newData['nilai_lq'],
                        'tipologi' => $newData['tipologi']
                    ]);
                    $successCount++;
                }
            }
        });

        if ($successCount > 0) {
            OperatorController::logActivity('Analisis Tipologi', 'diimpor', "Mengimpor {$successCount} data Analisis Tipologi Sektor secara massal dari Template Master.");
            session()->flash('success', "Berhasil mengimpor $successCount data baru!");

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Tidak ada data valid yang dapat diimpor. Pastikan format kolom sesuai dengan Template Master.']);
    }

    public function syncFromDatabase(Request $request)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'tahun_awal' => 'required|numeric',
            'tahun_akhir' => 'required|numeric'
        ]);

        $daerah = $request->tingkat_wilayah === 'Provinsi' ? $request->provinsi : $request->kabupaten;
        $tahunAwal = $request->tahun_awal;
        $tahunAkhir = $request->tahun_akhir;

        $ssData = \App\Models\ShiftShare::where('daerah_analisis', $daerah)
            ->where('tahun_awal', $tahunAwal)
            ->where('tahun_akhir', $tahunAkhir)
            ->get();

        $lqData = \App\Models\LQ::where('daerah_analisis', $daerah)
            ->where('tahun', $tahunAkhir)
            ->get();

        if ($ssData->isEmpty() || $lqData->isEmpty()) {
            return back()->with('error', 'Data LQ atau Shift Share untuk daerah dan tahun tersebut tidak ditemukan di database.');
        }

        $successCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($ssData, $lqData, &$successCount) {
            foreach ($ssData as $ss) {
                // Find matching LQ
                $lq = $lqData->first(function ($item) use ($ss) {
                    return $item->sektor_id == $ss->sektor_id && $item->tahun == $ss->tahun_akhir;
                });

                if ($lq) {
                    $item = [
                        'tingkat_wilayah' => $ss->tingkat_wilayah,
                        'provinsi' => $ss->daerah_pembanding === 'Nasional' ? $ss->daerah_analisis : $ss->daerah_pembanding, // Aproksimasi
                        'kabupaten' => $ss->tingkat_wilayah === 'Kabupaten/Kota' ? $ss->daerah_analisis : '-',
                        'sektor' => $ss->sektor->nama_sektor,
                        'tahun_awal' => $ss->tahun_awal,
                        'tahun_akhir' => $ss->tahun_akhir,
                        'nilai_ss' => $ss->dij,
                        'nilai_lq' => $lq->nilai_lq
                    ];

                    $newData = $this->calculateTipologiData($item);
                    if ($newData) {
                        // Cek apakah sudah ada untuk menghindari duplikat
                        $existing = Tipologi::where('daerah_analisis', $newData['daerah_analisis'])
                            ->where('sektor_id', $ss->sektor_id)
                            ->where('tahun_akhir', $newData['tahun_akhir'])
                            ->first();

                        if (!$existing) {
                            Tipologi::create([
                                'user_id' => Auth::id() ?? 1,
                                'sektor_id' => $ss->sektor_id,
                                'tingkat_wilayah' => $newData['tingkat_wilayah'],
                                'daerah_analisis' => $newData['daerah_analisis'],
                                'daerah_pembanding' => $newData['daerah_pembanding'],
                                'tahun_awal' => $newData['tahun_awal'],
                                'tahun_akhir' => $newData['tahun_akhir'],
                                'pdrb_sektor_analisis_awal' => 0,
                                'pdrb_sektor_analisis_akhir' => 0,
                                'total_pdrb_analisis_awal' => 0,
                                'total_pdrb_analisis_akhir' => 0,
                                'pdrb_sektor_pembanding_awal' => 0,
                                'pdrb_sektor_pembanding_akhir' => 0,
                                'total_pdrb_pembanding_awal' => 0,
                                'total_pdrb_pembanding_akhir' => 0,
                                'nilai_ss' => $newData['nilai_ss'],
                                'nilai_lq' => $newData['nilai_lq'],
                                'tipologi' => $newData['tipologi']
                            ]);
                            $successCount++;
                        }
                    }
                }
            }
        });

        if ($successCount > 0) {
            OperatorController::logActivity('Analisis Tipologi', 'disinkronisasi', "Menarik {$successCount} data Tipologi secara otomatis dari modul LQ & SS.");
            return back()->with('success', "Berhasil menyinkronkan $successCount data Tipologi baru dari database LQ & SS!");
        }

        return back()->with('success', 'Sinkronisasi selesai. Tidak ada data baru yang ditambahkan (semua sudah tersinkron atau data tidak cocok).');
    }

    public function downloadPdf(Request $request)
    {
        $query = Tipologi::with('sektor');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('daerah_analisis', 'like', "%{$search}%")
                    ->orWhere('daerah_pembanding', 'like', "%{$search}%")
                    ->orWhereHas('sektor', function ($qSektor) use ($search) {
                        $qSektor->where('nama_sektor', 'like', "%{$search}%");
                    });
            });
        }

        $rawDbData = $query->orderBy('created_at', 'desc')->orderBy('id', 'asc')->get();
        $tipologiData = $this->mapDbToView($rawDbData);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('operator.tipologi.pdf', [
            'tipologiData' => $tipologiData,
            'search' => $request->search ?? null,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-analisis-tipologi-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadExcel(Request $request)
    {
        $query = Tipologi::with('sektor');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('daerah_analisis', 'like', "%{$search}%")
                    ->orWhere('daerah_pembanding', 'like', "%{$search}%")
                    ->orWhereHas('sektor', function ($qSektor) use ($search) {
                        $qSektor->where('nama_sektor', 'like', "%{$search}%");
                    });
            });
        }

        $rawDbData = $query->orderBy('created_at', 'desc')->orderBy('id', 'asc')->get();
        $tipologiData = $this->mapDbToView($rawDbData);

        $html = view('operator.tipologi.excel', [
            'tipologiData' => $tipologiData,
            'search' => $request->search ?? null,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="laporan-analisis-tipologi-' . now()->format('Y-m-d') . '.xls"')
            ->header('Cache-Control', 'max-age=0');
    }
}

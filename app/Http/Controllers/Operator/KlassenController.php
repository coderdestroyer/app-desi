<?php

// Controller untuk mengelola analisis Klassen Typology bagi Operator
namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Klassen;
use App\Models\Sektor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class KlassenController extends Controller
{
    private function mapDbToView($items)
    {
        return $items->map(function ($item) {
            $ri = (float) $item->ri;
            $r = (float) $item->r;
            $yi = (float) $item->yi;
            $y = (float) $item->y;

            if ($yi > $y && $ri > $r) {
                $kuadran = 'Kuadran I';
            } elseif ($yi > $y && $ri < $r) {
                $kuadran = 'Kuadran II';
            } elseif ($yi < $y && $ri > $r) {
                $kuadran = 'Kuadran III';
            } else {
                $kuadran = 'Kuadran IV';
            }

            return [
                'id' => $item->id,
                'tingkat_wilayah' => $item->tingkat_wilayah,
                'daerah_analisis' => $item->daerah_analisis,
                'daerah_pembanding' => $item->daerah_pembanding,
                'provinsi' => $item->tingkat_wilayah === 'Provinsi' ? $item->daerah_analisis : $item->daerah_pembanding,
                'kabupaten' => $item->tingkat_wilayah === 'Provinsi' ? '' : $item->daerah_analisis,
                'sektor' => $item->sektor->nama_sektor ?? '-',
                'tahun_awal' => $item->tahun_awal,
                'tahun_akhir' => $item->tahun_akhir,
                'pdrb_sektor_analisis_awal' => $item->pdrb_sektor_analisis_awal,
                'pdrb_sektor_analisis_akhir' => $item->pdrb_sektor_analisis_akhir,
                'total_pdrb_analisis_awal' => $item->total_pdrb_analisis_awal,
                'total_pdrb_analisis_akhir' => $item->total_pdrb_analisis_akhir,
                'pdrb_sektor_pembanding_awal' => $item->pdrb_sektor_pembanding_awal,
                'pdrb_sektor_pembanding_akhir' => $item->pdrb_sektor_pembanding_akhir,
                'total_pdrb_pembanding_awal' => $item->total_pdrb_pembanding_awal,
                'total_pdrb_pembanding_akhir' => $item->total_pdrb_pembanding_akhir,
                'ri' => number_format((float) $item->ri, 3, '.', ''),
                'r' => number_format((float) $item->r, 3, '.', ''),
                'yi' => number_format((float) $item->yi, 3, '.', ''),
                'y' => number_format((float) $item->y, 3, '.', ''),
                'kuadran' => $kuadran,
                'klasifikasi' => $item->klasifikasi,
                'riwayat' => 'Diperbarui ' . $item->updated_at->format('d-m-Y'),
            ];
        })->toArray();
    }

    public function index(Request $request)
    {
        $query = Klassen::with('sektor');

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
        $klassenData = collect($this->mapDbToView($rawDbData));

        $editData = null;
        if ($request->has('edit')) {
            $editData = $klassenData->firstWhere('id', $request->edit);
        }

        $perPage = 10;
        $page = $request->get('page', 1);
        $paginatedData = (new LengthAwarePaginator(
            $klassenData->forPage($page, $perPage),
            $klassenData->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        ))->onEachSide(1);

        return view('operator.potensi_unggulan.klassen.index', [
            'klassenData' => $paginatedData,
            'editData' => $editData,
        ]);
    }

    // Menghitung Tipologi Klassen berdasarkan laju pertumbuhan dan kontribusi sektor (Year-on-Year).
    private function calculateKlassenData($yearsData, $tingkatWilayah, $provinsi, $kabupaten, $sektor)
    {
        // Sort data by year ascending just to be safe
        usort($yearsData, function ($a, $b) {
            return (int) $a['tahun'] <=> (int) $b['tahun'];
        });

        if (count($yearsData) < 2)
            return false;

        $sumRi = 0;
        $sumR = 0;
        $sumYi = 0;
        $sumY = 0;

        $countGrowth = count($yearsData) - 1;
        $countContrib = count($yearsData);

        for ($i = 0; $i < $countContrib; $i++) {
            $curr = $yearsData[$i];

            $pdrbSektorKab = $this->parseNumber($curr['pdrb_sektor_analisis'] ?? 0);
            $pdrbTotalKab = $this->parseNumber($curr['total_pdrb_analisis'] ?? 0);
            $pdrbSektorProv = $this->parseNumber($curr['pdrb_sektor_pembanding'] ?? 0);
            $pdrbTotalProv = $this->parseNumber($curr['total_pdrb_pembanding'] ?? 0);

            // Contribution (Y)
            $yi = 0;
            if ($pdrbTotalKab > 0) {
                $yi = ($pdrbSektorKab / $pdrbTotalKab) * 100;
            }
            $sumYi += $yi;

            $y = 0;
            if ($pdrbTotalProv > 0) {
                $y = ($pdrbSektorProv / $pdrbTotalProv) * 100;
            }
            $sumY += $y;

            // Growth (r) - only from the second year onwards
            if ($i > 0) {
                $prev = $yearsData[$i - 1];
                $prevPdrbSektorKab = $this->parseNumber($prev['pdrb_sektor_analisis'] ?? 0);
                $prevPdrbSektorProv = $this->parseNumber($prev['pdrb_sektor_pembanding'] ?? 0);

                $ri = 0;
                if ($prevPdrbSektorKab > 0) {
                    $ri = (($pdrbSektorKab - $prevPdrbSektorKab) / $prevPdrbSektorKab) * 100;
                }
                $sumRi += $ri;

                $r = 0;
                if ($prevPdrbSektorProv > 0) {
                    $r = (($pdrbSektorProv - $prevPdrbSektorProv) / $prevPdrbSektorProv) * 100;
                }
                $sumR += $r;
            }
        }

        $avgRi = $countGrowth > 0 ? $sumRi / $countGrowth : 0;
        $avgR = $countGrowth > 0 ? $sumR / $countGrowth : 0;
        $avgYi = $countContrib > 0 ? $sumYi / $countContrib : 0;
        $avgY = $countContrib > 0 ? $sumY / $countContrib : 0;

        // 3. KLASIFIKASI TIPOLOGI KLASSEN
        if ($avgYi > $avgY && $avgRi > $avgR) {
            $kuadran = 'Kuadran I';
            $klasifikasi = 'Sektor Maju, tumbuh pesat';
        } elseif ($avgYi > $avgY && $avgRi < $avgR) {
            $kuadran = 'Kuadran II';
            $klasifikasi = 'Sektor Maju dan Tertekan';
        } elseif ($avgYi < $avgY && $avgRi > $avgR) {
            $kuadran = 'Kuadran III';
            $klasifikasi = 'Sektor Potensial';
        } else {
            $kuadran = 'Kuadran IV';
            $klasifikasi = 'Sektor Relatif Tertinggal';
        }

        $daerah_analisis = ($tingkatWilayah === 'Provinsi') ? $provinsi : $kabupaten;
        $daerah_pembanding = ($tingkatWilayah === 'Provinsi') ? 'Nasional' : $provinsi;

        return [
            'tingkat_wilayah' => $tingkatWilayah,
            'provinsi' => $provinsi,
            'kabupaten' => $tingkatWilayah === 'Provinsi' ? '-' : ($kabupaten ?? '-'),
            'daerah_analisis' => $daerah_analisis,
            'daerah_pembanding' => $daerah_pembanding,
            'sektor' => $sektor,
            'tahun_awal' => $yearsData[0]['tahun'],
            'tahun_akhir' => $yearsData[$countContrib - 1]['tahun'],

            // Save the bounds for reference (only for the first and last year)
            'pdrb_sektor_analisis_awal' => $this->parseNumber($yearsData[0]['pdrb_sektor_analisis']),
            'pdrb_sektor_analisis_akhir' => $this->parseNumber($yearsData[$countContrib - 1]['pdrb_sektor_analisis']),
            'total_pdrb_analisis_awal' => $this->parseNumber($yearsData[0]['total_pdrb_analisis']),
            'total_pdrb_analisis_akhir' => $this->parseNumber($yearsData[$countContrib - 1]['total_pdrb_analisis']),

            'pdrb_sektor_pembanding_awal' => $this->parseNumber($yearsData[0]['pdrb_sektor_pembanding']),
            'pdrb_sektor_pembanding_akhir' => $this->parseNumber($yearsData[$countContrib - 1]['pdrb_sektor_pembanding']),
            'total_pdrb_pembanding_awal' => $this->parseNumber($yearsData[0]['total_pdrb_pembanding']),
            'total_pdrb_pembanding_akhir' => $this->parseNumber($yearsData[$countContrib - 1]['total_pdrb_pembanding']),

            'ri' => round($avgRi, 2),
            'r' => round($avgR, 2),
            'yi' => round($avgYi, 2),
            'y' => round($avgY, 2),
            'kuadran' => $kuadran,
            'klasifikasi' => $klasifikasi,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'sektor' => 'required|string',
            'tahun' => 'required|array',
            'pdrb_sektor_analisis' => 'required|array',
            'total_pdrb_analisis' => 'required|array',
            'pdrb_sektor_pembanding' => 'required|array',
            'total_pdrb_pembanding' => 'required|array',
        ]);

        $yearsData = [];
        for ($i = 0; $i < count($request->tahun); $i++) {
            $yearsData[] = [
                'tahun' => $request->tahun[$i],
                'pdrb_sektor_analisis' => $request->pdrb_sektor_analisis[$i],
                'total_pdrb_analisis' => $request->total_pdrb_analisis[$i],
                'pdrb_sektor_pembanding' => $request->pdrb_sektor_pembanding[$i],
                'total_pdrb_pembanding' => $request->total_pdrb_pembanding[$i],
            ];
        }

        $data = $this->calculateKlassenData($yearsData, $request->tingkat_wilayah, $request->provinsi, $request->kabupaten, $request->sektor);
        if (!$data) {
            return back()->with('error', 'Terjadi kesalahan perhitungan atau jumlah tahun kurang dari 2.');
        }

        $sektorModel = Sektor::firstOrCreate(['nama_sektor' => $data['sektor']]);

        Klassen::create([
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
            'ri' => $data['ri'],
            'r' => $data['r'],
            'yi' => $data['yi'],
            'y' => $data['y'],
            'klasifikasi' => $data['klasifikasi']
        ]);

        OperatorController::logActivity('Analisis Klassen', 'ditambah', "Menambahkan data perhitungan Analisis Tipologi Klassen untuk sektor {$data['sektor']}.");

        return back()->with('success', 'Analisis Klassen berhasil disimpan secara permanen!');
    }

    public function update(Request $request, $id)
    {
        $klassen = Klassen::find($id);
        if (!$klassen) {
            return redirect()->route('operator.klassen.index')->with('error', 'Data tidak ditemukan!');
        }

        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'sektor' => 'required|string',
            'tahun' => 'required|array',
            'pdrb_sektor_analisis' => 'required|array',
            'total_pdrb_analisis' => 'required|array',
            'pdrb_sektor_pembanding' => 'required|array',
            'total_pdrb_pembanding' => 'required|array',
        ]);

        $yearsData = [];
        for ($i = 0; $i < count($request->tahun); $i++) {
            $yearsData[] = [
                'tahun' => $request->tahun[$i],
                'pdrb_sektor_analisis' => $request->pdrb_sektor_analisis[$i],
                'total_pdrb_analisis' => $request->total_pdrb_analisis[$i],
                'pdrb_sektor_pembanding' => $request->pdrb_sektor_pembanding[$i],
                'total_pdrb_pembanding' => $request->total_pdrb_pembanding[$i],
            ];
        }

        $data = $this->calculateKlassenData($yearsData, $request->tingkat_wilayah, $request->provinsi, $request->kabupaten, $request->sektor);
        if (!$data) {
            return back()->with('error', 'Terjadi kesalahan perhitungan atau jumlah tahun kurang dari 2.');
        }

        $sektorModel = Sektor::firstOrCreate(['nama_sektor' => $data['sektor']]);

        $klassen->update([
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
            'ri' => $data['ri'],
            'r' => $data['r'],
            'yi' => $data['yi'],
            'y' => $data['y'],
            'klasifikasi' => $data['klasifikasi']
        ]);

        OperatorController::logActivity('Analisis Klassen', 'diperbarui', "Memperbarui data perhitungan Analisis Tipologi Klassen untuk sektor {$data['sektor']}.");

        return redirect()->route('operator.klassen.index')->with('success', 'Data perhitungan Klassen berhasil diperbarui secara permanen!');
    }

    public function destroy($id)
    {
        $klassen = Klassen::find($id);
        if ($klassen) {
            $daerah = $klassen->daerah_analisis;
            $klassen->delete();
            OperatorController::logActivity('Tipologi Klassen', 'dihapus', "Menghapus data Tipologi Klassen daerah {$daerah}.");
        }

        return back()->with('success', 'Data Tipologi Klassen berhasil dihapus secara permanen!');
    }

    public function empty()
    {
        Klassen::truncate();
        OperatorController::logActivity('Analisis Tipologi Klassen', 'dihapus', "Menghapus semua data perhitungan Tipologi Klassen");
        return back()->with('success', 'Semua data perhitungan Tipologi Klassen berhasil dihapus secara permanen!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if (!empty($ids)) {
            $count = count($ids);
            Klassen::whereIn('id', $ids)->delete();
            OperatorController::logActivity('Analisis Tipologi Klassen', 'dihapus', "Menghapus {$count} data perhitungan Tipologi Klassen secara massal");
            return back()->with('success', "{$count} data perhitungan Tipologi Klassen berhasil dihapus secara massal!");
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
        $sektorsCache = Sektor::all()->pluck('sektor_id', 'nama_sektor')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])->toArray();

        // Group rows by Region and Sector for Relational calculation
        $groupedData = [];

        foreach ($payload as $rawItem) {
            $item = $this->normalizeKeys($rawItem);
            $hasProvinsi = isset($item['provinsi']) || isset($item['kodeprovinsi']) || isset($item['kodewilayah']);
            if (
                !$hasProvinsi || !isset($item['sektor']) || !isset($item['tahun']) ||
                !isset($item['pdrbsektor']) || !isset($item['totalpdrb']) ||
                !isset($item['pdrbsektorpembanding']) || !isset($item['totalpdrbpembanding'])
            ) {
                continue;
            }

            $resolved = $this->resolveRegionNames($rawItem);
            $provinsi = $resolved['provinsi'];
            $kabupaten = $resolved['kabupaten'];
            $tingkat = ($kabupaten != '-' && $kabupaten != '') ? 'Kabupaten/Kota' : 'Provinsi';

            $sektorName = $this->resolveSektorName($rawItem);
            $groupKey = $tingkat . '_' . $provinsi . '_' . $kabupaten . '_' . $sektorName;

            if (!isset($groupedData[$groupKey])) {
                $groupedData[$groupKey] = [
                    'tingkat_wilayah' => $tingkat,
                    'provinsi' => $provinsi,
                    'kabupaten' => $kabupaten,
                    'sektor' => $sektorName,
                    'years' => []
                ];
            }

            $groupedData[$groupKey]['years'][] = [
                'tahun' => $item['tahun'],
                'pdrb_sektor_analisis' => $item['pdrbsektor'],
                'total_pdrb_analisis' => $item['totalpdrb'],
                'pdrb_sektor_pembanding' => $item['pdrbsektorpembanding'],
                'total_pdrb_pembanding' => $item['totalpdrbpembanding'],
            ];
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($groupedData, &$successCount, &$sektorsCache) {
            foreach ($groupedData as $group) {
                // Minimum 2 years for YoY
                if (count($group['years']) < 2)
                    continue;

                $newData = $this->calculateKlassenData($group['years'], $group['tingkat_wilayah'], $group['provinsi'], $group['kabupaten'], $group['sektor']);

                if ($newData) {
                    $sektorKey = strtolower(trim($group['sektor']));
                    if (isset($sektorsCache[$sektorKey])) {
                        $sektorId = $sektorsCache[$sektorKey];
                    } else {
                        $sektorModel = Sektor::create(['nama_sektor' => $group['sektor']]);
                        $sektorsCache[$sektorKey] = $sektorModel->sektor_id;
                        $sektorId = $sektorModel->sektor_id;
                    }

                    Klassen::create([
                        'user_id' => Auth::id() ?? 1,
                        'sektor_id' => $sektorId,
                        'tingkat_wilayah' => $newData['tingkat_wilayah'],
                        'daerah_analisis' => $newData['daerah_analisis'],
                        'daerah_pembanding' => $newData['daerah_pembanding'],
                        'tahun_awal' => $newData['tahun_awal'],
                        'tahun_akhir' => $newData['tahun_akhir'],
                        'pdrb_sektor_analisis_awal' => $newData['pdrb_sektor_analisis_awal'],
                        'pdrb_sektor_analisis_akhir' => $newData['pdrb_sektor_analisis_akhir'],
                        'total_pdrb_analisis_awal' => $newData['total_pdrb_analisis_awal'],
                        'total_pdrb_analisis_akhir' => $newData['total_pdrb_analisis_akhir'],
                        'pdrb_sektor_pembanding_awal' => $newData['pdrb_sektor_pembanding_awal'],
                        'pdrb_sektor_pembanding_akhir' => $newData['pdrb_sektor_pembanding_akhir'],
                        'total_pdrb_pembanding_awal' => $newData['total_pdrb_pembanding_awal'],
                        'total_pdrb_pembanding_akhir' => $newData['total_pdrb_pembanding_akhir'],
                        'ri' => $newData['ri'],
                        'r' => $newData['r'],
                        'yi' => $newData['yi'],
                        'y' => $newData['y'],
                        'klasifikasi' => $newData['klasifikasi']
                    ]);
                    $successCount++;
                }
            }
        });

        if ($successCount > 0) {
            OperatorController::logActivity('Analisis Klassen', 'diimpor', "Mengimpor {$successCount} Sektor Analisis Tipologi Klassen secara massal menggunakan metode Year-on-Year.");
            session()->flash('success', "Berhasil mengimpor $successCount data baru!");
            return response()->json([
                'success' => true,
                'message' => $successCount . ' sektor berhasil dihitung.',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Tidak ada data valid yang dapat diimpor. Pastikan template menggunakan format tahun berurutan minimal 2 tahun per sektor.']);
    }

    public function syncFromDatabase(Request $request)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'sektor' => 'required|string',
            'tahun_awal' => 'required|numeric',
            'tahun_akhir' => 'required|numeric',
        ]);

        $daerahAnalisis = $request->tingkat_wilayah === 'Provinsi' ? $request->provinsi : $request->kabupaten;
        $sektorName = $request->sektor;
        $tahunAwal = $request->tahun_awal;
        $tahunAkhir = $request->tahun_akhir;

        if ($tahunAkhir <= $tahunAwal) {
            return response()->json(['success' => false, 'message' => 'Tahun akhir harus lebih besar dari tahun awal.']);
        }

        $sektorModel = Sektor::where('nama_sektor', $sektorName)->first();
        if (!$sektorModel) {
            return response()->json(['success' => false, 'message' => 'Sektor tidak ditemukan di database.']);
        }

        // Cari data LQ untuk semua tahun dalam rentang
        $lqData = \App\Models\Lq::where('daerah_analisis', $daerahAnalisis)
            ->where('sektor_id', $sektorModel->sektor_id)
            ->whereBetween('tahun', [$tahunAwal, $tahunAkhir])
            ->orderBy('tahun', 'asc')
            ->get();

        if ($lqData->count() < 2) {
            return response()->json(['success' => false, 'message' => "Data PDRB untuk daerah {$daerahAnalisis} sektor {$sektorName} dalam rentang {$tahunAwal} - {$tahunAkhir} kurang dari 2 tahun di database LQ. Sinkronisasi dibatalkan."]);
        }

        // Return the array of yearly data
        $yearsData = $lqData->map(function ($item) {
            return [
                'tahun' => $item->tahun,
                'pdrb_sektor_analisis' => $item->pdrb_sektor_analisis,
                'total_pdrb_analisis' => $item->total_pdrb_analisis,
                'pdrb_sektor_pembanding' => $item->pdrb_sektor_pembanding,
                'total_pdrb_pembanding' => $item->total_pdrb_pembanding,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $yearsData,
            'message' => 'Berhasil menarik data tahunan dari database!'
        ]);
    }

    public function syncAllFromDatabase(Request $request)
    {
        $request->validate([
            'tingkat_wilayah' => 'required|string',
            'provinsi' => 'required|string',
            'kabupaten' => 'nullable|string',
            'tahun_awal' => 'required|numeric',
            'tahun_akhir' => 'required|numeric',
        ]);

        $daerahAnalisis = $request->tingkat_wilayah === 'Provinsi' ? $request->provinsi : $request->kabupaten;
        $tahunAwal = $request->tahun_awal;
        $tahunAkhir = $request->tahun_akhir;

        if ($tahunAkhir <= $tahunAwal) {
            return response()->json(['success' => false, 'message' => 'Tahun akhir harus lebih besar dari tahun awal.']);
        }

        // Ambil semua data LQ untuk daerah tersebut pada rentang tahun yang dipilih
        $lqData = \App\Models\Lq::with('sektor')
            ->where('daerah_analisis', $daerahAnalisis)
            ->whereBetween('tahun', [$tahunAwal, $tahunAkhir])
            ->orderBy('tahun', 'asc')
            ->get();

        if ($lqData->isEmpty()) {
            return response()->json(['success' => false, 'message' => "Tidak ada data PDRB ditemukan untuk daerah {$daerahAnalisis} dalam rentang {$tahunAwal} - {$tahunAkhir} di database LQ."]);
        }

        // Kelompokkan berdasarkan sektor
        $groupedBySektor = $lqData->groupBy('sektor_id');
        $successCount = 0;

        \Illuminate\Support\Facades\DB::transaction(function () use ($groupedBySektor, $request, &$successCount) {
            foreach ($groupedBySektor as $sektorId => $items) {
                // Pastikan minimal ada 2 tahun data
                if ($items->count() < 2) continue;

                $sektorName = $items->first()->sektor->nama_sektor;

                // Format data array tahunan untuk calculateKlassenData
                $yearsData = $items->map(function ($item) {
                    return [
                        'tahun' => $item->tahun,
                        'pdrb_sektor_analisis' => $item->pdrb_sektor_analisis,
                        'total_pdrb_analisis' => $item->total_pdrb_analisis,
                        'pdrb_sektor_pembanding' => $item->pdrb_sektor_pembanding,
                        'total_pdrb_pembanding' => $item->total_pdrb_pembanding,
                    ];
                })->toArray();

                $newData = $this->calculateKlassenData($yearsData, $request->tingkat_wilayah, $request->provinsi, $request->kabupaten, $sektorName);

                if ($newData) {
                    Klassen::create([
                        'user_id' => Auth::id() ?? 1,
                        'sektor_id' => $sektorId,
                        'tingkat_wilayah' => $newData['tingkat_wilayah'],
                        'daerah_analisis' => $newData['daerah_analisis'],
                        'daerah_pembanding' => $newData['daerah_pembanding'],
                        'tahun_awal' => $newData['tahun_awal'],
                        'tahun_akhir' => $newData['tahun_akhir'],
                        'pdrb_sektor_analisis_awal' => $newData['pdrb_sektor_analisis_awal'],
                        'pdrb_sektor_analisis_akhir' => $newData['pdrb_sektor_analisis_akhir'],
                        'total_pdrb_analisis_awal' => $newData['total_pdrb_analisis_awal'],
                        'total_pdrb_analisis_akhir' => $newData['total_pdrb_analisis_akhir'],
                        'pdrb_sektor_pembanding_awal' => $newData['pdrb_sektor_pembanding_awal'],
                        'pdrb_sektor_pembanding_akhir' => $newData['pdrb_sektor_pembanding_akhir'],
                        'total_pdrb_pembanding_awal' => $newData['total_pdrb_pembanding_awal'],
                        'total_pdrb_pembanding_akhir' => $newData['total_pdrb_pembanding_akhir'],
                        'ri' => $newData['ri'],
                        'r' => $newData['r'],
                        'yi' => $newData['yi'],
                        'y' => $newData['y'],
                        'klasifikasi' => $newData['klasifikasi']
                    ]);
                    $successCount++;
                }
            }
        });

        if ($successCount > 0) {
            OperatorController::logActivity('Analisis Klassen', 'diimpor', "Menarik {$successCount} Sektor Analisis Tipologi Klassen dari database secara massal.");
            session()->flash('success', "Berhasil menarik dan menghitung {$successCount} sektor dari database!");
            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses {$successCount} sektor."
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Data ditemukan tapi tidak cukup lengkap (minimal 2 tahun berurutan) untuk dihitung.']);
    }

    public function downloadPdf(Request $request)
    {
        $query = Klassen::with('sektor');

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
        $klassenData = $this->mapDbToView($rawDbData);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('operator.klassen.pdf', [
            'klassenData' => $klassenData,
            'search' => $request->search ?? null,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('laporan-analisis-klassen-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadExcel(Request $request)
    {
        $query = Klassen::with('sektor');

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
        $klassenData = $this->mapDbToView($rawDbData);

        $html = view('operator.klassen.excel', [
            'klassenData' => $klassenData,
            'search' => $request->search ?? null,
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="laporan-analisis-klassen-' . now()->format('Y-m-d') . '.xls"')
            ->header('Cache-Control', 'max-age=0');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Provinsi;
use App\Services\TipologiSektorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvestmentMapController extends Controller
{
    protected TipologiSektorService $tipologiSektorService;

    public function __construct(TipologiSektorService $tipologiSektorService)
    {
        $this->tipologiSektorService = $tipologiSektorService;
    }

    /**
     * Tampilkan halaman peta investasi
     */
    public function index()
    {
        // Daftar nama ibukota provinsi di Sumatera
        $ibukotaNames = [
            'KOTA MEDAN', 'KOTA BANDA ACEH', 'KOTA PADANG', 'KOTA PEKANBARU',
            'KOTA PALEMBANG', 'KOTA JAMBI', 'KOTA BENGKULU', 'KOTA BANDAR LAMPUNG',
            'KOTA PANGKAL PINANG', 'KOTA PANGKALPINANG', 'KOTA TANJUNG PINANG', 'KOTA TANJUNGPINANG'
        ];

        // Ambil Kabupaten/Kota yang memiliki koordinat
        $lokasi = Kabupaten::with('provinsi')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('nama_kabupaten')
            ->get()
            ->map(function ($item) use ($ibukotaNames) {
                $cleanName = strtoupper(trim($item->nama_kabupaten));
                return [
                    'id' => $item->kab_id,
                    'nama' => $item->nama_kabupaten,
                    'latitude' => (float)$item->latitude,
                    'longitude' => (float)$item->longitude,
                    'type' => 'kabupaten',
                    'provinsi' => $item->provinsi->nama_provinsi ?? 'Sumatera',
                    'is_ibukota' => in_array($cleanName, $ibukotaNames),
                ];
            });

        return view('landing.map', compact('lokasi'));
    }

    /**
     * Ambil sektor unggulan & status dominan berdasarkan wilayah secara dinamis
     */
    public function analysis($nama)
    {
        try {
            $nama = trim(urldecode($nama));

            // 1. Cek apakah ini Provinsi
            $provinsi = Provinsi::whereRaw('UPPER(TRIM(nama_provinsi)) = ?', [strtoupper($nama)])->first();

            if ($provinsi) {
                $tahunTerbaru = DB::table('pdrb_sumatera_provinsi')
                    ->where('provinsi_id', $provinsi->provinsi_id)
                    ->max('tahun');

                if (!$tahunTerbaru) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Belum ada data PDRB untuk provinsi ini.',
                        'kabupaten' => $provinsi->nama_provinsi,
                    ]);
                }

                // Hitung secara dinamis Tipologi Sektor Provinsi vs PDB Nasional
                $tipologiRows = $this->tipologiSektorService->calculateTipologiProvinsi($provinsi->provinsi_id, $tahunTerbaru);

                // Hitung jumlah sektor per Kuadran
                $c1 = $tipologiRows->where('kuadran', 'Kuadran I')->count();
                $c2 = $tipologiRows->where('kuadran', 'Kuadran II')->count();
                $c3 = $tipologiRows->where('kuadran', 'Kuadran III')->count();
                $c4 = $tipologiRows->where('kuadran', 'Kuadran IV')->count();

                // Sektor Unggulan yang ditampilkan (Hirarki: Kuadran I -> II -> III -> IV)
                $kuadranI = $tipologiRows->where('kuadran', 'Kuadran I')->pluck('sektor.nama_sektor')->filter()->unique()->values();
                $kuadranII = $tipologiRows->where('kuadran', 'Kuadran II')->pluck('sektor.nama_sektor')->filter()->unique()->values();
                $kuadranIII = $tipologiRows->where('kuadran', 'Kuadran III')->pluck('sektor.nama_sektor')->filter()->unique()->values();
                $kuadranIV = $tipologiRows->where('kuadran', 'Kuadran IV')->pluck('sektor.nama_sektor')->filter()->unique()->values();

                $sektorTampil = $kuadranI->isNotEmpty() ? $kuadranI
                    : ($kuadranII->isNotEmpty() ? $kuadranII
                    : ($kuadranIII->isNotEmpty() ? $kuadranIII
                    : ($kuadranIV->isNotEmpty() ? $kuadranIV : collect(['Sektor PDRB dalam Proses Pengolahan']))));

                // Algoritma Penentuan Kuadran Dominan + Tie-Breaking Rule (Provinsi vs PDB Nasional)
                $scores = [
                    'Kuadran I' => ($c1 * 100) + ($c2 * 10) + ($c3 * 1),
                    'Kuadran II' => ($c2 * 100) + ($c1 * 10) + ($c4 * 1),
                    'Kuadran III' => ($c3 * 100) + ($c4 * 10) + ($c1 * 1),
                    'Kuadran IV' => ($c4 * 100) + ($c3 * 10) + ($c2 * 1),
                ];

                arsort($scores);
                $dominantKuadran = key($scores);

                $labelMap = [
                    'Kuadran I' => "Dominan Kuadran I (Sektor Cepat Maju & Cepat Tumbuh - Provinsi)",
                    'Kuadran II' => "Dominan Kuadran II (Sektor Potensial / Cepat Berkembang - Provinsi)",
                    'Kuadran III' => "Dominan Kuadran III (Sektor Maju Tapi Tertekan - Provinsi)",
                    'Kuadran IV' => "Dominan Kuadran IV (Sektor Relatif Tertinggal - Provinsi)",
                ];

                $kategoriStatus = $labelMap[$dominantKuadran] ?? 'Data PDRB Dalam Pengolahan';

                return response()->json([
                    'success' => true,
                    'kabupaten' => $provinsi->nama_provinsi,
                    'tahun' => $tahunTerbaru,
                    'kategori' => $kategoriStatus,
                    'status' => $kategoriStatus,
                    'jumlah_sektor' => $sektorTampil->count(),
                    'sektor' => $sektorTampil,
                    'distribusi_kuadran' => [
                        'kuadran_1' => $c1,
                        'kuadran_2' => $c2,
                        'kuadran_3' => $c3,
                        'kuadran_4' => $c4,
                    ],
                ]);
            }

            // 2. Cek apakah ini Kabupaten (Pencarian Fleksibel)
            $cleanSearch = strtoupper(str_replace([' ', '.', 'KABUPATEN', 'KOTA', 'KAB'], '', $nama));

            $kabupaten = Kabupaten::all()->first(function ($item) use ($cleanSearch) {
                $cleanName = strtoupper(str_replace([' ', '.', 'KABUPATEN', 'KOTA', 'KAB'], '', $item->nama_kabupaten));
                return $cleanName === $cleanSearch;
            });

            if (!$kabupaten) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data analisis daerah belum tersedia.',
                    'nama_marker' => $nama,
                ], 404);
            }

            // Ambil tahun terbaru PDRB kabupaten tersebut
            $tahunTerbaru = DB::table('pdrb_sumatera_kabupaten')
                ->where('kabupaten_id', $kabupaten->kab_id)
                ->max('tahun');

            if (!$tahunTerbaru) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada hasil analisis untuk daerah ini.',
                    'kabupaten' => $kabupaten->nama_kabupaten,
                ]);
            }

            // Hitung secara dinamis menggunakan TipologiSektorService (Kabupaten vs Provinsi)
            $tipologiRows = $this->tipologiSektorService->calculateTipologi($kabupaten->kab_id, $tahunTerbaru);

            // Hitung jumlah sektor per Kuadran
            $c1 = $tipologiRows->where('kuadran', 'Kuadran I')->count();
            $c2 = $tipologiRows->where('kuadran', 'Kuadran II')->count();
            $c3 = $tipologiRows->where('kuadran', 'Kuadran III')->count();
            $c4 = $tipologiRows->where('kuadran', 'Kuadran IV')->count();

            // Sektor Unggulan yang ditampilkan (Hirarki: Kuadran I -> II -> III -> IV)
            $kuadranI = $tipologiRows->where('kuadran', 'Kuadran I')->pluck('sektor.nama_sektor')->filter()->unique()->values();
            $kuadranII = $tipologiRows->where('kuadran', 'Kuadran II')->pluck('sektor.nama_sektor')->filter()->unique()->values();
            $kuadranIII = $tipologiRows->where('kuadran', 'Kuadran III')->pluck('sektor.nama_sektor')->filter()->unique()->values();
            $kuadranIV = $tipologiRows->where('kuadran', 'Kuadran IV')->pluck('sektor.nama_sektor')->filter()->unique()->values();

            $sektorTampil = $kuadranI->isNotEmpty() ? $kuadranI
                : ($kuadranII->isNotEmpty() ? $kuadranII
                : ($kuadranIII->isNotEmpty() ? $kuadranIII
                : ($kuadranIV->isNotEmpty() ? $kuadranIV : collect(['Sektor PDRB dalam Proses Pengolahan']))));

            // Algoritma Penentuan Kuadran Dominan + Tie-Breaking Rule (Kabupaten vs Provinsi)
            $scores = [
                'Kuadran I' => ($c1 * 100) + ($c2 * 10) + ($c3 * 1),
                'Kuadran II' => ($c2 * 100) + ($c1 * 10) + ($c4 * 1),
                'Kuadran III' => ($c3 * 100) + ($c4 * 10) + ($c1 * 1),
                'Kuadran IV' => ($c4 * 100) + ($c3 * 10) + ($c2 * 1),
            ];

            arsort($scores);
            $dominantKuadran = key($scores);

            $labelMap = [
                'Kuadran I' => "Dominan Kuadran I (Sektor Cepat Maju & Cepat Tumbuh)",
                'Kuadran II' => "Dominan Kuadran II (Sektor Potensial / Cepat Berkembang)",
                'Kuadran III' => "Dominan Kuadran III (Sektor Maju Tapi Tertekan)",
                'Kuadran IV' => "Dominan Kuadran IV (Sektor Relatif Tertinggal)",
            ];

            $kategoriStatus = $labelMap[$dominantKuadran] ?? 'Data PDRB Dalam Pengolahan';

            return response()->json([
                'success' => true,
                'kabupaten' => $kabupaten->nama_kabupaten,
                'tahun' => $tahunTerbaru,
                'kategori' => $kategoriStatus,
                'status' => $kategoriStatus,
                'jumlah_sektor' => $sektorTampil->count(),
                'sektor' => $sektorTampil,
                'distribusi_kuadran' => [
                    'kuadran_1' => $c1,
                    'kuadran_2' => $c2,
                    'kuadran_3' => $c3,
                    'kuadran_4' => $c4,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Peta Investasi Error: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
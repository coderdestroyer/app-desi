<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use App\Models\Provinsi;
use Illuminate\Support\Facades\DB;

class InvestmentMapController extends Controller
{
    /**
     * Tampilkan halaman peta investasi
     */
    public function index()
    {
        // Ambil Provinsi yang memiliki koordinat
        $provinsi = Provinsi::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('nama_provinsi')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->provinsi_id,
                    'nama' => $item->nama_provinsi,
                    'latitude' => (float)$item->latitude,
                    'longitude' => (float)$item->longitude,
                    'type' => 'provinsi',
                ];
            });

        // Ambil Kabupaten yang memiliki koordinat
        $kabupaten = Kabupaten::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('nama_kabupaten')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->kab_id,
                    'nama' => $item->nama_kabupaten,
                    'latitude' => (float)$item->latitude,
                    'longitude' => (float)$item->longitude,
                    'type' => 'kabupaten',
                ];
            });

        // Gabungkan keduanya
        $lokasi = $provinsi->concat($kabupaten);

        return view('landing.map', compact('lokasi'));
    }

    /**
     * Ambil sektor unggulan berdasarkan wilayah (kabupaten/provinsi)
     */
    public function analysis($nama)
    {
        try {
            $nama = trim($nama);

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

                // Ambil 3 sektor terbesar berdasarkan nilai PDRB
                $sektorUnggulan = DB::table('pdrb_sumatera_provinsi as psp')
                    ->join('sektor as s', 's.sektor_id', '=', 'psp.sektor_id')
                    ->where('psp.provinsi_id', $provinsi->provinsi_id)
                    ->where('psp.tahun', $tahunTerbaru)
                    ->orderByDesc('psp.nilai_pdrb')
                    ->select('s.nama_sektor')
                    ->take(3)
                    ->get();

                return response()->json([
                    'success' => true,
                    'kabupaten' => $provinsi->nama_provinsi,
                    'tahun' => $tahunTerbaru,
                    'kategori' => 'Sektor PDRB Terbesar',
                    'jumlah_sektor' => $sektorUnggulan->count(),
                    'sektor' => $sektorUnggulan->pluck('nama_sektor')->values(),
                ]);
            }

            // 2. Cek apakah ini Kabupaten
            $namaMapping = [
                'Kota Padangsidimpuan' => 'KOTA PADANG SIDEMPUAN',
                'Kabupaten Nias Tengah' => 'KAB. NIAS TENGAH',
            ];

            if (isset($namaMapping[$nama])) {
                $namaDatabase = $namaMapping[$nama];
            } elseif (stripos($nama, 'Kabupaten ') === 0) {
                $namaKabupaten = substr($nama, strlen('Kabupaten '));
                $namaDatabase = 'KAB. ' . strtoupper(trim($namaKabupaten));
            } elseif (stripos($nama, 'Kota ') === 0) {
                $namaKota = substr($nama, strlen('Kota '));
                $namaDatabase = 'KOTA ' . strtoupper(trim($namaKota));
            } else {
                $namaDatabase = strtoupper($nama);
            }

            $kabupaten = Kabupaten::whereRaw('UPPER(TRIM(nama_kabupaten)) = ?', [strtoupper(trim($namaDatabase))])->first();

            if (!$kabupaten) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data analisis daerah belum tersedia.',
                    'nama_marker' => $nama,
                ], 404);
            }

            // Ambil analisis tipologi sektor untuk kabupaten tersebut
            $tahunTerbaru = DB::table('hasil_tipologi_sektor')
                ->where('kab_id', $kabupaten->kab_id)
                ->max('tahun');

            if (!$tahunTerbaru) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada hasil analisis untuk daerah ini.',
                    'kabupaten' => $kabupaten->nama_kabupaten,
                ]);
            }

            $sektorUnggulan = DB::table('hasil_tipologi_sektor as hts')
                ->join('sektor as s', 's.sektor_id', '=', 'hts.sektor_id')
                ->where('hts.kab_id', $kabupaten->kab_id)
                ->where('hts.tahun', $tahunTerbaru)
                ->where('hts.kuadran', 'Kuadran I')
                ->select('s.nama_sektor')
                ->distinct()
                ->orderBy('s.nama_sektor')
                ->get();

            return response()->json([
                'success' => true,
                'kabupaten' => $kabupaten->nama_kabupaten,
                'tahun' => $tahunTerbaru,
                'kategori' => 'Sektor Cepat Maju dan Cepat Tumbuh',
                'jumlah_sektor' => $sektorUnggulan->count(),
                'sektor' => $sektorUnggulan->pluck('nama_sektor')->values(),
            ]);

        } catch (\Throwable $e) {
            \Log::error('Peta Investasi Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
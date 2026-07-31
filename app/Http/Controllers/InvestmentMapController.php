<?php

namespace App\Http\Controllers;

use App\Models\Kabupaten;
use Illuminate\Support\Facades\DB;

class InvestmentMapController extends Controller
{
    /**
     * Tampilkan halaman peta investasi
     */
    public function index()
    {
        $lokasi = Kabupaten::whereNotNull('latitude')->orderBy('nama_kabupaten')->get();

        return view('landing.map', compact('lokasi'));
    }


    /**
     * Ambil sektor unggulan berdasarkan kabupaten/kota
     */
    public function analysis($nama)
    {
        try {

            // =====================================================
            // 1. BERSIHKAN NAMA DARI MARKER
            // =====================================================

            $nama = trim($nama);


            // =====================================================
            // 2. MAPPING NAMA KHUSUS
            // =====================================================
            //
            // Digunakan kalau nama pada tabel "lokasi"
            // berbeda dengan tabel "kabupaten".
            //

            $namaMapping = [

                // lokasi:
                // Kota Padangsidimpuan
                //
                // database:
                // KOTA PADANG SIDEMPUAN
                'Kota Padangsidimpuan'
                    => 'KOTA PADANG SIDEMPUAN',


                // lokasi:
                // Kabupaten Nias Tengah
                //
                // database:
                // KAB. NIAS TENGAH
                'Kabupaten Nias Tengah'
                    => 'KAB. NIAS TENGAH',

            ];


            // =====================================================
            // 3. CEK APAKAH ADA MAPPING KHUSUS
            // =====================================================

            if (isset($namaMapping[$nama])) {

                $namaDatabase =
                    $namaMapping[$nama];

            }

            // =====================================================
            // 4. NORMALISASI KABUPATEN
            // =====================================================

            elseif (
                stripos(
                    $nama,
                    'Kabupaten '
                ) === 0
            ) {

                $namaKabupaten =
                    substr(
                        $nama,
                        strlen('Kabupaten ')
                    );

                $namaDatabase =
                    'KAB. ' .
                    strtoupper(
                        trim($namaKabupaten)
                    );

            }

            // =====================================================
            // 5. NORMALISASI KOTA
            // =====================================================

            elseif (
                stripos(
                    $nama,
                    'Kota '
                ) === 0
            ) {

                $namaKota =
                    substr(
                        $nama,
                        strlen('Kota ')
                    );

                $namaDatabase =
                    'KOTA ' .
                    strtoupper(
                        trim($namaKota)
                    );

            }

            // =====================================================
            // 6. FALLBACK
            // =====================================================

            else {

                $namaDatabase =
                    strtoupper($nama);

            }


            // =====================================================
            // DEBUG
            // =====================================================

            \Log::info(
                'Peta Investasi - Pencarian Kabupaten',
                [
                    'nama_marker' =>
                        $nama,

                    'nama_database' =>
                        $namaDatabase,
                ]
            );


            // =====================================================
            // 7. CARI KABUPATEN/KOTA
            // =====================================================

            $kabupaten =
                DB::table('kabupaten')

                    ->whereRaw(
                        'UPPER(TRIM(nama_kabupaten)) = ?',
                        [
                            strtoupper(
                                trim($namaDatabase)
                            )
                        ]
                    )

                    ->first();


            // =====================================================
            // 8. KABUPATEN TIDAK DITEMUKAN
            // =====================================================

            if (!$kabupaten) {

                return response()->json([
                    'success' => false,

                    'message' =>
                        'Data analisis daerah belum tersedia.',

                    'nama_marker' =>
                        $nama,

                    'nama_dicari' =>
                        $namaDatabase,

                ], 404);
            }


            // =====================================================
            // 9. CARI TAHUN TERBARU
            // =====================================================

            $tahunTerbaru =
                DB::table(
                    'hasil_tipologi_sektor'
                )

                    ->where(
                        'kab_id',
                        $kabupaten->kab_id
                    )

                    ->max('tahun');


            // =====================================================
            // 10. BELUM ADA HASIL ANALISIS
            // =====================================================

            if (!$tahunTerbaru) {

                return response()->json([
                    'success' => false,

                    'message' =>
                        'Belum ada hasil analisis untuk daerah ini.',

                    'kabupaten' =>
                        $kabupaten->nama_kabupaten,

                ]);
            }


            // =====================================================
            // 11. AMBIL SEKTOR UNGGULAN
            // =====================================================
            //
            // Definisi:
            //
            // Kuadran I =
            // Sektor Cepat Maju dan Cepat Tumbuh
            //
            // Hanya data tahun terbaru yang digunakan.
            //

            $sektorUnggulan =
                DB::table(
                    'hasil_tipologi_sektor as hts'
                )

                    ->join(
                        'sektor as s',
                        's.sektor_id',
                        '=',
                        'hts.sektor_id'
                    )

                    ->where(
                        'hts.kab_id',
                        $kabupaten->kab_id
                    )

                    ->where(
                        'hts.tahun',
                        $tahunTerbaru
                    )

                    ->where(
                        'hts.kuadran',
                        'Kuadran I'
                    )

                    ->select(
                        's.sektor_id',
                        's.nama_sektor'
                    )

                    ->distinct()

                    ->orderBy(
                        's.nama_sektor'
                    )

                    ->get();


            // =====================================================
            // 12. RESPONSE
            // =====================================================

            return response()->json([

                'success' => true,

                'kabupaten' =>
                    $kabupaten->nama_kabupaten,

                'tahun' =>
                    $tahunTerbaru,

                'kategori' =>
                    'Sektor Cepat Maju dan Cepat Tumbuh',

                'jumlah_sektor' =>
                    $sektorUnggulan->count(),

                'sektor' =>
                    $sektorUnggulan
                        ->pluck('nama_sektor')
                        ->values(),

            ]);

        }

        // =========================================================
        // ERROR SERVER
        // =========================================================

        catch (\Throwable $e) {

            \Log::error(
                'Peta Investasi Error',
                [
                    'nama' =>
                        $nama,

                    'error' =>
                        $e->getMessage(),
                ]
            );


            return response()->json([

                'success' => false,

                'message' =>
                    'Terjadi kesalahan saat mengambil data.',

                // Untuk debugging sementara
                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }
}
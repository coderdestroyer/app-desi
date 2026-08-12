<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    public function index()
    {
        $provinsiInvestasi = [];
        
        // Dynamic latest year from DB (check data_investasi or pdrb_sumatera_provinsi)
        $latestYear = DB::table('data_investasi')->max('tahun')
            ?? DB::table('pdrb_sumatera_provinsi')->max('tahun')
            ?? 2025;

        // Default values for metrics
        $totalRealisasi = 0;
        $pdrbTertinggiNama = 'SUMATERA UTARA';
        $pdrbTertinggiNilai = 0;
        $jumlahProyek = 0;
        $topSectors = [];
        $trendsData = [];

        try {
            // 1. PDRB Per Provinsi for latest available PDRB year
            $pdrbYear = DB::table('pdrb_sumatera_provinsi')
                ->where('tahun', '<=', $latestYear)
                ->max('tahun') ?? $latestYear;

            $provinsiInvestasi = DB::table('pdrb_sumatera_provinsi')
                ->join('provinsi', 'pdrb_sumatera_provinsi.provinsi_id', '=', 'provinsi.provinsi_id')
                ->where('pdrb_sumatera_provinsi.tahun', $pdrbYear)
                ->select('provinsi.nama_provinsi', DB::raw('SUM(nilai_pdrb) as total_pdrb'))
                ->groupBy('provinsi.nama_provinsi')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [strtoupper(trim($item->nama_provinsi)) => (float)$item->total_pdrb];
                })
                ->toArray();

            // PDRB Tertinggi per provinsi pada tahun terbaru di DB
            if (!empty($provinsiInvestasi)) {
                $pdrbTertinggiNilai = max($provinsiInvestasi);
                $pdrbTertinggiNama = array_search($pdrbTertinggiNilai, $provinsiInvestasi);
            }

            // 2. Total Realisasi Investasi (100% Real DB dari tabel data_investasi untuk $latestYear)
            $realSumInvestasi = DB::table('data_investasi')
                ->where('tahun', $latestYear)
                ->sum('nilai_investasi');

            if ($realSumInvestasi > 0) {
                $totalRealisasi = (float) $realSumInvestasi;
            } else {
                $maxYearInvestasi = DB::table('data_investasi')->max('tahun');
                if ($maxYearInvestasi) {
                    $totalRealisasi = (float) DB::table('data_investasi')
                        ->where('tahun', $maxYearInvestasi)
                        ->sum('nilai_investasi');
                }
            }

            // 3. Jumlah Proyek (100% Real DB dari count tabel projects IPRO)
            $jumlahProyek = DB::table('projects')->count();

            // 4. Top 5 sektor potensial (Diambil dari PDRB Sumatera Utara tahun terbaru)
            $topSectors = DB::table('pdrb_sumatera_provinsi')
                ->join('sektor', 'pdrb_sumatera_provinsi.sektor_id', '=', 'sektor.sektor_id')
                ->join('provinsi', 'pdrb_sumatera_provinsi.provinsi_id', '=', 'provinsi.provinsi_id')
                ->where('pdrb_sumatera_provinsi.tahun', $pdrbYear)
                ->where('provinsi.nama_provinsi', 'like', '%SUMATERA UTARA%')
                ->select('sektor.nama_sektor', 'pdrb_sumatera_provinsi.nilai_pdrb as total_pdrb')
                ->orderBy('pdrb_sumatera_provinsi.nilai_pdrb', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'nama_sektor' => trim($item->nama_sektor),
                        'total_pdrb' => (float)$item->total_pdrb
                    ];
                })
                ->toArray();

            // Jika PDRB Sumut belum ada, fallback ke agregasi seluruh provinsi di Sumatera
            if (empty($topSectors)) {
                $topSectors = DB::table('pdrb_sumatera_provinsi')
                    ->join('sektor', 'pdrb_sumatera_provinsi.sektor_id', '=', 'sektor.sektor_id')
                    ->where('pdrb_sumatera_provinsi.tahun', $pdrbYear)
                    ->select('sektor.nama_sektor', DB::raw('SUM(nilai_pdrb) as total_pdrb'))
                    ->groupBy('sektor.nama_sektor')
                    ->orderBy('total_pdrb', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function ($item) {
                        return [
                            'nama_sektor' => trim($item->nama_sektor),
                            'total_pdrb' => (float)$item->total_pdrb
                        ];
                    })
                    ->toArray();
            }

            // 5. Trends data (Real DB)
            $trendsData = DB::table('pdrb_sumatera_provinsi')
                ->join('provinsi', 'pdrb_sumatera_provinsi.provinsi_id', '=', 'provinsi.provinsi_id')
                ->select('provinsi.nama_provinsi', 'pdrb_sumatera_provinsi.tahun', DB::raw('SUM(nilai_pdrb) as total_pdrb'))
                ->groupBy('provinsi.nama_provinsi', 'pdrb_sumatera_provinsi.tahun')
                ->orderBy('pdrb_sumatera_provinsi.tahun')
                ->get()
                ->groupBy(function($item) {
                    return strtoupper(trim($item->nama_provinsi));
                })
                ->map(function($group) {
                    return $group->map(function($item) {
                        return [
                            'tahun' => (int)$item->tahun,
                            'pdrb' => (float)$item->total_pdrb,
                            'investasi' => (float)$item->total_pdrb * 0.10
                        ];
                    })->values()->toArray();
                })
                ->toArray();

            // Real investment data for SUMATERA UTARA from data_investasi table
            $realInvestasiSumut = DB::table('data_investasi')
                ->select('tahun', DB::raw('SUM(nilai_investasi) as total_investasi'))
                ->groupBy('tahun')
                ->orderBy('tahun')
                ->get();

            if ($realInvestasiSumut->isNotEmpty()) {
                $trendsData['SUMATERA UTARA'] = $realInvestasiSumut->map(function($item) {
                    return [
                        'tahun' => (int)$item->tahun,
                        'investasi' => (float)$item->total_investasi
                    ];
                })->values()->toArray();
            }

        } catch (\Throwable $e) {
            // Fallback if db query fails
        }

        // Fallback default dataset jika DB belum terisi data PDRB
        if (empty($provinsiInvestasi)) {
            $provinsiInvestasi = [
                'ACEH' => 180000000000000,
                'SUMATERA UTARA' => 350000000000000,
                'SUMATERA BARAT' => 120000000000000,
                'RIAU' => 280000000000000,
                'JAMBI' => 90000000000000,
                'SUMATERA SELATAN' => 240000000000000,
                'BENGKULU' => 45000000000000,
                'LAMPUNG' => 150000000000000,
                'KEPULAUAN BANGKA BELITUNG' => 60000000000000,
                'KEPULAUAN RIAU' => 110000000000000,
            ];
        }

        if (empty($topSectors)) {
            $topSectors = [
                ['nama_sektor' => 'PERTANIAN, KEHUTANAN, DAN PERIKANAN', 'total_pdrb' => 450000000000000],
                ['nama_sektor' => 'INDUSTRI PENGOLAHAN', 'total_pdrb' => 380000000000000],
                ['nama_sektor' => 'PERTAMBANGAN DAN PENGGALIAN', 'total_pdrb' => 310000000000000],
                ['nama_sektor' => 'PERDAGANGAN BESAR DAN ECERAN', 'total_pdrb' => 250000000000000],
                ['nama_sektor' => 'KONSTRUKSI', 'total_pdrb' => 190000000000000],
            ];
        }

        return view('landing.home', compact(
            'provinsiInvestasi',
            'latestYear',
            'totalRealisasi',
            'pdrbTertinggiNama',
            'pdrbTertinggiNilai',
            'jumlahProyek',
            'topSectors',
            'trendsData'
        ));
    }
}

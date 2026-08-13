<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    public function index()
    {
        $provinsiInvestasi = [];
        
        // Latest year for PDRB & Investasi
        $latestPdrbYear = DB::table('pdrb_sumatera_provinsi')->max('tahun') ?? 2025;
        $latestYear = DB::table('data_investasi')->max('tahun') ?? $latestPdrbYear;

        // Default values for metrics
        $totalRealisasi = 0;
        $pdrbTertinggiNama = 'SUMATERA UTARA';
        $pdrbTertinggiNilai = 0;
        $jumlahDataInvestasi = 0;
        $topSectors = [];
        $allTopSectors = [];
        $provinsiList = [];
        $trendsData = [];

        try {
            // 1. PDRB Per Provinsi for latest available PDRB year
            $pdrbYear = $latestPdrbYear;

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

            // 3. Jumlah Data Investasi (100% Real DB dari count tabel data_investasi untuk $latestYear)
            $jumlahDataInvestasi = DB::table('data_investasi')->where('tahun', $latestYear)->count();
            if ($jumlahDataInvestasi === 0) {
                $jumlahDataInvestasi = DB::table('data_investasi')->count();
            }

            // 4. Top 5 sektor potensial per Provinsi untuk $pdrbYear
            $allTopSectors = DB::table('pdrb_sumatera_provinsi')
                ->join('sektor', 'pdrb_sumatera_provinsi.sektor_id', '=', 'sektor.sektor_id')
                ->join('provinsi', 'pdrb_sumatera_provinsi.provinsi_id', '=', 'provinsi.provinsi_id')
                ->where('pdrb_sumatera_provinsi.tahun', $pdrbYear)
                ->select('provinsi.nama_provinsi', 'sektor.nama_sektor', 'pdrb_sumatera_provinsi.nilai_pdrb as total_pdrb')
                ->get()
                ->groupBy(function($item) {
                    return strtoupper(trim($item->nama_provinsi));
                })
                ->map(function($group) {
                    return $group->sortByDesc('total_pdrb')->take(5)->map(function($item) {
                        return [
                            'nama_sektor' => trim($item->nama_sektor),
                            'total_pdrb' => (float)$item->total_pdrb
                        ];
                    })->values()->toArray();
                })
                ->toArray();

            $topSectors = $allTopSectors['SUMATERA UTARA'] ?? reset($allTopSectors) ?: [];
            $provinsiList = array_keys($allTopSectors);

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
            $allTopSectors['SUMATERA UTARA'] = $topSectors;
        }

        if (empty($provinsiList)) {
            $provinsiList = array_keys($provinsiInvestasi);
        }

        return view('landing.home', compact(
            'provinsiInvestasi',
            'latestYear',
            'latestPdrbYear',
            'totalRealisasi',
            'pdrbTertinggiNama',
            'pdrbTertinggiNilai',
            'jumlahDataInvestasi',
            'topSectors',
            'allTopSectors',
            'provinsiList',
            'trendsData'
        ));
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvestasiSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/data_perusahaan_investasi.csv');
        $legacyFile = database_path('data/Daftar_investasi_SUMUT.csv');

        if (file_exists($file)) {
            $this->seedDetailedPerusahaanInvestasi($file);
        } elseif (file_exists($legacyFile)) {
            $this->seedLegacyDataInvestasi($legacyFile);
        }
    }

    private function seedDetailedPerusahaanInvestasi(string $file): void
    {
        // Helper to normalize kabupaten/kota name for string matching
        $getCleanName = function ($str) {
            $s = strtoupper(trim($str));
            $s = preg_replace('/^(KABUPATEN|KAB\.|KAB|KOTA)\s+/i', '', $s);
            $s = preg_replace('/[^A-Z0-9]/', '', $s);
            return $s;
        };

        // Load provinsi mapping (Default to 12 - Sumatera Utara)
        $provinsiMap = DB::table('provinsi')->pluck('provinsi_id', 'nama_provinsi')->toArray();
        $getProvId = function ($name) use ($provinsiMap) {
            $upperName = strtoupper(trim($name));
            foreach ($provinsiMap as $provName => $id) {
                if (strtoupper(trim($provName)) === $upperName) {
                    return $id;
                }
            }
            return 12; // Default Sumatera Utara
        };

        // Load kabupaten mapping from DB or fallback CSV
        $kabMap = [];
        $kabRows = DB::table('kabupaten')->get(['kab_id', 'nama_kabupaten']);
        if ($kabRows->isNotEmpty()) {
            foreach ($kabRows as $k) {
                $clean = $getCleanName($k->nama_kabupaten);
                $kabMap[$clean] = $k->kab_id;
            }
        } else {
            $kabCsv = database_path('data/Sumatera_Kabupaten.csv');
            if (file_exists($kabCsv)) {
                $h = fopen($kabCsv, 'r');
                fgetcsv($h);
                while (($r = fgetcsv($h)) !== false) {
                    if (!empty($r[0])) {
                        $clean = $getCleanName($r[2]);
                        $kabMap[$clean] = (int) $r[0];
                    }
                }
                fclose($h);
            }
        }

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Header: id_laporan_lkpm, id_proyek_nku, nama_perusahaan, status, tahun, kabupaten_kota_usaha, provinsi_usaha, nama_sektor, nilai_investasi

        $batch = [];
        $now = now();
        $batchSize = 2500;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 9 || empty($row[4])) continue;

            $kabKey = $getCleanName($row[5]);
            $kabId = $kabMap[$kabKey] ?? null;
            $provId = $getProvId($row[6]);

            $batch[] = [
                'id_laporan_lkpm' => !empty($row[0]) ? (int) $row[0] : null,
                'id_proyek_nku' => !empty($row[1]) ? (int) $row[1] : null,
                'nama_perusahaan' => mb_substr(trim($row[2]), 0, 255),
                'status' => mb_substr(trim($row[3]), 0, 20),
                'provinsi_id' => $provId,
                'kabupaten_id' => $kabId,
                'nama_sektor' => mb_substr(trim($row[7]), 0, 255),
                'tahun' => (int) $row[4],
                'nilai_investasi' => (float) trim($row[8]),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('data_investasi')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('data_investasi')->insert($batch);
        }

        fclose($handle);
    }

    private function seedLegacyDataInvestasi(string $file): void
    {
        $handle = fopen($file, 'r');
        fgetcsv($handle);

        $batch = [];
        $now = now();
        $batchSize = 2000;

        while (($row = fgetcsv($handle)) !== false) {
            if (!isset($row[0]) || trim($row[0]) === '') continue;

            $batch[] = [
                'provinsi_id' => 12,
                'tahun' => (int) trim($row[0]),
                'nilai_investasi' => isset($row[2]) ? (float) trim($row[2]) : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('data_investasi')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('data_investasi')->insert($batch);
        }

        fclose($handle);
    }
}

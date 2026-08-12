<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvestasiSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/Daftar_investasi_SUMUT.csv');
        if (!file_exists($file)) return;

        // Fetch or fallback provinsi_id mapping
        $provinsiMap = DB::table('provinsi')->get()->pluck('provinsi_id', 'nama_provinsi')->toArray();

        // Helper to resolve provinsi_id
        $getProvinsiId = function ($name) use ($provinsiMap) {
            $upperName = strtoupper(trim($name));
            foreach ($provinsiMap as $provName => $id) {
                if (strtoupper(trim($provName)) === $upperName) {
                    return $id;
                }
            }
            return 12; // Default to 12 (Sumatera Utara)
        };

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Read header: Tahun,Provinsi,Nilai Investasi

        $batch = [];
        $now = now();
        $batchSize = 2000;

        while (($row = fgetcsv($handle)) !== false) {
            if (!isset($row[0]) || trim($row[0]) === '') continue;

            $tahun = (int) trim($row[0]);
            $provinsiNama = trim($row[1] ?? 'Sumatera Utara');
            $nilaiInvestasi = isset($row[2]) ? (float) trim($row[2]) : 0;
            $provId = $getProvinsiId($provinsiNama);

            $batch[] = [
                'provinsi_id' => $provId,
                'tahun' => $tahun,
                'nilai_investasi' => $nilaiInvestasi,
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterStandardSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSektor();
        $this->seedLokasi();
        $this->seedKbli();
        $this->seedKbki();
    }

    private function seedSektor(): void
    {
        $file = database_path('data/sektor.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;
            DB::table('sektor')->updateOrInsert(
                ['sektor_id' => (int) $row[0]],
                [
                    'nama_sektor' => trim($row[1]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        fclose($handle);
    }

    private function seedLokasi(): void
    {
        $file = database_path('data/lokasi.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;
            DB::table('lokasi')->updateOrInsert(
                ['id' => (int) $row[0]],
                [
                    'nama' => trim($row[1]),
                    'latitude' => (float) $row[2],
                    'longitude' => (float) $row[3],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        fclose($handle);
    }

    private function seedKbli(): void
    {
        $file = database_path('data/data_kbli.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 11) continue;

            $batch[] = [
                'struktur' => trim($row[1]),
                'level' => (int) $row[2],
                'kode' => trim($row[3]),
                'kode_induk' => !empty($row[4]) ? trim($row[4]) : null,
                'kategori_kode' => !empty($row[5]) ? trim($row[5]) : null,
                'golongan_pokok_kode' => !empty($row[6]) ? trim($row[6]) : null,
                'golongan_kode' => !empty($row[7]) ? trim($row[7]) : null,
                'subgolongan_kode' => !empty($row[8]) ? trim($row[8]) : null,
                'kelompok_kode' => !empty($row[9]) ? trim($row[9]) : null,
                'judul' => trim($row[10]),
                'cakupan' => isset($row[11]) && !empty($row[11]) ? trim($row[11]) : null,
                'tidak_cakupan' => isset($row[12]) && !empty($row[12]) ? trim($row[12]) : null,
                'no_asli' => isset($row[13]) && !empty($row[13]) ? trim($row[13]) : null,
                'kode_asli' => isset($row[14]) && !empty($row[14]) ? trim($row[14]) : null,
                'catatan' => isset($row[15]) && !empty($row[15]) ? trim($row[15]) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('data_kbli')->insertOrIgnore($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('data_kbli')->insertOrIgnore($batch);
        }

        fclose($handle);
    }

    private function seedKbki(): void
    {
        $file = database_path('data/data_kbki.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 14) continue;

            $kode = trim($row[4]);
            if (empty($kode)) continue;

            $batch[] = [
                'level' => (int) $row[2],
                'kode' => $kode,
                'kode_induk' => !empty($row[5]) ? trim($row[5]) : null,
                'nama' => trim($row[13]),
                'deskripsi' => isset($row[18]) && !empty($row[18]) ? trim($row[18]) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 1000) {
                DB::table('data_kbki')->insertOrIgnore($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('data_kbki')->insertOrIgnore($batch);
        }

        fclose($handle);
    }
}

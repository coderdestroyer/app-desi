<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProvinsi();
        $this->seedKabupaten();
        $this->seedKecamatan();
        $this->seedKelurahanDesa();
    }

    private function seedProvinsi(): void
    {
        $file = database_path('data/Sumatera_Provinsi.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;
            $batch[] = [
                'provinsi_id' => (int) $row[0],
                'nama_provinsi' => trim($row[1]),
                'latitude' => isset($row[2]) && $row[2] !== '' ? (float) $row[2] : null,
                'longitude' => isset($row[3]) && $row[3] !== '' ? (float) $row[3] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('provinsi')->insertOrIgnore($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('provinsi')->insertOrIgnore($batch);
        }
        fclose($handle);
    }

    private function seedKabupaten(): void
    {
        $file = database_path('data/Sumatera_Kabupaten.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;
            $batch[] = [
                'kab_id' => (int) $row[0],
                'provinsi_id' => (int) $row[1],
                'nama_kabupaten' => trim($row[2]),
                'latitude' => isset($row[3]) && $row[3] !== '' ? (float) $row[3] : null,
                'longitude' => isset($row[4]) && $row[4] !== '' ? (float) $row[4] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('kabupaten')->insertOrIgnore($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('kabupaten')->insertOrIgnore($batch);
        }
        fclose($handle);
    }

    private function seedKecamatan(): void
    {
        $file = database_path('data/Sumatera_Kecamatan.csv');
        if (!file_exists($file)) return;

        // Load valid kab_id set to ensure FK integrity
        $kabIds = DB::table('kabupaten')->pluck('kab_id')->flip()->toArray();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0]) || empty($row[1])) continue;

            $kecId = (int) $row[0];
            $kabId = (int) $row[1];

            if (!isset($kabIds[$kabId])) continue;

            $batch[] = [
                'id' => $kecId,
                'kabupaten_id' => $kabId,
                'nama_kecamatan' => trim($row[2]),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 1000) {
                DB::table('kecamatan')->insertOrIgnore($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('kecamatan')->insertOrIgnore($batch);
        }
        fclose($handle);
    }

    private function seedKelurahanDesa(): void
    {
        $file = database_path('data/Sumatera_Kelurahan_Desa.csv');
        if (!file_exists($file)) return;

        // Load valid kec_id set to ensure FK integrity
        $kecIds = DB::table('kecamatan')->pluck('id')->flip()->toArray();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0]) || empty($row[1])) continue;

            $desaId = (int) $row[0];
            $kecId = (int) $row[1];

            if (!isset($kecIds[$kecId])) continue;

            $batch[] = [
                'id' => $desaId,
                'kecamatan_id' => $kecId,
                'nama_desa' => trim($row[2]),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 1000) {
                DB::table('kelurahan_desa')->insertOrIgnore($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('kelurahan_desa')->insertOrIgnore($batch);
        }
        fclose($handle);
    }
}

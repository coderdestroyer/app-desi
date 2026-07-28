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
        $this->seedKecamatanAndDesa();
    }

    private function seedProvinsi(): void
    {
        $file = database_path('data/provinsi.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Header

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;
            DB::table('provinsi')->updateOrInsert(
                ['provinsi_id' => (int) $row[0]],
                [
                    'nama_provinsi' => trim($row[1]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        fclose($handle);
    }

    private function seedKabupaten(): void
    {
        $file = database_path('data/kabupaten.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Header

        while (($row = fgetcsv($handle)) !== false) {
            if (empty($row[0])) continue;

            $rawCode = trim($row[0]);
            // Ensure 4-digit formatting like "12.10" -> 1210 instead of "12.1" -> 121
            if (str_contains($rawCode, '.')) {
                $parts = explode('.', $rawCode);
                $parts[1] = str_pad($parts[1], 2, '0', STR_PAD_RIGHT);
                $kabId = (int) ($parts[0] . $parts[1]);
            } else {
                $kabId = (int) $rawCode;
            }

            DB::table('kabupaten')->updateOrInsert(
                ['kab_id' => $kabId],
                [
                    'provinsi_id' => 12, // Sumatera Utara
                    'nama_kabupaten' => trim($row[1]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
        fclose($handle);
    }

    private function seedKecamatanAndDesa(): void
    {
        $file = database_path('data/data wilayah.csv');
        if (!file_exists($file)) return;

        // Load mapping of nama_kabupaten -> kab_id from DB
        $kabMap = DB::table('kabupaten')->pluck('kab_id', 'nama_kabupaten')->toArray();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Read header

        $kecamatanMap = []; // Key: "kabId|namaKecamatan" => kecamatan_id
        $desaBatch = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 8) continue;

            $namaKab = trim($row[2]);
            $kabCode = trim($row[3]); // e.g. "12.01" or "12.1"
            $namaKec = trim($row[4]);
            $namaDesa = trim($row[6]);

            // Resolve kab_id from map by name first for 100% foreign key matching
            if (isset($kabMap[$namaKab])) {
                $kabId = $kabMap[$namaKab];
            } else {
                // Fallback to padded code
                if (str_contains($kabCode, '.')) {
                    $parts = explode('.', $kabCode);
                    $parts[1] = str_pad($parts[1], 2, '0', STR_PAD_RIGHT);
                    $kabId = (int) ($parts[0] . $parts[1]);
                } else {
                    $kabId = (int) $kabCode;
                }
            }

            if (empty($namaKec) || empty($kabId)) continue;

            $kecKey = "{$kabId}|{$namaKec}";

            // Insert / Get Kecamatan ID
            if (!isset($kecamatanMap[$kecKey])) {
                $existingKec = DB::table('kecamatan')
                    ->where('kabupaten_id', $kabId)
                    ->where('nama_kecamatan', $namaKec)
                    ->first();

                if ($existingKec) {
                    $kecId = $existingKec->id;
                } else {
                    $kecId = DB::table('kecamatan')->insertGetId([
                        'kabupaten_id' => $kabId,
                        'nama_kecamatan' => $namaKec,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $kecamatanMap[$kecKey] = $kecId;
            } else {
                $kecId = $kecamatanMap[$kecKey];
            }

            // Collect Desa
            if (!empty($namaDesa)) {
                $desaBatch[] = [
                    'kecamatan_id' => $kecId,
                    'nama_desa' => $namaDesa,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($desaBatch) >= 500) {
                DB::table('kelurahan_desa')->insert($desaBatch);
                $desaBatch = [];
            }
        }

        if (!empty($desaBatch)) {
            DB::table('kelurahan_desa')->insert($desaBatch);
        }

        fclose($handle);
    }
}

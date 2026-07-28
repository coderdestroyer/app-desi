<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PdrbIndikatorSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPdrbSumut();
        $this->seedPdrbKabupaten();
        $this->seedIndikatorProvinsi();
        $this->seedIndikatorKabupaten();
    }

    private function parseKabId(string $rawCode): int
    {
        $rawCode = trim($rawCode);
        if (str_contains($rawCode, '.')) {
            $parts = explode('.', $rawCode);
            $parts[1] = str_pad($parts[1], 2, '0', STR_PAD_RIGHT);
            return (int) ($parts[0] . $parts[1]);
        }
        return (int) $rawCode;
    }

    private function seedPdrbSumut(): void
    {
        $file = database_path('data/pdrb_sumut.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            $batch[] = [
                'sektor_id' => (int) $row[1],
                'tahun' => (int) $row[2],
                'nilai_pdrb' => (float) $row[3],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($batch)) {
            DB::table('pdrb_sumut')->insert($batch);
        }
        fclose($handle);
    }

    private function seedPdrbKabupaten(): void
    {
        $file = database_path('data/pdrb_kabupaten.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            $kabId = $this->parseKabId($row[0]);

            $batch[] = [
                'kabupaten_id' => $kabId,
                'sektor_id' => (int) $row[1],
                'tahun' => (int) $row[2],
                'nilai_pdrb' => (float) $row[3],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('pdrb_kabupaten')->insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('pdrb_kabupaten')->insert($batch);
        }
        fclose($handle);
    }

    private function seedIndikatorProvinsi(): void
    {
        $file = database_path('data/indikator_provinsi.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;

            $provId = (int) $row[1];
            $tahun = (int) $row[3];
            $pertumbuhan = (float) $row[4];
            $kontribusi = (float) $row[5];

            $batch[] = [
                'provinsi_id' => $provId,
                'tahun' => $tahun,
                'nama_indikator' => 'Laju Pertumbuhan PDRB',
                'nilai' => $pertumbuhan,
                'satuan' => '%',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $batch[] = [
                'provinsi_id' => $provId,
                'tahun' => $tahun,
                'nama_indikator' => 'Kontribusi PDRB',
                'nilai' => $kontribusi,
                'satuan' => '%',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($batch)) {
            DB::table('indikator_provinsi')->insert($batch);
        }
        fclose($handle);
    }

    private function seedIndikatorKabupaten(): void
    {
        $file = database_path('data/indikator_kabupaten.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;

            $kabId = $this->parseKabId($row[1]);
            $tahun = (int) $row[3];
            $pertumbuhan = (float) $row[4];
            $kontribusi = (float) $row[5];

            $batch[] = [
                'kabupaten_id' => $kabId,
                'tahun' => $tahun,
                'nama_indikator' => 'Laju Pertumbuhan PDRB',
                'nilai' => $pertumbuhan,
                'satuan' => '%',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $batch[] = [
                'kabupaten_id' => $kabId,
                'tahun' => $tahun,
                'nama_indikator' => 'Kontribusi PDRB',
                'nilai' => $kontribusi,
                'satuan' => '%',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('indikator_kabupaten')->insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('indikator_kabupaten')->insert($batch);
        }
        fclose($handle);
    }
}

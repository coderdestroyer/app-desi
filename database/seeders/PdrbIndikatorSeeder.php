<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PdrbIndikatorSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPdbNasional();
        $this->seedPdrbSumateraProvinsi();
        $this->seedPdrbSumateraKabupaten();
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

    private function seedPdbNasional(): void
    {
        $file = database_path('data/PDB_INDO.csv');
        if (!file_exists($file)) return;

        $sektorIds = DB::table('sektor')->pluck('sektor_id')->flip()->toArray();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            $sektorId = (int) $row[1];
            if (!empty($sektorIds) && !isset($sektorIds[$sektorId])) continue;

            $batch[] = [
                'kode_wilayah' => trim($row[0]),
                'sektor_id' => $sektorId,
                'tahun' => (int) $row[2],
                'nilai' => (float) $row[3],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($batch)) {
            DB::table('pdb_nasional')->insert($batch);
        }
        fclose($handle);
    }

    private function seedPdrbSumateraProvinsi(): void
    {
        $file = database_path('data/Sumatera_PDRB_Provinsi.csv');
        if (!file_exists($file)) return;

        $provIds = DB::table('provinsi')->pluck('provinsi_id')->flip()->toArray();
        $sektorIds = DB::table('sektor')->pluck('sektor_id')->flip()->toArray();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            $provId = (int) $row[0];
            $sektorId = (int) $row[1];
            $tahun = (int) $row[2];
            $nilai = (float) $row[3];

            if (!empty($provIds) && !isset($provIds[$provId])) continue;
            if (!empty($sektorIds) && !isset($sektorIds[$sektorId])) continue;

            $batch[] = [
                'provinsi_id' => $provId,
                'sektor_id' => $sektorId,
                'tahun' => $tahun,
                'nilai_pdrb' => $nilai,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('pdrb_sumatera_provinsi')->insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('pdrb_sumatera_provinsi')->insert($batch);
        }
        fclose($handle);
    }

    private function seedPdrbSumateraKabupaten(): void
    {
        $file = database_path('data/Sumatera_PDRB_Kabupaten.csv');
        if (!file_exists($file)) return;

        $kabIds = DB::table('kabupaten')->pluck('kab_id')->flip()->toArray();
        $sektorIds = DB::table('sektor')->pluck('sektor_id')->flip()->toArray();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            $kabId = $this->parseKabId($row[0]);
            $sektorId = (int) $row[1];
            $tahun = (int) $row[2];
            $nilai = (float) $row[3];

            if (!empty($kabIds) && !isset($kabIds[$kabId])) continue;
            if (!empty($sektorIds) && !isset($sektorIds[$sektorId])) continue;

            $batch[] = [
                'kabupaten_id' => $kabId,
                'sektor_id' => $sektorId,
                'tahun' => $tahun,
                'nilai_pdrb' => $nilai,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= 500) {
                DB::table('pdrb_sumatera_kabupaten')->insert($batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            DB::table('pdrb_sumatera_kabupaten')->insert($batch);
        }
        fclose($handle);
    }

    private function seedIndikatorProvinsi(): void
    {
        $file = database_path('data/indikator_provinsi.csv');
        if (!file_exists($file)) return;

        $provIds = DB::table('provinsi')->pluck('provinsi_id')->flip()->toArray();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;

            $provId = (int) $row[1];
            if (!empty($provIds) && !isset($provIds[$provId])) continue;

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

        $kabIds = DB::table('kabupaten')->pluck('kab_id')->flip()->toArray();

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batch = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;

            $kabId = $this->parseKabId($row[1]);
            if (!empty($kabIds) && !isset($kabIds[$kabId])) continue;

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

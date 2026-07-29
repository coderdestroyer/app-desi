<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HasilAnalisisSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedHasilLq();
        $this->seedHasilSsa();
        $this->seedTipologiKlassen();
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

    private function seedHasilLq(): void
    {
        $file = database_path('data/hasil_lq.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batchLq = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue;

            $kabId = $this->parseKabId($row[1]);
            $sektorId = (int) $row[2];
            $tahun = (int) $row[3];
            $nilaiLq = (float) $row[4];
            $kategori = trim($row[5]);

            $batchLq[] = [
                'kab_id' => $kabId,
                'sektor_id' => $sektorId,
                'tahun' => $tahun,
                'nilai_lq' => $nilaiLq,
                'kategori' => $kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($batchLq)) {
            DB::table('hasil_lq')->insertOrIgnore($batchLq);
        }

        fclose($handle);
    }

    private function seedHasilSsa(): void
    {
        $file = database_path('data/hasil_ssa.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batchSsa = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 10) continue;

            $kabId = $this->parseKabId($row[1]);
            $sektorId = (int) $row[2];
            $tahun = (int) $row[3];
            $rn = (float) $row[4];
            $mij = (float) $row[7];
            $cij = (float) $row[8];

            $batchSsa[] = [
                'kab_id' => $kabId,
                'sektor_id' => $sektorId,
                'tahun' => $tahun,
                'rn' => $rn,
                'mij' => $mij,
                'cij' => $cij,
                'komponen_n' => $rn,
                'komponen_p' => $mij,
                'komponen_d' => $cij,
                'total_shift' => $rn + $mij + $cij,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($batchSsa)) {
            DB::table('hasil_ssa')->insertOrIgnore($batchSsa);
        }

        fclose($handle);
    }

    private function seedTipologiKlassen(): void
    {
        $file = database_path('data/hasil_tipologi_klassen.csv');
        if (!file_exists($file)) return;

        $handle = fopen($file, 'r');
        $header = fgetcsv($handle);

        $batchTipologi = [];
        $batchKlassen = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 13) continue;

            $kabId = $this->parseKabId($row[7]);
            $sektorId = (int) $row[8];
            $tahun = (int) $row[3];
            $kuadran = trim($row[4]);
            $pertumbuhanKab = (float) $row[9];
            $kontribusiKab = (float) $row[10];

            $batchTipologi[] = [
                'kab_id' => $kabId,
                'sektor_id' => $sektorId,
                'tahun' => $tahun,
                'kuadran' => $kuadran,
                'kategori_sektor' => $kuadran,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $batchKlassen[] = [
                'kab_id' => $kabId,
                'sektor_id' => $sektorId,
                'tahun' => $tahun,
                'pertumbuhan_kabupaten' => $pertumbuhanKab,
                'kontribusi_kabupaten' => $kontribusiKab,
                'laju_pertumbuhan' => $pertumbuhanKab,
                'kontribusi_pdrb' => $kontribusiKab,
                'kuadran' => $kuadran,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($batchTipologi)) {
            DB::table('hasil_tipologi_sektor')->insertOrIgnore($batchTipologi);
        }

        if (!empty($batchKlassen)) {
            DB::table('hasil_tipologi_klassen')->insertOrIgnore($batchKlassen);
        }

        fclose($handle);
    }
}


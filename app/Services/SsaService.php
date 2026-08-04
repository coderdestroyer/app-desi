<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SsaService extends BaseAnalysisService
{
    /**
     * Hitung Shift-Share Analysis secara dinamis (On-The-Fly) dari data PDRB
     */
    public function calculateSsa(int $kabId, int $tahun): Collection
    {
        $tahunLalu = $tahun - 1;
        $provinsiId = $this->getProvinsiId($kabId);

        $totalProvinsiSekarang = $this->getTotalProvinsi($provinsiId, $tahun);
        $totalProvinsiLalu = $this->getTotalProvinsi($provinsiId, $tahunLalu);

        if ($totalProvinsiLalu <= 0) {
            return collect();
        }

        $rn = $this->calculateGrowth($totalProvinsiSekarang, $totalProvinsiLalu);

        $kabupatenSekarang = $this->getPdrbKabupatenByTahun($kabId, $tahun)->keyBy('sektor_id');
        $kabupatenLalu = $this->getPdrbKabupatenByTahun($kabId, $tahunLalu)->keyBy('sektor_id');

        $provinsiSekarang = $this->getPdrbProvinsiByTahun($provinsiId, $tahun)->keyBy('sektor_id');
        $provinsiLalu = $this->getPdrbProvinsiByTahun($provinsiId, $tahunLalu)->keyBy('sektor_id');

        $rows = [];

        foreach ($kabupatenSekarang as $sektorId => $kabSekarang) {
            $kabLalu = $kabupatenLalu->get($sektorId);
            $provSekarang = $provinsiSekarang->get($sektorId);
            $provLalu = $provinsiLalu->get($sektorId);

            if (!$kabLalu || !$provSekarang || !$provLalu) {
                continue;
            }

            $nilaiKabSekarang = $kabSekarang->nilai_pdrb ?? 0;
            $nilaiKabLalu = $kabLalu->nilai_pdrb ?? 0;
            $nilaiProvSekarang = $provSekarang->nilai_pdrb ?? 0;
            $nilaiProvLalu = $provLalu->nilai_pdrb ?? 0;

            if ($this->hasNullValue($nilaiKabSekarang, $nilaiKabLalu, $nilaiProvSekarang, $nilaiProvLalu)) {
                continue;
            }

            $rij = $this->calculateGrowth($nilaiKabSekarang, $nilaiKabLalu);
            $rin = $this->calculateGrowth($nilaiProvSekarang, $nilaiProvLalu);

            $yij = $nilaiKabLalu;

            $nij = $yij * $rn;
            $mij = $yij * ($rin - $rn);
            $cij = $yij * ($rij - $rin);
            $dij = $nij + $mij + $cij;

            $kategoriPertumbuhan = $dij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat';
            $kategoriDayaSaing = $cij >= 0 ? 'Daya Saing Baik' : 'Tidak Dapat Bersaing';

            $rows[] = [
                'kab_id' => $kabId,
                'sektor_id' => $sektorId,
                'sektor' => $kabSekarang->sektor,
                'tahun' => $tahun,
                'rn' => round($rn, 6),
                'rin' => round($rin, 6),
                'rij' => round($rij, 6),
                'nij' => round($nij, 2),
                'mij' => round($mij, 2),
                'cij' => round($cij, 2),
                'dij' => round($dij, 2),
                'komponen_n' => round($nij, 2),
                'komponen_p' => round($mij, 2),
                'komponen_d' => round($cij, 2),
                'total_shift' => round($dij, 2),
                'kategori_pertumbuhan' => $kategoriPertumbuhan,
                'kategori_daya_saing' => $kategoriDayaSaing,
            ];
        }

        return collect($rows);
    }
}

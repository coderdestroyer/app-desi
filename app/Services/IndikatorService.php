<?php

namespace App\Services;

use Illuminate\Support\Collection;

class IndikatorService extends BaseAnalysisService
{
    /**
     * Hitung Indikator Kabupaten (Laju Pertumbuhan & Kontribusi) secara dinamis
     */
    public function getIndikatorKabupaten(int $kabId, int $tahun): Collection
    {
        $tahunLalu = $tahun - 1;
        $totalKabupatenSekarang = $this->getTotalKabupaten($kabId, $tahun);
        $totalKabupatenLalu = $this->getTotalKabupaten($kabId, $tahunLalu);

        if ($totalKabupatenSekarang <= 0) {
            return collect();
        }

        $kabupatenSekarang = $this->getPdrbKabupatenByTahun($kabId, $tahun)->keyBy('sektor_id');
        $kabupatenLalu = $this->getPdrbKabupatenByTahun($kabId, $tahunLalu)->keyBy('sektor_id');

        $rows = [];

        foreach ($kabupatenSekarang as $sektorId => $sektor) {
            $sektorLalu = $kabupatenLalu->get($sektorId);
            $nilaiSekarang = $sektor->nilai_pdrb ?? 0;
            $nilaiLalu = $sektorLalu->nilai_pdrb ?? 0;

            $pertumbuhan = $nilaiLalu > 0 ? (($nilaiSekarang - $nilaiLalu) / $nilaiLalu) * 100 : 0;
            $kontribusi = $totalKabupatenSekarang > 0 ? ($nilaiSekarang / $totalKabupatenSekarang) * 100 : 0;

            $rows[] = [
                'kab_id' => $kabId,
                'sektor_id' => $sektorId,
                'sektor' => $sektor->sektor,
                'tahun' => $tahun,
                'pertumbuhan' => round($pertumbuhan, 2),
                'kontribusi' => round($kontribusi, 2),
            ];
        }

        return collect($rows);
    }

    /**
     * Hitung Indikator Provinsi (Laju Pertumbuhan & Kontribusi) secara dinamis
     */
    public function getIndikatorProvinsi(int $provinsiId, int $tahun): Collection
    {
        $tahunLalu = $tahun - 1;
        $totalProvinsiSekarang = $this->getTotalProvinsi($provinsiId, $tahun);
        $totalProvinsiLalu = $this->getTotalProvinsi($provinsiId, $tahunLalu);

        if ($totalProvinsiSekarang <= 0) {
            return collect();
        }

        $provinsiSekarang = $this->getPdrbProvinsiByTahun($provinsiId, $tahun)->keyBy('sektor_id');
        $provinsiLalu = $this->getPdrbProvinsiByTahun($provinsiId, $tahunLalu)->keyBy('sektor_id');

        $rows = [];

        foreach ($provinsiSekarang as $sektorId => $sektor) {
            $sektorLalu = $provinsiLalu->get($sektorId);
            $nilaiSekarang = $sektor->nilai_pdrb ?? 0;
            $nilaiLalu = $sektorLalu->nilai_pdrb ?? 0;

            $pertumbuhan = $nilaiLalu > 0 ? (($nilaiSekarang - $nilaiLalu) / $nilaiLalu) * 100 : 0;
            $kontribusi = $totalProvinsiSekarang > 0 ? ($nilaiSekarang / $totalProvinsiSekarang) * 100 : 0;

            $rows[] = [
                'provinsi_id' => $provinsiId,
                'sektor_id' => $sektorId,
                'sektor' => $sektor->sektor,
                'tahun' => $tahun,
                'pertumbuhan' => round($pertumbuhan, 2),
                'kontribusi' => round($kontribusi, 2),
            ];
        }

        return collect($rows);
    }
}
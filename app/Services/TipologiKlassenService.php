<?php

namespace App\Services;

use Illuminate\Support\Collection;

class TipologiKlassenService extends BaseAnalysisService
{
    /**
     * Hitung Tipologi Klassen Kabupaten secara dinamis (On-The-Fly) dari data PDRB
     */
    public function calculateKlassen(int $kabId, int $tahun): Collection
    {
        $tahunLalu = $tahun - 1;
        $provinsiId = $this->getProvinsiId($kabId);

        $totalKabupatenSekarang = $this->getTotalKabupaten($kabId, $tahun);
        $totalKabupatenLalu = $this->getTotalKabupaten($kabId, $tahunLalu);

        $totalProvinsiSekarang = $this->getTotalProvinsi($provinsiId, $tahun);
        $totalProvinsiLalu = $this->getTotalProvinsi($provinsiId, $tahunLalu);

        if ($totalKabupatenSekarang <= 0 || $totalProvinsiSekarang <= 0 || $totalKabupatenLalu <= 0 || $totalProvinsiLalu <= 0) {
            return collect();
        }

        // Laju Pertumbuhan Total PDRB Wilayah Acuan (r)
        $r_provinsi = $this->calculateGrowth($totalProvinsiSekarang, $totalProvinsiLalu);

        $kabupatenSekarang = $this->getPdrbKabupatenByTahun($kabId, $tahun)->keyBy('sektor_id');
        $kabupatenLalu = $this->getPdrbKabupatenByTahun($kabId, $tahunLalu)->keyBy('sektor_id');

        $provinsiSekarang = $this->getPdrbProvinsiByTahun($provinsiId, $tahun)->keyBy('sektor_id');

        $rows = [];

        foreach ($kabupatenSekarang as $sektorId => $kabSekarang) {
            $kabLalu = $kabupatenLalu->get($sektorId);
            $provSekarang = $provinsiSekarang->get($sektorId);

            if (!$kabLalu || !$provSekarang) {
                continue;
            }

            $nilaiKabSekarang = $kabSekarang->nilai_pdrb ?? 0;
            $nilaiKabLalu = $kabLalu->nilai_pdrb ?? 0;
            $nilaiProvSekarang = $provSekarang->nilai_pdrb ?? 0;

            // Laju Pertumbuhan Sektor i di Kabupaten (r_i)
            $r_i = $this->calculateGrowth($nilaiKabSekarang, $nilaiKabLalu);

            // Kontribusi/Share Sektor i di Kabupaten (y_i)
            $y_i = $totalKabupatenSekarang > 0 ? ($nilaiKabSekarang / $totalKabupatenSekarang) : 0;

            // Kontribusi/Share Sektor i di Provinsi (y)
            $y_provinsi = $totalProvinsiSekarang > 0 ? ($nilaiProvSekarang / $totalProvinsiSekarang) : 0;

            $kuadran = $this->determineTipologiKlassenQuadrant($r_i, $r_provinsi, $y_i, $y_provinsi);

            $klasifikasiMap = [
                'Kuadran I' => 'Sektor Maju dan Tumbuh Pesat',
                'Kuadran II' => 'Sektor Maju tapi Tertekan',
                'Kuadran III' => 'Sektor Berkembang Cepat / Potensial',
                'Kuadran IV' => 'Sektor Relatif Tertinggal',
            ];

            $rows[] = [
                'kab_id' => $kabId,
                'sektor_id' => $sektorId,
                'sektor' => $kabSekarang->sektor,
                'tahun' => $tahun,
                'laju_pertumbuhan' => round($r_i * 100, 2),
                'laju_pertumbuhan_acuan' => round($r_provinsi * 100, 2),
                'kontribusi_pdrb' => round($y_i * 100, 2),
                'kontribusi_acuan' => round($y_provinsi * 100, 2),
                'kuadran' => $kuadran,
                'klasifikasi_sektor' => $klasifikasiMap[$kuadran] ?? $kuadran,
            ];
        }

        return collect($rows);
    }

    /**
     * Hitung Tipologi Klassen Provinsi secara dinamis (Pembanding: PDB Nasional)
     */
    public function calculateKlassenProvinsi(int $provinsiId, int $tahun): Collection
    {
        $tahunLalu = $tahun - 1;

        $totalProvinsiSekarang = $this->getTotalProvinsi($provinsiId, $tahun);
        $totalProvinsiLalu = $this->getTotalProvinsi($provinsiId, $tahunLalu);

        $totalNasionalSekarang = $this->getTotalNasional($tahun);
        $totalNasionalLalu = $this->getTotalNasional($tahunLalu);

        if ($totalProvinsiSekarang <= 0 || $totalNasionalSekarang <= 0 || $totalProvinsiLalu <= 0 || $totalNasionalLalu <= 0) {
            return collect();
        }

        $r_nasional = $this->calculateGrowth($totalNasionalSekarang, $totalNasionalLalu);

        $provinsiSekarang = $this->getPdrbProvinsiByTahun($provinsiId, $tahun)->keyBy('sektor_id');
        $provinsiLalu = $this->getPdrbProvinsiByTahun($provinsiId, $tahunLalu)->keyBy('sektor_id');

        $nasionalSekarang = $this->getPdbNasionalByTahun($tahun)->keyBy('sektor_id');

        $rows = [];

        foreach ($provinsiSekarang as $sektorId => $provSekarang) {
            $provLalu = $provinsiLalu->get($sektorId);
            $nasSekarang = $nasionalSekarang->get($sektorId);

            if (!$provLalu || !$nasSekarang) {
                continue;
            }

            $nilaiProvSekarang = $provSekarang->nilai_pdrb ?? 0;
            $nilaiProvLalu = $provLalu->nilai_pdrb ?? 0;
            $nilaiNasSekarang = $nasSekarang->nilai ?? 0;

            $r_i = $this->calculateGrowth($nilaiProvSekarang, $nilaiProvLalu);

            $y_i = $totalProvinsiSekarang > 0 ? ($nilaiProvSekarang / $totalProvinsiSekarang) : 0;
            $y_nasional = $totalNasionalSekarang > 0 ? ($nilaiNasSekarang / $totalNasionalSekarang) : 0;

            $kuadran = $this->determineTipologiKlassenQuadrant($r_i, $r_nasional, $y_i, $y_nasional);

            $klasifikasiMap = [
                'Kuadran I' => 'Sektor Maju dan Tumbuh Pesat',
                'Kuadran II' => 'Sektor Maju tapi Tertekan',
                'Kuadran III' => 'Sektor Berkembang Cepat / Potensial',
                'Kuadran IV' => 'Sektor Relatif Tertinggal',
            ];

            $rows[] = [
                'provinsi_id' => $provinsiId,
                'sektor_id' => $sektorId,
                'sektor' => $provSekarang->sektor,
                'tahun' => $tahun,
                'laju_pertumbuhan' => round($r_i * 100, 2),
                'laju_pertumbuhan_acuan' => round($r_nasional * 100, 2),
                'kontribusi_pdrb' => round($y_i * 100, 2),
                'kontribusi_acuan' => round($y_nasional * 100, 2),
                'kuadran' => $kuadran,
                'klasifikasi_sektor' => $klasifikasiMap[$kuadran] ?? $kuadran,
            ];
        }

        return collect($rows);
    }
}
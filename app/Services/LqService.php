<?php

namespace App\Services;

use App\Models\Kabupaten;
use App\Models\PdrbKabupaten;
use App\Models\PdrbSumateraProvinsi;
use Illuminate\Support\Collection;

class LqService extends BaseAnalysisService
{
    /**
     * Hitung LQ secara langsung (On-The-Fly) dari data PDRB
     */
    public function calculateLq(int $kabId, int $tahun): Collection
    {
        $provinsiId = $this->getProvinsiId($kabId);
        $totalKabupaten = $this->getTotalKabupaten($kabId, $tahun);
        $totalProvinsi = $this->getTotalProvinsi($provinsiId, $tahun);

        if ($totalKabupaten <= 0 || $totalProvinsi <= 0) {
            return collect();
        }

        $dataKabupaten = $this->getPdrbKabupaten($kabId, $tahun);
        $dataProvinsi = $this->getPdrbProvinsiByTahun($provinsiId, $tahun)->keyBy('sektor_id');

        $rows = [];

        foreach ($dataKabupaten as $item) {
            $provinsi = $dataProvinsi->get($item->sektor_id);
            if (!$provinsi || $provinsi->nilai_pdrb == 0) {
                continue;
            }

            $persenKabupaten = $item->nilai_pdrb / $totalKabupaten;
            $persenProvinsi = $provinsi->nilai_pdrb / $totalProvinsi;

            if ($persenProvinsi == 0) {
                continue;
            }

            $nilaiLq = round($persenKabupaten / $persenProvinsi, 4);
            $kategori = $nilaiLq >= 1 ? 'Basis' : 'Non Basis';

            $rows[] = [
                'kab_id' => $kabId,
                'sektor_id' => $item->sektor_id,
                'sektor' => $item->sektor,
                'tahun' => $tahun,
                'nilai_lq' => $nilaiLq,
                'kategori' => $kategori,
                'persen_kabupaten' => round($persenKabupaten * 100, 2),
                'persen_provinsi' => round($persenProvinsi * 100, 2),
            ];
        }

        return collect($rows);
    }
}
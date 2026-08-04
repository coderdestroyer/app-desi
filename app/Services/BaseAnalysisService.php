<?php

namespace App\Services;

use App\Models\Kabupaten;
use App\Models\PdrbKabupaten;
use App\Models\PdrbSumut;
use App\Models\PdbNasional;
use App\Services\Concerns\DeterminesQuadrants;

class BaseAnalysisService
{
    use DeterminesQuadrants;

    protected static array $provinsiIdCache = [];
    protected static array $totalProvinsiCache = [];
    protected static array $totalNasionalCache = [];
    protected static array $pdrbProvinsiByTahunCache = [];

    /**
     * Ambil provinsi dari kabupaten
     */
    protected function getProvinsiId(int $kabId): int
    {
        if (! isset(self::$provinsiIdCache[$kabId])) {
            self::$provinsiIdCache[$kabId] = Kabupaten::where('kab_id', $kabId)->value('provinsi_id') ?? 12;
        }

        return self::$provinsiIdCache[$kabId];
    }

    /**
     * Total PDRB Kabupaten
     */
    protected function getTotalKabupaten(int $kabId, int $tahun): float
    {
        return (float) PdrbKabupaten::where('kabupaten_id', $kabId)
            ->where('tahun', $tahun)
            ->sum('nilai_pdrb');
    }

    /**
     * Total PDRB Provinsi
     */
    protected function getTotalProvinsi(int $provinsiId, int $tahun): float
    {
        $key = $provinsiId . '_' . $tahun;
        if (! isset(self::$totalProvinsiCache[$key])) {
            self::$totalProvinsiCache[$key] = (float) PdrbSumut::where('provinsi_id', $provinsiId)
                ->where('tahun', $tahun)
                ->sum('nilai_pdrb');
        }

        return self::$totalProvinsiCache[$key];
    }

    /**
     * Total PDB Nasional
     */
    protected function getTotalNasional(int $tahun): float
    {
        if (! isset(self::$totalNasionalCache[$tahun])) {
            self::$totalNasionalCache[$tahun] = (float) PdbNasional::where('tahun', $tahun)->sum('nilai');
        }

        return self::$totalNasionalCache[$tahun];
    }

    /**
     * Seluruh sektor kabupaten
     */
    protected function getPdrbKabupaten(int $kabId, int $tahun)
    {
        return PdrbKabupaten::with('sektor')
            ->where('kabupaten_id', $kabId)
            ->where('tahun', $tahun)
            ->get();
    }

    /**
     * PDRB sektor provinsi
     */
    protected function getPdrbProvinsi(int $provinsiId, int $sektorId, int $tahun)
    {
        return PdrbSumut::where('provinsi_id', $provinsiId)
            ->where('sektor_id', $sektorId)
            ->where('tahun', $tahun)
            ->first();
    }

    /**
     * PDRB sektor kabupaten
     */
    protected function getPdrbKabupatenBySektor(int $kabId, int $sektorId, int $tahun)
    {
        return PdrbKabupaten::where('kabupaten_id', $kabId)
            ->where('sektor_id', $sektorId)
            ->where('tahun', $tahun)
            ->first();
    }

    /**
     * Pertumbuhan
     */
    protected function calculateGrowth(float $current, float $previous): float
    {
        if ($previous == 0) {
            return 0;
        }

        return ($current - $previous) / $previous;
    }

    protected function calculateContribution(float $sector, float $total): float
    {
        if ($total == 0) {
            return 0;
        }

        return $sector / $total;
    }

    protected function getPdrbProvinsiByTahun(int $provinsiId, int $tahun)
    {
        $key = $provinsiId . '_' . $tahun;
        if (! isset(self::$pdrbProvinsiByTahunCache[$key])) {
            self::$pdrbProvinsiByTahunCache[$key] = PdrbSumut::with('sektor')
                ->where('provinsi_id', $provinsiId)
                ->where('tahun', $tahun)
                ->get();
        }

        return self::$pdrbProvinsiByTahunCache[$key];
    }

    protected function getPdrbKabupatenByTahun(int $kabId, int $tahun)
    {
        return PdrbKabupaten::with('sektor')
            ->where('kabupaten_id', $kabId)
            ->where('tahun', $tahun)
            ->get();
    }

    protected function getPdbNasionalByTahun(int $tahun)
    {
        return PdbNasional::with('sektor')
            ->where('tahun', $tahun)
            ->get();
    }

    protected function buildIndicatorRow(array &$rows, array $attributes, array $values): void
    {
        $rows[] = array_merge(
            $attributes,
            $values,
            $this->timestamp()
        );
    }

    protected function timestamp(): array
    {
        return [
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    protected function hasNullValue(...$values): bool
    {
        foreach ($values as $value) {
            if (is_null($value)) {
                return true;
            }
        }

        return false;
    }
}
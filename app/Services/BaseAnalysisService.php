<?php

namespace App\Services;

use App\Models\Kabupaten;
use App\Models\PdrbKabupaten;
use App\Models\PdrbSumut;
use App\Services\Concerns\DeterminesQuadrants;


class BaseAnalysisService
{
    use DeterminesQuadrants;

    /**
     * Ambil provinsi dari kabupaten
     */
    protected function getProvinsiId(int $kabId): int
    {
        return Kabupaten::findOrFail($kabId)->provinsi_id;
    }

    /**
     * Total PDRB Kabupaten
     */
    protected function getTotalKabupaten(
        int $kabId,
        int $tahun
    ): float {

        return (float) PdrbKabupaten::where(function ($q) use ($kabId) {
                $q->where('kabupaten_id', $kabId)->orWhere('kab_id', $kabId);
            })
            ->where('tahun', $tahun)
            ->sum('nilai_pdrb');

    }

    /**
     * Total PDRB Provinsi
     */
    protected function getTotalProvinsi(
        int $provinsiId,
        int $tahun
    ): float {

        return (float) PdrbSumut::where('provinsi_id', $provinsiId)
            ->where('tahun', $tahun)
            ->sum('nilai_pdrb');

    }

    /**
     * Seluruh sektor kabupaten
     */
    protected function getPdrbKabupaten(
        int $kabId,
        int $tahun
    ) {

        return PdrbKabupaten::with('sektor')
            ->where(function ($q) use ($kabId) {
                $q->where('kabupaten_id', $kabId)->orWhere('kab_id', $kabId);
            })
            ->where('tahun', $tahun)
            ->get();

    }

    /**
     * PDRB sektor provinsi
     */
    protected function getPdrbProvinsi(
        int $provinsiId,
        int $sektorId,
        int $tahun
    ) {

        return PdrbSumut::where('provinsi_id', $provinsiId)
            ->where('sektor_id', $sektorId)
            ->where('tahun', $tahun)
            ->first();

    }

    /**
     * PDRB sektor kabupaten
     */
    protected function getPdrbKabupatenBySektor(
        int $kabId,
        int $sektorId,
        int $tahun
    ) {

        return PdrbKabupaten::where(function ($q) use ($kabId) {
                $q->where('kabupaten_id', $kabId)->orWhere('kab_id', $kabId);
            })
            ->where('sektor_id', $sektorId)
            ->where('tahun', $tahun)
            ->first();

    }

    /**
     * Pertumbuhan
     */
    protected function CalculateGrowth(
        float $current,
        float $previous
    ): float {

        if($previous == 0){
            return 0;
        }

        return ($current - $previous) / $previous;

    }

    protected function calculateContribution(
        float $sector,
        float $total
    ): float {
        if ($total == 0) {
            return 0;
        }

        return $sector / $total;
    }

    protected function getPdrbProvinsiByTahun(
        int $provinsiId,
        int $tahun
    )
    {

        return PdrbSumut::

            with('sektor')

            ->where(
                'provinsi_id',
                $provinsiId
            )

            ->where(
                'tahun',
                $tahun
            )

            ->get();

    }

    protected function getPdrbKabupatenByTahun(
        int $kabId,
        int $tahun
    )
    {
        return PdrbKabupaten::with('sektor')
            ->where(function ($q) use ($kabId) {
                $q->where('kabupaten_id', $kabId)->orWhere('kab_id', $kabId);
            })
            ->where('tahun', $tahun)
            ->get();
    }

    protected function buildIndicatorRow(
        array &$rows,
        array $attributes,
        array $values
    ): void
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
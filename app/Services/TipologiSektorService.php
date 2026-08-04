<?php

namespace App\Services;

use Illuminate\Support\Collection;

class TipologiSektorService extends BaseAnalysisService
{
    protected LqService $lqService;
    protected SsaService $ssaService;

    public function __construct(LqService $lqService, SsaService $ssaService)
    {
        $this->lqService = $lqService;
        $this->ssaService = $ssaService;
    }

    /**
     * Hitung Tipologi Sektor Kabupaten secara dinamis (On-The-Fly) dari hasil LQ & SSA
     */
    public function calculateTipologi(int $kabId, int $tahun): Collection
    {
        $hasilLq = $this->lqService->calculateLq($kabId, $tahun)->keyBy('sektor_id');
        $hasilSsa = $this->ssaService->calculateSsa($kabId, $tahun)->keyBy('sektor_id');

        $rows = [];

        foreach ($hasilLq as $sektorId => $lq) {
            $ssa = $hasilSsa->get($sektorId);
            if (!$ssa) {
                continue;
            }

            $nilaiLq = $lq['nilai_lq'];
            $cij = $ssa['cij'];

            $kuadran = $this->determineTipologiSektorQuadrant($nilaiLq, $cij);

            $kategoriMap = [
                'Kuadran I' => 'Maju dan Tumbuh Cepat',
                'Kuadran II' => 'Potensial / Cepat Berkembang',
                'Kuadran III' => 'Maju tapi Tertekan',
                'Kuadran IV' => 'Relatif Tertinggal',
            ];

            $rows[] = [
                'kab_id' => $kabId,
                'sektor_id' => $sektorId,
                'sektor' => $lq['sektor'],
                'tahun' => $tahun,
                'lq' => $nilaiLq,
                'cij' => $cij,
                'kuadran' => $kuadran,
                'kategori_sektor' => $kategoriMap[$kuadran] ?? $kuadran,
            ];
        }

        return collect($rows);
    }

    /**
     * Hitung Tipologi Sektor Provinsi (Pembanding: PDB Nasional) secara dinamis (On-The-Fly)
     */
    public function calculateTipologiProvinsi(int $provinsiId, int $tahun): Collection
    {
        $hasilLq = $this->lqService->calculateLqProvinsi($provinsiId, $tahun)->keyBy('sektor_id');
        $hasilSsa = $this->ssaService->calculateSsaProvinsi($provinsiId, $tahun)->keyBy('sektor_id');

        $rows = [];

        foreach ($hasilLq as $sektorId => $lq) {
            $ssa = $hasilSsa->get($sektorId);
            if (!$ssa) {
                continue;
            }

            $nilaiLq = $lq['nilai_lq'];
            $cij = $ssa['cij'];

            $kuadran = $this->determineTipologiSektorQuadrant($nilaiLq, $cij);

            $kategoriMap = [
                'Kuadran I' => 'Maju dan Tumbuh Cepat',
                'Kuadran II' => 'Potensial / Cepat Berkembang',
                'Kuadran III' => 'Maju tapi Tertekan',
                'Kuadran IV' => 'Relatif Tertinggal',
            ];

            $rows[] = [
                'provinsi_id' => $provinsiId,
                'sektor_id' => $sektorId,
                'sektor' => $lq['sektor'],
                'tahun' => $tahun,
                'lq' => $nilaiLq,
                'cij' => $cij,
                'kuadran' => $kuadran,
                'kategori_sektor' => $kategoriMap[$kuadran] ?? $kuadran,
            ];
        }

        return collect($rows);
    }
}
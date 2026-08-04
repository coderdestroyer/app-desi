<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ComparisonService
{
    protected LqService $lqService;
    protected SsaService $ssaService;
    protected TipologiKlassenService $tipologiKlassenService;
    protected IndikatorService $indikatorService;

    public function __construct(
        LqService $lqService,
        SsaService $ssaService,
        TipologiKlassenService $tipologiKlassenService,
        IndikatorService $indikatorService
    ) {
        $this->lqService = $lqService;
        $this->ssaService = $ssaService;
        $this->tipologiKlassenService = $tipologiKlassenService;
        $this->indikatorService = $indikatorService;
    }

    public function getDashboard(array $filter): array
    {
        $rows = $this->loadData($filter);

        return [
            'summary' => $this->getSummary($rows),
            'charts' => [
                'growth'       => $this->getGrowthChart($rows),
                'contribution' => $this->getContributionChart($rows),
                'lq'           => $this->getLqChart($rows),
                'ssa'          => $this->getSsaChart($rows),
            ],
            'table' => $this->getTrendTable($rows),
        ];
    }

    private function loadData(array $filter): Collection
    {
        $kabId = (int) ($filter['kabupaten'] ?? 0);
        $sektorId = isset($filter['sektor']) ? (int) $filter['sektor'] : null;
        $tahunAwal = (int) ($filter['tahun_awal'] ?? 2021);
        $tahunAkhir = (int) ($filter['tahun_akhir'] ?? 2024);

        if (!$kabId) {
            return collect();
        }

        $results = collect();

        for ($y = $tahunAwal; $y <= $tahunAkhir; $y++) {
            $lqRows = $this->lqService->calculateLq($kabId, $y)->keyBy('sektor_id');
            $ssaRows = $this->ssaService->calculateSsa($kabId, $y)->keyBy('sektor_id');
            $klassenRows = $this->tipologiKlassenService->calculateKlassen($kabId, $y)->keyBy('sektor_id');

            foreach ($klassenRows as $sId => $klassen) {
                if ($sektorId && $sId !== $sektorId) {
                    continue;
                }

                $lq = $lqRows->get($sId);
                $ssa = $ssaRows->get($sId);

                $results->push((object) [
                    'kab_id' => $kabId,
                    'sektor_id' => $sId,
                    'sektor' => (object) ['nama_sektor' => $klassen['sektor']->nama_sektor ?? 'Sektor ' . $sId],
                    'tahun' => $y,
                    'pertumbuhan_kabupaten' => $klassen['laju_pertumbuhan'],
                    'pertumbuhan_provinsi' => $klassen['laju_pertumbuhan_acuan'],
                    'kontribusi_kabupaten' => $klassen['kontribusi_pdrb'],
                    'kontribusi_provinsi' => $klassen['kontribusi_acuan'],
                    'kuadran' => $klassen['kuadran'],
                    'nilai_lq' => $lq['nilai_lq'] ?? 0,
                    'kategori' => $lq['kategori'] ?? 'Non Basis',
                    'dij' => $ssa['dij'] ?? 0,
                    'cij' => $ssa['cij'] ?? 0,
                    'kategori_pertumbuhan' => $ssa['kategori_pertumbuhan'] ?? '-',
                    'kategori_daya_saing' => $ssa['kategori_daya_saing'] ?? '-',
                ]);
            }
        }

        return $results;
    }

    private function getSummary(Collection $rows): array
    {
        return [
            'total_tahun' => $rows->pluck('tahun')->unique()->count(),
            'total_sektor' => $rows->pluck('sektor_id')->unique()->count(),
            'avg_lq' => round($rows->avg('nilai_lq'), 2),
            'avg_growth' => round($rows->avg('pertumbuhan_kabupaten'), 2),
        ];
    }

    private function getGrowthChart(Collection $rows): array
    {
        return [
            'type' => 'line',
            'title' => 'Tren Pertumbuhan PDRB',
            'labels' => $rows->pluck('tahun')->unique()->values()->toArray(),
            'datasets' => [
                [
                    'label' => 'Pertumbuhan Kabupaten (%)',
                    'data' => $rows->pluck('pertumbuhan_kabupaten')->toArray(),
                    'borderColor' => '#3b82f6',
                ],
            ],
        ];
    }

    private function getContributionChart(Collection $rows): array
    {
        return [
            'type' => 'line',
            'title' => 'Tren Kontribusi PDRB',
            'labels' => $rows->pluck('tahun')->unique()->values()->toArray(),
            'datasets' => [
                [
                    'label' => 'Kontribusi Kabupaten (%)',
                    'data' => $rows->pluck('kontribusi_kabupaten')->toArray(),
                    'borderColor' => '#10b981',
                ],
            ],
        ];
    }

    private function getLqChart(Collection $rows): array
    {
        return [
            'type' => 'line',
            'title' => 'Tren Nilai LQ',
            'labels' => $rows->pluck('tahun')->unique()->values()->toArray(),
            'datasets' => [
                [
                    'label' => 'Nilai LQ',
                    'data' => $rows->pluck('nilai_lq')->toArray(),
                    'borderColor' => '#8b5cf6',
                ],
            ],
        ];
    }

    private function getSsaChart(Collection $rows): array
    {
        return [
            'type' => 'bar',
            'title' => 'Komponen Shift-Share',
            'labels' => $rows->pluck('tahun')->unique()->values()->toArray(),
            'datasets' => [
                [
                    'label' => 'Total Shift (Dij)',
                    'data' => $rows->pluck('dij')->toArray(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
        ];
    }

    private function getTrendTable(Collection $rows): array
    {
        return $rows->map(function ($r) {
            return [
                'tahun' => $r->tahun,
                'sektor' => $r->sektor->nama_sektor ?? '-',
                'pertumbuhan' => $r->pertumbuhan_kabupaten . '%',
                'kontribusi' => $r->kontribusi_kabupaten . '%',
                'lq' => $r->nilai_lq,
                'kategori_lq' => $r->kategori,
                'dij' => $r->dij,
                'kuadran' => $r->kuadran,
            ];
        })->toArray();
    }
}
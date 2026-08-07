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
            $lqRows = \App\Models\SummaryLqResult::with('sektor')
                ->where('tingkat_wilayah', 'kabupaten')
                ->where('kabupaten_id', $kabId)
                ->where('tahun', $y)
                ->get()
                ->keyBy('sektor_id');

            $ssaRows = \App\Models\SummaryShiftShareResult::with('sektor')
                ->where('tingkat_wilayah', 'kabupaten')
                ->where('kabupaten_id', $kabId)
                ->where('tahun_akhir', $y)
                ->get()
                ->keyBy('sektor_id');

            $klassenRows = \App\Models\SummaryKlassenResult::with('sektor')
                ->where('tingkat_wilayah', 'kabupaten')
                ->where('kabupaten_id', $kabId)
                ->where('tahun_akhir', $y)
                ->get()
                ->keyBy('sektor_id');

            foreach ($klassenRows as $sId => $klassen) {
                if ($sektorId && $sId !== $sektorId) {
                    continue;
                }

                $lq = $lqRows->get($sId);
                $ssa = $ssaRows->get($sId);

                $results->push((object) [
                    'kab_id' => $kabId,
                    'sektor_id' => $sId,
                    'sektor' => (object) ['nama_sektor' => $klassen->sektor->nama_sektor ?? 'Sektor ' . $sId],
                    'tahun' => $y,
                    'pertumbuhan_kabupaten' => (float)$klassen->growth_daerah,
                    'pertumbuhan_provinsi' => (float)$klassen->growth_pembanding,
                    'kontribusi_kabupaten' => (float)$klassen->share_daerah,
                    'kontribusi_provinsi' => (float)$klassen->share_pembanding,
                    'kuadran' => $klassen->kuadran,
                    'nilai_lq' => $lq ? (float)$lq->nilai_lq : 0,
                    'kategori' => $lq ? $lq->kategori : 'Non Basis',
                    'dij' => $ssa ? (float)$ssa->d_dij : 0,
                    'cij' => $ssa ? (float)$ssa->s_sij : 0,
                    'kategori_pertumbuhan' => $ssa ? ((float)$ssa->d_dij >= 0 ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat') : '-',
                    'kategori_daya_saing' => $ssa ? ((float)$ssa->s_sij >= 0 ? 'Daya Saing Baik' : 'Tidak Dapat Bersaing') : '-',
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
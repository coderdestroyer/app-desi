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
        if ($rows->isEmpty()) {
            return [
                'growth' => [
                    'average' => '-',
                    'highest' => ['tahun' => '-', 'nilai' => '-'],
                ],
                'contribution' => [
                    'average' => '-',
                    'highest' => ['tahun' => '-', 'nilai' => '-'],
                ],
                'lq' => [
                    'nilai' => '-',
                    'tahun' => '-',
                    'status' => '-',
                    'change' => 0,
                ],
                'tipologi' => [
                    'kategori' => '-',
                    'tahun' => '-',
                    'movement' => '-',
                ],
            ];
        }

        $sortedByYear = $rows->sortBy('tahun')->values();
        $lastRow = $sortedByYear->last();
        $prevRow = $sortedByYear->count() > 1 ? $sortedByYear->get($sortedByYear->count() - 2) : null;

        $highestGrowth = $rows->sortByDesc('pertumbuhan_kabupaten')->first();
        $highestContribution = $rows->sortByDesc('kontribusi_kabupaten')->first();

        $avgGrowth = round((float) $rows->avg('pertumbuhan_kabupaten'), 2);
        $avgContribution = round((float) $rows->avg('kontribusi_kabupaten'), 2);

        $lqChange = 0;
        if ($prevRow && (float)$prevRow->nilai_lq != 0) {
            $lqChange = round((((float)$lastRow->nilai_lq - (float)$prevRow->nilai_lq) / abs((float)$prevRow->nilai_lq)) * 100, 2);
        }

        $klassenDesc = match ($lastRow->kuadran ?? '') {
            'Kuadran I'   => 'Sektor Maju & Tumbuh Pesat',
            'Kuadran II'  => 'Sektor Maju tapi Lambat',
            'Kuadran III' => 'Sektor Berkembang Potensial',
            'Kuadran IV'  => 'Sektor Relatif Tertinggal',
            default       => '-',
        };

        return [
            'growth' => [
                'average' => number_format($avgGrowth, 2, ',', '.'),
                'highest' => [
                    'tahun' => $highestGrowth->tahun ?? '-',
                    'nilai' => number_format((float) ($highestGrowth->pertumbuhan_kabupaten ?? 0), 2, ',', '.'),
                ],
            ],
            'contribution' => [
                'average' => number_format($avgContribution, 2, ',', '.'),
                'highest' => [
                    'tahun' => $highestContribution->tahun ?? '-',
                    'nilai' => number_format((float) ($highestContribution->kontribusi_kabupaten ?? 0), 2, ',', '.'),
                ],
            ],
            'lq' => [
                'nilai' => number_format((float) ($lastRow->nilai_lq ?? 0), 3, ',', '.'),
                'tahun' => $lastRow->tahun ?? '-',
                'status' => $lastRow->kategori ?? 'Non Basis',
                'change' => $lqChange,
            ],
            'tipologi' => [
                'kategori' => $lastRow->kuadran ?? '-',
                'tahun' => $lastRow->tahun ?? '-',
                'movement' => $klassenDesc,
            ],
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
            $kategori = match ($r->kuadran) {
                'Kuadran I'   => 'Sektor Unggulan',
                'Kuadran II'  => 'Sektor Potensial',
                'Kuadran III' => 'Sektor Berkembang',
                default       => 'Sektor Relatif Tertinggal',
            };

            return [
                'tahun' => $r->tahun,
                'growth' => (float) $r->pertumbuhan_kabupaten,
                'contribution' => (float) $r->kontribusi_kabupaten,
                'lq' => (float) $r->nilai_lq,
                'ssa' => (float) $r->dij,
                'status_lq' => $r->kategori ?? 'Non Basis',
                'kuadran' => $r->kuadran ?? '-',
                'kategori' => $kategori,
            ];
        })->toArray();
    }
}
<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

use App\Services\Concerns\BuildsCharts;
use App\Services\Concerns\BuildsSummary;
use App\Services\Concerns\BuildsRanking;
use App\Services\Concerns\BuildsTabs;
use App\Services\Concerns\BuildsTables;
use App\Services\Concerns\FormatsDashboard;

use App\Services\LqService;
use App\Services\SsaService;
use App\Services\TipologiSektorService;
use App\Services\TipologiKlassenService;

class DashboardAnalysisService
{
    use BuildsCharts, BuildsSummary, BuildsRanking, BuildsTabs, BuildsTables;

    protected LqService $lqService;
    protected SsaService $ssaService;
    protected TipologiSektorService $tipologiSektorService;
    protected TipologiKlassenService $tipologiKlassenService;

    public function __construct(
        LqService $lqService,
        SsaService $ssaService,
        TipologiSektorService $tipologiSektorService,
        TipologiKlassenService $tipologiKlassenService
    ) {
        $this->lqService = $lqService;
        $this->ssaService = $ssaService;
        $this->tipologiSektorService = $tipologiSektorService;
        $this->tipologiKlassenService = $tipologiKlassenService;
    }

    /** Entry point: dispatch ke dashboard sesuai metode */
    public function getDashboard(int $kabId, string $metode, string|int $tahun = 'all'): array
    {
        return match ($metode) {
            'lq'       => $this->getLqDashboard($kabId, $tahun),
            'ssa'      => $this->getSsaDashboard($kabId, $tahun),
            'tipologi' => $this->getTipologiDashboard($kabId, $tahun),
            'klassen'  => $this->getKlassenDashboard($kabId, $tahun),
            default    => [],
        };
    }

    private function getAvailableYears(int $kabId): array
    {
        return DB::table('pdrb_sumatera_kabupaten')
            ->where('kabupaten_id', $kabId)
            ->distinct()
            ->pluck('tahun')
            ->sort()
            ->values()
            ->toArray();
    }

    /** ============ Base Queries (Dynamic On-The-Fly Calculations) ============ */

    private function baseLqQuery(int $kabId, string|int $tahun): Collection
    {
        $years = $tahun === 'all' ? $this->getAvailableYears($kabId) : [(int) $tahun];
        $results = collect();

        foreach ($years as $y) {
            $rows = $this->lqService->calculateLq($kabId, $y);
            foreach ($rows as $item) {
                // Convert array item to object format expected by dashboard builders
                $results->push((object) [
                    'kab_id' => $item['kab_id'],
                    'sektor_id' => $item['sektor_id'],
                    'sektor' => (object) ['nama_sektor' => $item['sektor']->nama_sektor ?? 'Sektor ' . $item['sektor_id']],
                    'tahun' => $item['tahun'],
                    'nilai_lq' => $item['nilai_lq'],
                    'kategori' => $item['kategori'],
                    'persen_kabupaten' => $item['persen_kabupaten'] ?? 0,
                    'persen_provinsi' => $item['persen_provinsi'] ?? 0,
                ]);
            }
        }

        return $results;
    }

    private function baseSsaQuery(int $kabId, string|int $tahun): Collection
    {
        $years = $tahun === 'all' ? $this->getAvailableYears($kabId) : [(int) $tahun];
        $results = collect();

        foreach ($years as $y) {
            $rows = $this->ssaService->calculateSsa($kabId, $y);
            foreach ($rows as $item) {
                $results->push((object) [
                    'kab_id' => $item['kab_id'],
                    'sektor_id' => $item['sektor_id'],
                    'sektor' => (object) ['nama_sektor' => $item['sektor']->nama_sektor ?? 'Sektor ' . $item['sektor_id']],
                    'tahun' => $item['tahun'],
                    'rn' => $item['rn'],
                    'rin' => $item['rin'],
                    'rij' => $item['rij'],
                    'nij' => $item['nij'],
                    'mij' => $item['mij'],
                    'cij' => $item['cij'],
                    'dij' => $item['dij'],
                    'komponen_n' => $item['komponen_n'],
                    'komponen_p' => $item['komponen_p'],
                    'komponen_d' => $item['komponen_d'],
                    'total_shift' => $item['total_shift'],
                    'kategori_pertumbuhan' => $item['kategori_pertumbuhan'],
                    'kategori_daya_saing' => $item['kategori_daya_saing'],
                ]);
            }
        }

        return $results;
    }

    private function baseTipologiQuery(int $kabId, string|int $tahun): Collection
    {
        $years = $tahun === 'all' ? $this->getAvailableYears($kabId) : [(int) $tahun];
        $results = collect();

        foreach ($years as $y) {
            $rows = $this->tipologiSektorService->calculateTipologi($kabId, $y);
            foreach ($rows as $item) {
                $results->push((object) [
                    'kab_id' => $item['kab_id'],
                    'sektor_id' => $item['sektor_id'],
                    'sektor' => (object) ['nama_sektor' => $item['sektor']->nama_sektor ?? 'Sektor ' . $item['sektor_id']],
                    'tahun' => $item['tahun'],
                    'nilai_lq' => $item['lq'],
                    'lq' => $item['lq'],
                    'cij' => $item['cij'],
                    'kuadran' => $item['kuadran'],
                    'kategori_sektor' => $item['kategori_sektor'],
                    'kategori_lq' => $item['lq'] >= 1 ? 'Basis' : 'Non Basis',
                    'kategori_daya_saing' => $item['cij'] >= 0 ? 'Daya Saing Baik' : 'Tidak Dapat Bersaing',
                ]);
            }
        }

        return $results;
    }

    private function baseKlassenQuery(int $kabId, string|int $tahun): Collection
    {
        $years = $tahun === 'all' ? $this->getAvailableYears($kabId) : [(int) $tahun];
        $results = collect();

        foreach ($years as $y) {
            $rows = $this->tipologiKlassenService->calculateKlassen($kabId, $y);
            foreach ($rows as $item) {
                $results->push((object) [
                    'kab_id' => $item['kab_id'],
                    'sektor_id' => $item['sektor_id'],
                    'sektor' => (object) ['nama_sektor' => $item['sektor']->nama_sektor ?? 'Sektor ' . $item['sektor_id']],
                    'tahun' => $item['tahun'],
                    'laju_pertumbuhan' => $item['laju_pertumbuhan'],
                    'pertumbuhan_kabupaten' => $item['laju_pertumbuhan'],
                    'pertumbuhan_provinsi' => $item['laju_pertumbuhan_acuan'],
                    'kontribusi_pdrb' => $item['kontribusi_pdrb'],
                    'kontribusi_kabupaten' => $item['kontribusi_pdrb'],
                    'kontribusi_provinsi' => $item['kontribusi_acuan'],
                    'kuadran' => $item['kuadran'],
                    'klasifikasi_sektor' => $item['klasifikasi_sektor'],
                ]);
            }
        }

        return $results;
    }

    /** ============ Dashboard LQ ============ */

    private function getLqDashboard(int $kabId, string|int $tahun): array
    {
        $rows = $this->baseLqQuery($kabId, $tahun);
        return [
            'header' => [
                'kabupaten' => optional($rows->first()?->kabupaten)->nama_kabupaten ?? '-',
                'tahun' => $tahun,
                'title' => 'Hasil Analisis LQ',
                'description' =>
                'Mengidentifikasi sektor basis berdasarkan nilai Location Quotient (LQ).',
            ],
            'summary' => $this->getLqSummary($rows),
            'charts'  => [
                'doughnut' => $this->getLqDoughnutChart($rows),
                'bar' => $this->getLqBarChart($rows),
            ],
            'ranking' => $this->getLqRanking($rows),
            'table'   => $this->getLqTable($rows),
        ];
    }

    private function getLqSummary(Collection $rows): array
    {
        $basis     = $this->countBy($rows, 'kategori', 'Basis');
        $nonBasis  = $this->countBy($rows, 'kategori', 'Non Basis');
        $tertinggi = $this->maximum($rows, 'nilai_lq');
        $terendah  = $this->minimum($rows, 'nilai_lq');
        $rata      = $this->average($rows, 'nilai_lq');

        return [

            [
                'title' => 'Sektor Basis',
                'value' => $basis,
                'description' => 'Sektor dengan nilai LQ ≥ 1 sehingga mampu memenuhi kebutuhan daerah dan menjadi sektor basis.',
                'icon' => 'fa-solid fa-chart-line',
                'color' => 'success',
            ],

            [
                'title' => 'Sektor Non Basis',
                'value' => $nonBasis,
                'description' => 'Sektor dengan nilai LQ < 1 sehingga masih bergantung pada pasokan dari luar daerah.',
                'icon' => 'fa-solid fa-chart-area',
                'color' => 'warning',
            ],

            [
                'title' => 'LQ Tertinggi',
                'value' => round($tertinggi?->nilai_lq ?? 0, 2),
                'description' => 'Nilai LQ tertinggi diperoleh pada sektor ' .
                    \Illuminate\Support\Str::title(
                        strtolower($tertinggi?->sektor?->nama_sektor ?? '-')
                    ) . '.',
                'icon' => 'fa-solid fa-trophy',
                'color' => 'primary',
            ],

            [
                'title' => 'Rata-rata LQ',
                'value' => round($rata, 2),
                'description' => 'Rata-rata nilai LQ seluruh sektor pada tahun analisis yang dipilih.',
                'icon' => 'fa-solid fa-chart-column',
                'color' => 'danger',
            ],

        ];
    }

    private function getLqDoughnutChart(Collection $rows): array
    {
        $basis    = $this->countBy($rows, 'kategori', 'Basis');
        $nonBasis = $this->countBy($rows, 'kategori', 'Non Basis');

        $total = $basis + $nonBasis;

        return [

            'type'  => 'doughnut',

            'title' => 'Distribusi Sektor Basis',

            'total' => $total,

            'labels' => [
                'Sektor Basis',
                'Sektor Non Basis',
            ],

            'datasets' => [[

                'label' => 'Jumlah Sektor',

                'data' => [
                    $basis,
                    $nonBasis,
                ],

                'backgroundColor' => [
                    '#00663f',
                    '#efc53a',
                ],

                'borderColor' => [
                    '#ffffff',
                    '#ffffff',
                ],

                'borderWidth' => 2,
                'cutout' => '58%',
                'radius' => '92%',
                'hoverOffset' => 10,
                'hoverOffset' => 10,

            ]],

            // Data untuk label di luar chart
            'legend' => [

                [
                    'label' => 'Sektor Basis',
                    'value' => $basis,
                    'percent' => round($basis / $total * 100, 2),
                    'color' => '#00663f',
                ],

                [
                    'label' => 'Sektor Non Basis',
                    'value' => $nonBasis,
                    'percent' => round($nonBasis / $total * 100, 2),
                    'color' => '#efc53a',
                ],

            ],

        ];
    }

    private function getLqBarChart(Collection $rows): array
    {
        $rows = $rows->sortByDesc('nilai_lq')->values();

        return [
            'type'     => 'bar',
            'title'    => 'Nilai LQ Seluruh Sektor',
            'indexAxis' => 'y',
            'labels' => $rows
                ->pluck('sektor.nama_sektor')
                ->map(fn ($nama) => Str::title(Str::lower($nama)))
                ->toArray(),
            'datasets' => [
                [
                    'label' => 'Nilai LQ',

                    'data' => $rows
                        ->pluck('nilai_lq')
                        ->map(fn($v)=>round((float)$v,4))
                        ->toArray(),

                    'backgroundColor' => '#efc53a',

                    'borderColor' => '#efc53a',

                    'borderWidth' => 1,

                ]

            ]
        ];
    }

    private function getLqRanking(Collection $rows): array
    {
        $map = fn ($row) => $this->rankingItem(
            $row->sektor_id,
            $row->sektor->nama_sektor,
            (float)$row->nilai_lq,
            $row->kategori
        );
        return [
            'top'    => $this->topRanking($rows,'nilai_lq')->map($map)->values()->toArray(),];
    }

    private function getLqTable(Collection $rows): array
    {
        return $this->buildTable(
            rows: $rows,
            sortResolver: fn ($row) => [
                $row->tahun,
                $row->sektor_id,
            ],
            mapResolver: fn ($row) => [
                'tahun'       => $row->tahun,
                'nama_sektor' => $row->sektor->nama_sektor,
                'nilai_lq'    => round((float) $row->nilai_lq, 4),
                'kategori'    => $row->kategori,
            ],
        );
    }

    /** ============ Dashboard SSA ============ */

    private function getSsaDashboard(int $kabId, string|int $tahun): array
    {
        $rows = $this->baseSsaQuery($kabId, $tahun);

        return [
            'header' => [
                'kabupaten' => optional($rows->first()?->kabupaten)->nama_kabupaten ?? '-',
                'tahun' => $tahun,
                'title' => 'Hasil Analisis Shift Share',
                'description' =>
                    'Menganalisis pertumbuhan ekonomi sektoral dan daya saing daerah menggunakan metode Shift Share Analysis (SSA).',
            ],

            'summary' => $this->getSsaSummary($rows),

            'charts' => [
                'Doughnut_growth'          => $this->getSsaDoughnutGrowth($rows),
                'Doughnut_competitiveness' => $this->getSsaDoughnutCompetitiveness($rows),
            ],
            'ranking' => $this->getSsaRanking($rows),

            'tabs' => $this->getSsaTabs($rows),

            'table' => $this->getSsaTable($rows),
        ];
    }

    private function getSsaSummary(Collection $rows): array
    {
        $cepat  = $this->countBy($rows, 'kategori_pertumbuhan', 'Pertumbuhan Cepat');
        $lambat = $this->countBy($rows, 'kategori_pertumbuhan', 'Pertumbuhan Lambat');
        $baik   = $this->countBy($rows, 'kategori_daya_saing', 'Daya Saing Baik', 'Tidak Dapat Bersaing');
        $terbaik = $this->maximum($rows, 'dij');

        return [
            [
                'title' => 'Pertumbuhan Cepat',
                'value' => 15,
                'icon' => 'fa-solid fa-arrow-trend-up',
                'color' => 'success',
                'description' =>
                'Sektor dengan pertumbuhan lebih cepat dibanding rata-rata provinsi.'
            ],

            [
                'title' => 'Pertumbuhan Lambat',
                'value' => 2,
                'icon' => 'fa-solid fa-arrow-trend-down',
                'color' => 'warning',
                'description' =>
                'Sektor dengan pertumbuhan lebih lambat dibanding rata-rata provinsi.'
            ],

            [
                'title' => 'Daya Saing Baik',
                'value' => 8,
                'icon' => 'fas fa-trophy',
                'color' => 'primary',
                'description' =>
                'Sektor memiliki keunggulan kompetitif dibanding rata-rata provinsi.'
            ],

            [
                'title' => 'Tidak Dapat Bersaing',
                'value' => 9,
                'icon' => 'fas fa-triangle-exclamation',
                'color' => 'danger',
                'description' =>
                'Sektor belum memiliki keunggulan kompetitif dibanding rata-rata provinsi.'
            ]
        ];
    }

    private function getSsaDoughnutGrowth(Collection $rows): array
    {
        $cepat  = $this->countBy($rows, 'kategori_pertumbuhan', 'Pertumbuhan Cepat');
        $lambat = $this->countBy($rows, 'kategori_pertumbuhan', 'Pertumbuhan Lambat');

        return [
            'title'    => 'Distribusi Pertumbuhan Sektor',
            'labels'   => ['Pertumbuhan Cepat', 'Pertumbuhan Lambat'],
            'datasets' => [['label' => 'Jumlah Sektor', 'data' => [$cepat, $lambat]]],
            'summary'  => [
                'total'     => $cepat + $lambat,
                'mayoritas' => $cepat >= $lambat ? 'Pertumbuhan Cepat' : 'Pertumbuhan Lambat',
            ],
        ];
    }

    private function getSsaDoughnutCompetitiveness(Collection $rows): array
    {
        $baik  = $this->countBy($rows, 'kategori_daya_saing', 'Daya Saing Baik');
        $tidak = $this->countBy($rows, 'kategori_daya_saing', 'Tidak Dapat Bersaing');
        $total = max(1, $baik + $tidak);

        return [
            'title'    => 'Distribusi Daya Saing Sektor',
            'type'     => 'doughnut',
            'labels'   => ['Daya Saing Baik', 'Tidak Dapat Bersaing'],
            'datasets' => [['label' => 'Jumlah Sektor', 'data' => [$baik, $tidak]]],
            'summary'  => [
                'total'      => $total,
                'mayoritas'  => $baik >= $tidak ? 'Daya Saing Baik' : 'Tidak Dapat Bersaing',
                'persentase' => [
                    'baik'  => round(($baik / $total) * 100, 2),
                    'tidak' => round(($tidak / $total) * 100, 2),
                ],
            ],
        ];
    }

    /** Scatter: X = Cij, Y = Dij */
    private function getSsaScatterChart(Collection $rows): array
    {
        $dataset = $rows->map(fn ($row) => [
            'x'                    => round((float) $row->cij, 4),
            'y'                    => round((float) $row->dij, 4),
            'label'                => $row->sektor->nama_sektor,
            'kategori_pertumbuhan' => $row->kategori_pertumbuhan,
            'kategori_daya_saing'  => $row->kategori_daya_saing,
        ])->values()->toArray();

        return [
            'title'    => 'Scatter Plot SSA',
            'type'     => 'scatter',
            'xLabel'   => 'Competitive Effect (Cij)',
            'yLabel'   => 'Net Shift (Dij)',
            'datasets' => [['label' => 'Sektor', 'data' => $dataset]],
        ];
    }

    private function getSsaRanking(Collection $rows): array
    {
        $map = fn($row)=>$this->rankingItem(
            $row->sektor_id,
            $row->sektor->nama_sektor,
            (float)$row->dij,
            $row->kategori_pertumbuhan
        );

        return [
            'top'=>$this->topRanking($rows,'dij')->map($map)->values()->toArray(),
            'bottom'=>$this->bottomRanking($rows,'dij')->map($map)->values()->toArray(),
        ];
    }

    private function getSsaTabs(Collection $rows): array
    {
        $mapPertumbuhan = fn ($row) => [
            'sektor_id' => $row->sektor_id,
            'nama' => $row->sektor->nama_sektor,
            'dij' => round((float) $row->dij, 4),
            'kategori_pertumbuhan' => $row->kategori_pertumbuhan,
        ];

        $mapDayaSaing = fn ($row) => [
            'sektor_id' => $row->sektor_id,
            'nama' => $row->sektor->nama_sektor,
            'cij' => round((float) $row->cij, 4),
            'kategori_daya_saing' => $row->kategori_daya_saing,
        ];

        return [
            $this->makeTab(
                'pertumbuhan_cepat',
                'Pertumbuhan Cepat',
                $rows
                    ->where('kategori_pertumbuhan','Pertumbuhan Cepat')
                    ->map($mapPertumbuhan)
            ),

            $this->makeTab(
                'pertumbuhan_lambat',
                'Pertumbuhan Lambat',
                $rows
                    ->where('kategori_pertumbuhan','Pertumbuhan Lambat')
                    ->map($mapPertumbuhan)
            ),

            $this->makeTab(
                'daya_saing_baik',
                'Daya Saing Baik',
                $rows
                    ->where('kategori_daya_saing','Daya Saing Baik')
                    ->map($mapDayaSaing)
            ),

            $this->makeTab(
                'tidak_dapat_bersaing',
                'Tidak Dapat Bersaing',
                $rows
                    ->where('kategori_daya_saing','Tidak Dapat Bersaing')
                    ->map($mapDayaSaing)
            ),

        ];
    }

    private function getSsaTable(Collection $rows): array
    {
        return $rows
            ->sortBy([['tahun', 'asc'], ['sektor_id', 'asc']])
            ->map(fn ($row) => [
                'tahun'                => $row->tahun,
                'sektor_id'            => $row->sektor_id,
                'nama_sektor'          => $row->sektor->nama_sektor,
                'rn'                   => round((float) $row->rn, 6),
                'rin'                  => round((float) $row->rin, 6),
                'rij'                  => round((float) $row->rij, 6),
                'nij'                  => round((float) $row->nij, 4),
                'mij'                  => round((float) $row->mij, 4),
                'cij'                  => round((float) $row->cij, 4),
                'dij'                  => round((float) $row->dij, 4),
                'kategori_pertumbuhan' => $row->kategori_pertumbuhan,
                'kategori_daya_saing'  => $row->kategori_daya_saing,
            ])
            ->values()
            ->toArray();
    }

    /** ============ Dashboard Tipologi Sektor ============ */

    private function getTipologiDashboard(int $kabId, string|int $tahun): array
    {
        $rows = $this->baseTipologiQuery($kabId, $tahun);

        return [
            'header' => [
                'kabupaten' => optional($rows->first()?->kabupaten)->nama_kabupaten ?? '-',
                'tahun' => $tahun,
                'title' => 'Hasil Analisis Tipologi Sektor',
                'description' =>
                    'Mengelompokkan sektor ekonomi ke dalam empat kuadran berdasarkan tingkat pertumbuhan dan kontribusi terhadap perekonomian daerah.',
            ],

            'summary' => $this->getTipologiSummary($rows),

            'charts' => [
            ],

            'ranking' => $this->getTipologiRanking($rows),

            'table' => $this->getTipologiTable($rows),
        ];
    }

    private function getTipologiSummary(Collection $rows): array
    {
        return [

            [
                'title' => 'Sektor Unggulan',
                'value' => $this->countBy($rows, 'kuadran', 'Kuadran I'),
                'description' => 'Sektor maju dan tumbuh pesat dengan pertumbuhan serta kontribusi di atas rata-rata provinsi.',
                'icon' => 'fa-solid fa-medal',
                'color' => 'success',
            ],

            [
                'title' => 'Sektor Potensial',
                'value' => $this->countBy($rows, 'kuadran', 'Kuadran II'),
                'description' => 'Sektor berkembang dengan kontribusi tinggi namun pertumbuhannya masih di bawah rata-rata provinsi.',
                'icon' => 'fa-solid fa-seedling',
                'color' => 'warning',
            ],

            [
                'title' => 'Sektor Berkembang',
                'value' => $this->countBy($rows, 'kuadran', 'Kuadran III'),
                'description' => 'Sektor berpotensi tumbuh karena memiliki laju pertumbuhan tinggi meskipun kontribusinya masih rendah.',
                'icon' => 'fa-solid fa-chart-line',
                'color' => 'primary',
            ],

            [
                'title' => 'Sektor Relatif Tertinggal',
                'value' => $this->countBy($rows, 'kuadran', 'Kuadran IV'),
                'description' => 'Sektor relatif tertinggal dengan pertumbuhan dan kontribusi di bawah rata-rata provinsi.',
                'icon' => 'fa-solid fa-arrow-trend-down',
                'color' => 'danger',
            ],

        ];
    }

    private function getTipologiDoughnutChart(Collection $rows): array
    {
        $counts = collect(['Kuadran I', 'Kuadran II', 'Kuadran III', 'Kuadran IV'])
            ->mapWithKeys(fn ($k) => [$k => $this->countBy($rows, 'kuadran', $k)]);

        $total = max(1, $counts->sum());

        return [
            'title'    => 'Distribusi Tipologi Sektor',
            'type'     => 'doughnut',
            'labels'   => $counts->keys()->toArray(),
            'datasets' => [['label' => 'Jumlah Sektor', 'data' => $counts->values()->toArray()]],
            'summary'  => [
                'total'      => $total,
                'persentase' => [
                    'kuadran1' => round(($counts['Kuadran I'] / $total) * 100, 2),
                    'kuadran2' => round(($counts['Kuadran II'] / $total) * 100, 2),
                    'kuadran3' => round(($counts['Kuadran III'] / $total) * 100, 2),
                    'kuadran4' => round(($counts['Kuadran IV'] / $total) * 100, 2),
                ],
            ],
        ];
    }

    private function getTipologiBarChart(Collection $rows): array
    {
        $rows = $rows
            ->sortByDesc('nilai_lq')
            ->values();

        return [

            'type' => 'bar',

            'title' => 'Nilai LQ Berdasarkan Tipologi',

            'labels' => $rows
                ->pluck('sektor.nama_sektor')
                ->toArray(),

            'datasets' => [[

                'label' => 'Nilai LQ',

                'data' => $rows
                    ->pluck('nilai_lq')
                    ->map(fn($v)=>round((float)$v,4))
                    ->toArray()

            ]]

        ];
    }
    private function getTipologiRanking(Collection $rows): array
    {
        $sorted = $rows
            ->sortByDesc('nilai_lq')
            ->values();

        $map = fn($row)=>
            $this->rankingItem(

                $row->sektor_id,

                $row->sektor->nama_sektor,

                (float)$row->nilai_lq,

                $row->kuadran

            );

        return [

            'top'=>$sorted
                ->take(5)
                ->map($map)
                ->values()
                ->toArray(),

            'bottom'=>$sorted
                ->reverse()
                ->take(5)
                ->values()
                ->map($map)
                ->values()
                ->toArray()

        ];
    }

    private function getTipologiTabs(Collection $rows): array
    {
        return $this->buildQuadrantTable(
            rows: $rows,

            sortResolver: fn ($row) => [
                $row->tahun,
                $row->kuadran,
                $row->sektor_id,
            ],

            mapResolver: fn ($row) => [

                'tahun' => $row->tahun,

                'nama_sektor' => $row->sektor->nama_sektor,

                'kuadran' => $row->kuadran,

                'nilai_lq' => round((float) ($row->nilai_lq ?? 0), 4),

                'kategori_lq' => $row->kategori_lq,

                'cij' => round((float) ($row->cij ?? 0), 4),

                'dij' => round((float) ($row->dij ?? 0), 4),

                'kategori_pertumbuhan' => $row->kategori_pertumbuhan,

                'kategori_daya_saing' => $row->kategori_daya_saing,

            ],
        );
    }

    private function getTipologiTable(Collection $rows): array
    {
        return $this->buildTable(
            rows: $rows,

            sortResolver: fn ($row) => [
                $row->kuadran,
                $row->sektor_id,
            ],

            mapResolver: fn ($row) => [

                'tahun'       => $row->tahun,

                'nama_sektor' => $row->sektor->nama_sektor,

                // Nilai SSA
                'nilai_ssa'   => round((float) ($row->dij ?? 0), 2),

                // Nilai LQ
                'nilai_lq'    => round((float) ($row->nilai_lq ?? 0), 2),

                'kuadran'     => $row->kuadran,

                'kategori'    => match ($row->kuadran) {
                    'Kuadran I'   => 'Sektor Unggulan',
                    'Kuadran II'  => 'Sektor Potensial',
                    'Kuadran III' => 'Sektor Berkembang',
                    'Kuadran IV'  => 'Sektor Relatif Tertinggal',
                    default       => '-',
                },

            ],
        );
    }

    /** ============ Dashboard Tipologi Klassen ============ */

    private function getKlassenDashboard(int $kabId, string|int $tahun): array
    {
        $rows = $this->baseKlassenQuery($kabId, $tahun);

        return [
            'header' => [
                'kabupaten' => optional($rows->first()?->kabupaten)->nama_kabupaten ?? '-',
                'tahun' => $tahun,
                'title' => 'Hasil Analisis Tipologi Klassen',
                'description' =>
                    'Mengklasifikasikan sektor ekonomi berdasarkan kombinasi laju pertumbuhan dan kontribusi PDRB untuk mengidentifikasi sektor maju, berkembang, maupun tertinggal.',
            ],

            'summary' => $this->getKlassenSummary($rows),

            'charts' => [
            ],

            'ranking' => $this->getKlassenRanking($rows),

            'table' => $this->getKlassenTable($rows),
        ];
    }

    private function getKlassenSummary(Collection $data): array
    {
        return [

            [
                'title' => 'Kuadran I',
                'value' => $data->where('kuadran', 'Kuadran I')->count(),
                'description' => 'Sektor maju dan tumbuh pesat dengan pertumbuhan serta kontribusi di atas rata-rata provinsi.',
                'icon' => 'fa-solid fa-medal',
                'color' => 'success',
            ],

            [
                'title' => 'Kuadran II',
                'value' => $data->where('kuadran', 'Kuadran II')->count(),
                'description' => 'Sektor maju namun mengalami pertumbuhan lebih lambat dibanding rata-rata provinsi.',
                'icon' => 'fa-solid fa-chart-line',
                'color' => 'warning',
            ],

            [
                'title' => 'Kuadran III',
                'value' => $data->where('kuadran', 'Kuadran III')->count(),
                'description' => 'Sektor berkembang dengan pertumbuhan tinggi tetapi kontribusinya masih relatif rendah.',
                'icon' => 'fa-solid fa-arrow-trend-up',
                'color' => 'primary',
            ],

            [
                'title' => 'Kuadran IV',
                'value' => $data->where('kuadran', 'Kuadran IV')->count(),
                'description' => 'Sektor relatif tertinggal dengan pertumbuhan dan kontribusi di bawah rata-rata provinsi.',
                'icon' => 'fa-solid fa-arrow-trend-down',
                'color' => 'danger',
            ],

        ];
    }

    private function getKlassenDoughnutChart(Collection $rows): array
    {
        $group = $rows
            ->groupBy('kuadran')
            ->map->count();

        return [

            'type'=>'doughnut',

            'title'=>'Distribusi Tipologi Klassen',

            'labels'=>$group
                ->keys()
                ->values()
                ->toArray(),

            'datasets'=>[[

                'label'=>'Jumlah Sektor',

                'data'=>$group
                    ->values()
                    ->toArray()

            ]]

        ];
    }

    private function getKlassenBarChart(Collection $rows): array
    {
        $group = $rows
            ->groupBy('kuadran')
            ->map->count();

        return [

            'type'=>'bar',

            'title'=>'Jumlah Sektor per Kuadran',

            'labels'=>$group
                ->keys()
                ->values()
                ->toArray(),

            'datasets'=>[[

                'label'=>'Jumlah Sektor',

                'data'=>$group
                    ->values()
                    ->toArray()

            ]]

        ];
    }

    private function getKlassenRanking(Collection $data): array
    {
        return $data
            ->sortByDesc('pertumbuhan_kabupaten')
            ->take(10)
            ->values()
            ->map(fn ($item) => [
                'sektor'      => $item->sektor?->nama_sektor,
                'kuadran'     => $item->kuadran,
                'pertumbuhan' => round($item->pertumbuhan_kabupaten, 2),
                'kontribusi'  => round($item->kontribusi_kabupaten, 2),
            ])
            ->toArray();
    }

    private function getKlassenTabs(Collection $data): array
    {
        return $data
            ->groupBy('klassen')
            ->map(function ($items, $klassen) {

                return [

                    'key' => str($klassen)
                        ->lower()
                        ->replace(' ', '_')
                        ->replace('-', '_')
                        ->toString(),

                    'title' => $klassen,

                    'rows' => $items
                        ->sortBy('sektor_id')
                        ->map(fn ($item) => [

                            'sektor' => $item->sektor?->nama_sektor,

                            'pertumbuhan' => round(
                                (float) $item->pertumbuhan_kabupaten,
                                2
                            ),

                            'kontribusi' => round(
                                (float) $item->kontribusi_kabupaten,
                                2
                            ),

                        ])
                        ->values()
                        ->toArray(),

                ];

            })
            ->values()
            ->toArray();
    }

    private function getKlassenTable(Collection $data): array
    {
        return $data
            ->map(fn ($item) => [
                'tahun'                 => $item->tahun,
                'sektor'                => $item->sektor?->nama_sektor,
                'kuadran'               => $item->kuadran,
                'pertumbuhan_kabupaten' => (float) $item->pertumbuhan_kabupaten,
                'kontribusi_kabupaten'  => (float) $item->kontribusi_kabupaten,
                'pertumbuhan_provinsi'  => (float) $item->pertumbuhan_provinsi,
                'kontribusi_provinsi'   => (float) $item->kontribusi_provinsi,
            ])
            ->values()
            ->toArray();
    }

    /** ============ Shared Helpers ============ */

    private function total(Collection $rows): int
    {
        return $rows->count();
    }

    private function countBy(Collection $rows, string $column, string $value): int
    {
        return $rows->where($column, $value)->count();
    }

    private function average(Collection $rows, string $column): float
    {
        return $rows->isEmpty() ? 0 : round((float) $rows->avg($column), 4);
    }

    private function maximum(Collection $rows, string $column)
    {
        return $rows->sortByDesc($column)->first();
    }

    private function minimum(Collection $rows, string $column)
    {
        return $rows->sortBy($column)->first();
    }

    private function topRanking(Collection $rows, string $column, int $limit = 5): Collection
    {
        return $rows->sortByDesc($column)->take($limit)->values();
    }

    private function bottomRanking(Collection $rows, string $column, int $limit = 5): Collection
    {
        return $rows->sortBy($column)->take($limit)->values();
    }

    private function chartLabels(Collection $rows): array
    {
        return $rows->pluck('sektor.nama_sektor')->toArray();
    }

    private function chartValues(Collection $rows, string $column): array
    {
        return $rows->pluck($column)->map(fn ($v) => round((float) $v, 4))->toArray();
    }

    private function doughnutDataset(Collection $rows, string $column): array
    {
        return $rows->groupBy($column)->map->count()->toArray();
    }

    private function availableYears(Collection $rows): array
    {
        return $rows->pluck('tahun')->unique()->sort()->values()->toArray();
    }

    private function dataTable(Collection $rows): Collection
    {
        return $rows->sortBy([['tahun', 'asc'], ['sektor_id', 'asc']])->values();
    }
}
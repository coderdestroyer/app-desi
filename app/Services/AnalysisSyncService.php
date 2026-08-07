<?php

namespace App\Services;

use App\Models\Kabupaten;
use App\Models\PdbNasional;
use App\Models\PdrbSumateraKabupaten;
use App\Models\PdrbSumateraProvinsi;
use App\Models\Provinsi;
use App\Models\SummaryIndikatorResult;
use App\Models\SummaryKlassenResult;
use App\Models\SummaryLqResult;
use App\Models\SummaryShiftShareResult;
use App\Models\SummaryTipologiSektorResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalysisSyncService
{
    public function __construct(
        protected LqService $lqService,
        protected TipologiKlassenService $klassenService,
        protected SsaService $ssaService,
        protected TipologiSektorService $tipologiSektorService,
        protected IndikatorService $indikatorService
    ) {}

    /**
     * Sinkronisasi hasil analisis untuk 1 Provinsi pada Tahun tertentu
     */
    public function syncProvinsi(int $provinsiId, int $tahun): void
    {
        DB::transaction(function () use ($provinsiId, $tahun) {
            // 1. LQ Provinsi
            $lqData = $this->lqService->calculateLqProvinsi($provinsiId, $tahun);
            foreach ($lqData as $row) {
                SummaryLqResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => null,
                        'sektor_id' => $row['sektor_id'],
                        'tahun' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'provinsi',
                        'nilai_lq' => $row['nilai_lq'],
                        'kategori' => $row['kategori'],
                        'persen_daerah' => $row['persen_provinsi'] ?? 0,
                        'persen_acuan' => $row['persen_nasional'] ?? 0,
                    ]
                );
            }

            // 2. Klassen Provinsi
            $klassenData = $this->klassenService->calculateKlassenProvinsi($provinsiId, $tahun);
            $tahunAwal = $tahun - 1;
            foreach ($klassenData as $row) {
                SummaryKlassenResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => null,
                        'sektor_id' => $row['sektor_id'],
                        'tahun_awal' => $tahunAwal,
                        'tahun_akhir' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'provinsi',
                        'growth_daerah' => $row['laju_pertumbuhan'] ?? 0,
                        'growth_pembanding' => $row['laju_pertumbuhan_acuan'] ?? 0,
                        'share_daerah' => $row['kontribusi_pdrb'] ?? 0,
                        'share_pembanding' => $row['kontribusi_acuan'] ?? 0,
                        'kuadran' => $row['kuadran'],
                        'kategori_kuadran' => $row['klasifikasi_sektor'],
                    ]
                );
            }

            // 3. Shift Share Provinsi
            $ssaData = $this->ssaService->calculateSsaProvinsi($provinsiId, $tahun);
            foreach ($ssaData as $row) {
                SummaryShiftShareResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => null,
                        'sektor_id' => $row['sektor_id'],
                        'tahun_awal' => $tahunAwal,
                        'tahun_akhir' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'provinsi',
                        'n_nij' => $row['nij'] ?? 0,
                        'c_cij' => $row['mij'] ?? 0,
                        's_sij' => $row['cij'] ?? 0,
                        'd_dij' => $row['dij'] ?? 0,
                        'keunggulan_kompetitif' => ($row['cij'] ?? 0) >= 0,
                        'spesialisasi' => ($row['mij'] ?? 0) >= 0,
                    ]
                );
            }

            // 4. Tipologi Sektor Provinsi
            $tipologiData = $this->tipologiSektorService->calculateTipologiProvinsi($provinsiId, $tahun);
            foreach ($tipologiData as $row) {
                SummaryTipologiSektorResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => null,
                        'sektor_id' => $row['sektor_id'],
                        'tahun' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'provinsi',
                        'nilai_lq' => $row['lq'] ?? 0,
                        'kategori_lq' => ($row['lq'] ?? 0) >= 1 ? 'Basis' : 'Non Basis',
                        'shift_share_net' => $row['cij'] ?? 0,
                        'klasifikasi_sektor' => $row['kategori_sektor'] ?? '',
                    ]
                );
            }

            // 5. Indikator Provinsi
            $indikatorData = $this->indikatorService->getIndikatorProvinsi($provinsiId, $tahun);
            foreach ($indikatorData as $row) {
                SummaryIndikatorResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => null,
                        'sektor_id' => $row['sektor_id'],
                        'tahun' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'provinsi',
                        'pertumbuhan' => $row['pertumbuhan'] ?? 0,
                        'kontribusi' => $row['kontribusi'] ?? 0,
                    ]
                );
            }
        });
    }

    /**
     * Sinkronisasi hasil analisis untuk 1 Kabupaten pada Tahun tertentu
     */
    public function syncKabupaten(int $kabId, int $tahun): void
    {
        $kabupaten = Kabupaten::find($kabId);
        if (!$kabupaten) {
            return;
        }

        $provinsiId = $kabupaten->provinsi_id;
        $tahunAwal = $tahun - 1;

        DB::transaction(function () use ($kabId, $provinsiId, $tahun, $tahunAwal) {
            // 1. LQ Kabupaten
            $lqData = $this->lqService->calculateLq($kabId, $tahun);
            foreach ($lqData as $row) {
                SummaryLqResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => $kabId,
                        'sektor_id' => $row['sektor_id'],
                        'tahun' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'kabupaten',
                        'nilai_lq' => $row['nilai_lq'],
                        'kategori' => $row['kategori'],
                        'persen_daerah' => $row['persen_kabupaten'] ?? 0,
                        'persen_acuan' => $row['persen_provinsi'] ?? 0,
                    ]
                );
            }

            // 2. Klassen Kabupaten
            $klassenData = $this->klassenService->calculateKlassen($kabId, $tahun);
            foreach ($klassenData as $row) {
                SummaryKlassenResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => $kabId,
                        'sektor_id' => $row['sektor_id'],
                        'tahun_awal' => $tahunAwal,
                        'tahun_akhir' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'kabupaten',
                        'growth_daerah' => $row['laju_pertumbuhan'] ?? 0,
                        'growth_pembanding' => $row['laju_pertumbuhan_acuan'] ?? 0,
                        'share_daerah' => $row['kontribusi_pdrb'] ?? 0,
                        'share_pembanding' => $row['kontribusi_acuan'] ?? 0,
                        'kuadran' => $row['kuadran'],
                        'kategori_kuadran' => $row['klasifikasi_sektor'],
                    ]
                );
            }

            // 3. Shift Share Kabupaten
            $ssaData = $this->ssaService->calculateSsa($kabId, $tahun);
            foreach ($ssaData as $row) {
                SummaryShiftShareResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => $kabId,
                        'sektor_id' => $row['sektor_id'],
                        'tahun_awal' => $tahunAwal,
                        'tahun_akhir' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'kabupaten',
                        'n_nij' => $row['nij'] ?? 0,
                        'c_cij' => $row['mij'] ?? 0,
                        's_sij' => $row['cij'] ?? 0,
                        'd_dij' => $row['dij'] ?? 0,
                        'keunggulan_kompetitif' => ($row['cij'] ?? 0) >= 0,
                        'spesialisasi' => ($row['mij'] ?? 0) >= 0,
                    ]
                );
            }

            // 4. Tipologi Sektor Kabupaten
            $tipologiData = $this->tipologiSektorService->calculateTipologi($kabId, $tahun);
            foreach ($tipologiData as $row) {
                SummaryTipologiSektorResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => $kabId,
                        'sektor_id' => $row['sektor_id'],
                        'tahun' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'kabupaten',
                        'nilai_lq' => $row['lq'] ?? 0,
                        'kategori_lq' => ($row['lq'] ?? 0) >= 1 ? 'Basis' : 'Non Basis',
                        'shift_share_net' => $row['cij'] ?? 0,
                        'klasifikasi_sektor' => $row['kategori_sektor'] ?? '',
                    ]
                );
            }

            // 5. Indikator Kabupaten
            $indikatorData = $this->indikatorService->getIndikatorKabupaten($kabId, $tahun);
            foreach ($indikatorData as $row) {
                SummaryIndikatorResult::updateOrCreate(
                    [
                        'provinsi_id' => $provinsiId,
                        'kabupaten_id' => $kabId,
                        'sektor_id' => $row['sektor_id'],
                        'tahun' => $tahun,
                    ],
                    [
                        'tingkat_wilayah' => 'kabupaten',
                        'pertumbuhan' => $row['pertumbuhan'] ?? 0,
                        'kontribusi' => $row['kontribusi'] ?? 0,
                    ]
                );
            }
        });
    }

    /**
     * Hitung ulang seluruh data dari nol (Seeder / Recalculate Command)
     */
    public function syncAll(): void
    {
        $yearsProv = PdrbSumateraProvinsi::distinct()->pluck('tahun');
        $yearsKab = PdrbSumateraKabupaten::distinct()->pluck('tahun');
        $allYears = $yearsProv->concat($yearsKab)->unique()->sort()->values();

        $provinsis = Provinsi::all();
        $kabupatens = Kabupaten::all();

        foreach ($allYears as $tahun) {
            // Hitung untuk semua provinsi
            foreach ($provinsis as $prov) {
                try {
                    $this->syncProvinsi($prov->provinsi_id, (int)$tahun);
                } catch (\Exception $e) {
                    Log::error("Gagal sync analisis Provinsi {$prov->nama_provinsi} Tahun {$tahun}: " . $e->getMessage());
                }
            }

            // Hitung untuk semua kabupaten
            foreach ($kabupatens as $kab) {
                try {
                    $this->syncKabupaten($kab->kab_id, (int)$tahun);
                } catch (\Exception $e) {
                    Log::error("Gagal sync analisis Kabupaten {$kab->nama_kabupaten} Tahun {$tahun}: " . $e->getMessage());
                }
            }
        }
    }
}

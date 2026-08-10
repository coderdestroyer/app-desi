<?php

namespace App\Services;

use App\Models\Kabupaten;
use App\Models\PdbNasional;
use App\Models\PdrbSumateraKabupaten as PdrbKabupaten;
use App\Models\PdrbSumateraProvinsi as PdrbSumut;
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
    /**
     * Sinkronisasi hasil analisis untuk 1 Provinsi pada Tahun tertentu (Batch Upsert)
     */
    /**
     * Sinkronisasi hasil analisis untuk 1 Provinsi pada Tahun tertentu (Bulk Delete & Insert)
     */
    /**
     * Sinkronisasi hasil analisis untuk 1 Provinsi pada Tahun tertentu (Hanya jika PDB Nasional tahun tsb ada)
     */
    public function syncProvinsi(int $provinsiId, int $tahun, bool $warmUp = true): void
    {
        // Syarat: PDB Nasional pada tahun $tahun harus sudah ada
        $hasNasional = PdbNasional::where('tahun', $tahun)->exists();
        $hasProvinsi = PdrbSumut::where('provinsi_id', $provinsiId)->where('tahun', $tahun)->exists();

        if (! $hasNasional || ! $hasProvinsi) {
            // Hapus summary yang tidak memenuhi syarat agar tidak menggantung
            SummaryLqResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun', $tahun)->delete();
            SummaryKlassenResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun_akhir', $tahun)->delete();
            SummaryShiftShareResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun_akhir', $tahun)->delete();
            SummaryTipologiSektorResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun', $tahun)->delete();
            SummaryIndikatorResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun', $tahun)->delete();
            return;
        }

        if ($warmUp) {
            $this->lqService->warmUpProvinsiCache($provinsiId, [$tahun - 1, $tahun]);
        }

        DB::transaction(function () use ($provinsiId, $tahun) {
            $tahunAwal = $tahun - 1;

            // 1. LQ Provinsi
            $lqData = $this->lqService->calculateLqProvinsi($provinsiId, $tahun);
            $lqRows = [];
            foreach ($lqData as $row) {
                $lqRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => null,
                    'sektor_id' => $row['sektor_id'],
                    'tahun' => $tahun,
                    'tingkat_wilayah' => 'provinsi',
                    'nilai_lq' => $row['nilai_lq'],
                    'kategori' => $row['kategori'],
                    'persen_daerah' => $row['persen_provinsi'] ?? 0,
                    'persen_acuan' => $row['persen_nasional'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryLqResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun', $tahun)->delete();
            if (!empty($lqRows)) {
                SummaryLqResult::insert($lqRows);
            }

            // 2. Klassen Provinsi
            $klassenData = $this->klassenService->calculateKlassenProvinsi($provinsiId, $tahun);
            $klassenRows = [];
            foreach ($klassenData as $row) {
                $klassenRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => null,
                    'sektor_id' => $row['sektor_id'],
                    'tahun_awal' => $tahunAwal,
                    'tahun_akhir' => $tahun,
                    'tingkat_wilayah' => 'provinsi',
                    'growth_daerah' => $row['laju_pertumbuhan'] ?? 0,
                    'growth_pembanding' => $row['laju_pertumbuhan_acuan'] ?? 0,
                    'share_daerah' => $row['kontribusi_pdrb'] ?? 0,
                    'share_pembanding' => $row['kontribusi_acuan'] ?? 0,
                    'kuadran' => $row['kuadran'],
                    'kategori_kuadran' => $row['klasifikasi_sektor'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryKlassenResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun_akhir', $tahun)->delete();
            if (!empty($klassenRows)) {
                SummaryKlassenResult::insert($klassenRows);
            }

            // 3. Shift Share Provinsi
            $ssaData = $this->ssaService->calculateSsaProvinsi($provinsiId, $tahun);
            $ssaRows = [];
            foreach ($ssaData as $row) {
                $ssaRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => null,
                    'sektor_id' => $row['sektor_id'],
                    'tahun_awal' => $tahunAwal,
                    'tahun_akhir' => $tahun,
                    'tingkat_wilayah' => 'provinsi',
                    'n_nij' => $row['nij'] ?? 0,
                    'c_cij' => $row['mij'] ?? 0,
                    's_sij' => $row['cij'] ?? 0,
                    'd_dij' => $row['dij'] ?? 0,
                    'keunggulan_kompetitif' => \Illuminate\Support\Facades\DB::raw((($row['cij'] ?? 0) >= 0) ? 'true' : 'false'),
                    'spesialisasi' => \Illuminate\Support\Facades\DB::raw((($row['mij'] ?? 0) >= 0) ? 'true' : 'false'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryShiftShareResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun_akhir', $tahun)->delete();
            if (!empty($ssaRows)) {
                SummaryShiftShareResult::insert($ssaRows);
            }

            // 4. Tipologi Sektor Provinsi
            $tipologiData = $this->tipologiSektorService->calculateTipologiProvinsi($provinsiId, $tahun);
            $tipologiRows = [];
            foreach ($tipologiData as $row) {
                $tipologiRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => null,
                    'sektor_id' => $row['sektor_id'],
                    'tahun' => $tahun,
                    'tingkat_wilayah' => 'provinsi',
                    'nilai_lq' => $row['lq'] ?? 0,
                    'kategori_lq' => ($row['lq'] ?? 0) >= 1 ? 'Basis' : 'Non Basis',
                    'shift_share_net' => $row['cij'] ?? 0,
                    'klasifikasi_sektor' => $row['kategori_sektor'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryTipologiSektorResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun', $tahun)->delete();
            if (!empty($tipologiRows)) {
                SummaryTipologiSektorResult::insert($tipologiRows);
            }

            // 5. Indikator Provinsi
            $indikatorData = $this->indikatorService->getIndikatorProvinsi($provinsiId, $tahun);
            $indikatorRows = [];
            foreach ($indikatorData as $row) {
                $indikatorRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => null,
                    'sektor_id' => $row['sektor_id'],
                    'tahun' => $tahun,
                    'tingkat_wilayah' => 'provinsi',
                    'pertumbuhan' => $row['pertumbuhan'] ?? 0,
                    'kontribusi' => $row['kontribusi'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryIndikatorResult::where('provinsi_id', $provinsiId)->whereNull('kabupaten_id')->where('tahun', $tahun)->delete();
            if (!empty($indikatorRows)) {
                SummaryIndikatorResult::insert($indikatorRows);
            }
        });
    }

    /**
     * Sinkronisasi hasil analisis untuk 1 Kabupaten pada Tahun tertentu (Hanya jika PDRB Provinsi tahun tsb ada)
     */
    public function syncKabupaten(int $kabId, int $tahun, bool $warmUp = true): void
    {
        $kabupaten = Kabupaten::find($kabId);
        if (!$kabupaten) {
            return;
        }

        $provinsiId = $kabupaten->provinsi_id;

        // Syarat: Data PDRB Kabupaten DAN Data PDRB Provinsi induknya pada tahun $tahun harus sudah ada di DB
        $hasProvinsiData = PdrbSumut::where('provinsi_id', $provinsiId)->where('tahun', $tahun)->exists();
        $hasKabupatenData = PdrbKabupaten::where('kabupaten_id', $kabId)->where('tahun', $tahun)->exists();

        if (! $hasProvinsiData || ! $hasKabupatenData) {
            // Hapus summary yang tidak memenuhi syarat agar tidak menggantung
            SummaryLqResult::where('kabupaten_id', $kabId)->where('tahun', $tahun)->delete();
            SummaryKlassenResult::where('kabupaten_id', $kabId)->where('tahun_akhir', $tahun)->delete();
            SummaryShiftShareResult::where('kabupaten_id', $kabId)->where('tahun_akhir', $tahun)->delete();
            SummaryTipologiSektorResult::where('kabupaten_id', $kabId)->where('tahun', $tahun)->delete();
            SummaryIndikatorResult::where('kabupaten_id', $kabId)->where('tahun', $tahun)->delete();
            return;
        }

        if ($warmUp) {
            $this->lqService->warmUpKabupatenCache($kabId, $provinsiId, [$tahun - 1, $tahun]);
        }

        $tahunAwal = $tahun - 1;

        DB::transaction(function () use ($kabId, $provinsiId, $tahun, $tahunAwal) {
            // 1. LQ Kabupaten
            $lqData = $this->lqService->calculateLq($kabId, $tahun);
            $lqRows = [];
            foreach ($lqData as $row) {
                $lqRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabId,
                    'sektor_id' => $row['sektor_id'],
                    'tahun' => $tahun,
                    'tingkat_wilayah' => 'kabupaten',
                    'nilai_lq' => $row['nilai_lq'],
                    'kategori' => $row['kategori'],
                    'persen_daerah' => $row['persen_kabupaten'] ?? 0,
                    'persen_acuan' => $row['persen_provinsi'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryLqResult::where('kabupaten_id', $kabId)->where('tahun', $tahun)->delete();
            if (!empty($lqRows)) {
                SummaryLqResult::insert($lqRows);
            }

            // 2. Klassen Kabupaten
            $klassenData = $this->klassenService->calculateKlassen($kabId, $tahun);
            $klassenRows = [];
            foreach ($klassenData as $row) {
                $klassenRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabId,
                    'sektor_id' => $row['sektor_id'],
                    'tahun_awal' => $tahunAwal,
                    'tahun_akhir' => $tahun,
                    'tingkat_wilayah' => 'kabupaten',
                    'growth_daerah' => $row['laju_pertumbuhan'] ?? 0,
                    'growth_pembanding' => $row['laju_pertumbuhan_acuan'] ?? 0,
                    'share_daerah' => $row['kontribusi_pdrb'] ?? 0,
                    'share_pembanding' => $row['kontribusi_acuan'] ?? 0,
                    'kuadran' => $row['kuadran'],
                    'kategori_kuadran' => $row['klasifikasi_sektor'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryKlassenResult::where('kabupaten_id', $kabId)->where('tahun_akhir', $tahun)->delete();
            if (!empty($klassenRows)) {
                SummaryKlassenResult::insert($klassenRows);
            }

            // 3. Shift Share Kabupaten
            $ssaData = $this->ssaService->calculateSsa($kabId, $tahun);
            $ssaRows = [];
            foreach ($ssaData as $row) {
                $ssaRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabId,
                    'sektor_id' => $row['sektor_id'],
                    'tahun_awal' => $tahunAwal,
                    'tahun_akhir' => $tahun,
                    'tingkat_wilayah' => 'kabupaten',
                    'n_nij' => $row['nij'] ?? 0,
                    'c_cij' => $row['mij'] ?? 0,
                    's_sij' => $row['cij'] ?? 0,
                    'd_dij' => $row['dij'] ?? 0,
                    'keunggulan_kompetitif' => \Illuminate\Support\Facades\DB::raw((($row['cij'] ?? 0) >= 0) ? 'true' : 'false'),
                    'spesialisasi' => \Illuminate\Support\Facades\DB::raw((($row['mij'] ?? 0) >= 0) ? 'true' : 'false'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryShiftShareResult::where('kabupaten_id', $kabId)->where('tahun_akhir', $tahun)->delete();
            if (!empty($ssaRows)) {
                SummaryShiftShareResult::insert($ssaRows);
            }

            // 4. Tipologi Sektor Kabupaten
            $tipologiData = $this->tipologiSektorService->calculateTipologi($kabId, $tahun);
            $tipologiRows = [];
            foreach ($tipologiData as $row) {
                $tipologiRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabId,
                    'sektor_id' => $row['sektor_id'],
                    'tahun' => $tahun,
                    'tingkat_wilayah' => 'kabupaten',
                    'nilai_lq' => $row['lq'] ?? 0,
                    'kategori_lq' => ($row['lq'] ?? 0) >= 1 ? 'Basis' : 'Non Basis',
                    'shift_share_net' => $row['cij'] ?? 0,
                    'klasifikasi_sektor' => $row['kategori_sektor'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryTipologiSektorResult::where('kabupaten_id', $kabId)->where('tahun', $tahun)->delete();
            if (!empty($tipologiRows)) {
                SummaryTipologiSektorResult::insert($tipologiRows);
            }

            // 5. Indikator Kabupaten
            $indikatorData = $this->indikatorService->getIndikatorKabupaten($kabId, $tahun);
            $indikatorRows = [];
            foreach ($indikatorData as $row) {
                $indikatorRows[] = [
                    'provinsi_id' => $provinsiId,
                    'kabupaten_id' => $kabId,
                    'sektor_id' => $row['sektor_id'],
                    'tahun' => $tahun,
                    'tingkat_wilayah' => 'kabupaten',
                    'pertumbuhan' => $row['pertumbuhan'] ?? 0,
                    'kontribusi' => $row['kontribusi'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            SummaryIndikatorResult::where('kabupaten_id', $kabId)->where('tahun', $tahun)->delete();
            if (!empty($indikatorRows)) {
                SummaryIndikatorResult::insert($indikatorRows);
            }
        });
    }

    /**
     * Sinkronisasi hasil analisis untuk 1 Provinsi dan seluruh Kabupaten di dalamnya untuk Tahun tertentu
     */
    public function syncProvinsiAndChildren(int $provinsiId, int $tahun): void
    {
        $this->lqService->warmUpProvinsiCache($provinsiId, [$tahun - 1, $tahun]);
        $this->syncProvinsi($provinsiId, $tahun, false);

        $kabupatens = PdrbKabupaten::whereHas('kabupaten', function ($q) use ($provinsiId) {
            $q->where('provinsi_id', $provinsiId);
        })->where('tahun', $tahun)->pluck('kabupaten_id')->unique();

        foreach ($kabupatens as $kabId) {
            try {
                $this->syncKabupaten((int)$kabId, $tahun, true);
            } catch (\Exception $e) {
                Log::error("Gagal sync analisis Kabupaten ID {$kabId} Tahun {$tahun}: " . $e->getMessage());
            }
        }
    }

    /**
     * Sinkronisasi seluruh provinsi dan kabupaten untuk Tahun tertentu (misal ketika PDB Nasional diperbarui)
     */
    public function syncAllForYear(int $tahun): void
    {
        // Global Preload for this year and previous year (Only 3 SQL SELECT queries total!)
        $this->lqService->clearCache();
        $this->lqService->preloadPdrbData([$tahun - 1, $tahun]);

        // 1. Sync Provinsi yang punya data PDRB Provinsi di tahun $tahun
        $provinsiIds = PdrbSumut::where('tahun', $tahun)->pluck('provinsi_id')->unique();
        foreach ($provinsiIds as $provId) {
            try {
                $this->syncProvinsi((int)$provId, $tahun, false);
            } catch (\Exception $e) {
                Log::error("Gagal sync analisis Provinsi ID {$provId} Tahun {$tahun}: " . $e->getMessage());
            }
        }

        // 2. Sync Kabupaten yang punya data PDRB Kabupaten di tahun $tahun
        $kabupatenIds = PdrbKabupaten::where('tahun', $tahun)->pluck('kabupaten_id')->unique();
        foreach ($kabupatenIds as $kabId) {
            try {
                $this->syncKabupaten((int)$kabId, $tahun, false);
            } catch (\Exception $e) {
                Log::error("Gagal sync analisis Kabupaten ID {$kabId} Tahun {$tahun}: " . $e->getMessage());
            }
        }
    }

    /**
     * Hitung ulang seluruh data dari nol (Seeder / Recalculate Command)
     */
    public function syncAll(): void
    {
        $yearsProv = PdrbSumut::distinct()->pluck('tahun');
        $yearsKab = PdrbKabupaten::distinct()->pluck('tahun');
        $allYears = $yearsProv->concat($yearsKab)->unique()->sort()->values();

        foreach ($allYears as $tahun) {
            $this->syncAllForYear((int)$tahun);
        }
    }
}

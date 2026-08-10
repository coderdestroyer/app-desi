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
    protected static array $totalKabupatenCache = [];
    protected static array $totalProvinsiCache = [];
    protected static array $totalNasionalCache = [];
    protected static array $pdrbKabupatenCache = [];
    protected static array $pdrbKabupatenBySektorCache = [];
    protected static array $pdrbProvinsiCache = [];
    protected static array $pdrbProvinsiByTahunCache = [];
    protected static array $pdbNasionalByTahunCache = [];
    protected static bool $isPreloaded = false;

    /**
     * Clear all static memory caches.
     */
    public function clearCache(): void
    {
        self::$provinsiIdCache = [];
        self::$totalKabupatenCache = [];
        self::$totalProvinsiCache = [];
        self::$totalNasionalCache = [];
        self::$pdrbKabupatenCache = [];
        self::$pdrbKabupatenBySektorCache = [];
        self::$pdrbProvinsiCache = [];
        self::$pdrbProvinsiByTahunCache = [];
        self::$pdbNasionalByTahunCache = [];
        self::$isPreloaded = false;
    }

    /**
     * Preload targeted PDRB & PDB data for 1 Kabupaten in specific years (e.g. $tahun and $tahun-1).
     */
    public function warmUpKabupatenCache(int $kabId, int $provId, array $years): void
    {
        self::$provinsiIdCache[$kabId] = $provId;

        // 1. Preload PdrbKabupaten for $kabId
        $allKab = PdrbKabupaten::with('sektor')
            ->where('kabupaten_id', $kabId)
            ->whereIn('tahun', $years)
            ->get();

        foreach ($allKab->groupBy(fn($item) => $item->kabupaten_id . '_' . $item->tahun) as $key => $items) {
            self::$pdrbKabupatenCache[$key] = $items;
            self::$totalKabupatenCache[$key] = (float) $items->sum('nilai_pdrb');
            foreach ($items as $item) {
                self::$pdrbKabupatenBySektorCache[$item->kabupaten_id . '_' . $item->sektor_id . '_' . $item->tahun] = $item;
            }
        }

        // 2. Preload PdrbSumut (Provinsi) for $provId
        $allProv = PdrbSumut::with('sektor')
            ->where('provinsi_id', $provId)
            ->whereIn('tahun', $years)
            ->get();

        foreach ($allProv->groupBy(fn($item) => $item->provinsi_id . '_' . $item->tahun) as $key => $items) {
            self::$pdrbProvinsiByTahunCache[$key] = $items;
            self::$totalProvinsiCache[$key] = (float) $items->sum('nilai_pdrb');
            foreach ($items as $item) {
                self::$pdrbProvinsiCache[$item->provinsi_id . '_' . $item->sektor_id . '_' . $item->tahun] = $item;
            }
        }

        // 3. Preload PdbNasional for $years
        $allNas = PdbNasional::with('sektor')
            ->whereIn('tahun', $years)
            ->get();

        foreach ($allNas->groupBy('tahun') as $thn => $items) {
            self::$pdbNasionalByTahunCache[$thn] = $items;
            self::$totalNasionalCache[$thn] = (float) $items->sum('nilai');
        }
    }

    /**
     * Preload targeted PDRB & PDB data for 1 Provinsi in specific years.
     */
    public function warmUpProvinsiCache(int $provId, array $years): void
    {
        // 1. Preload PdrbSumut (Provinsi) for $provId
        $allProv = PdrbSumut::with('sektor')
            ->where('provinsi_id', $provId)
            ->whereIn('tahun', $years)
            ->get();

        foreach ($allProv->groupBy(fn($item) => $item->provinsi_id . '_' . $item->tahun) as $key => $items) {
            self::$pdrbProvinsiByTahunCache[$key] = $items;
            self::$totalProvinsiCache[$key] = (float) $items->sum('nilai_pdrb');
            foreach ($items as $item) {
                self::$pdrbProvinsiCache[$item->provinsi_id . '_' . $item->sektor_id . '_' . $item->tahun] = $item;
            }
        }

        // 2. Preload PdbNasional for $years
        $allNas = PdbNasional::with('sektor')
            ->whereIn('tahun', $years)
            ->get();

        foreach ($allNas->groupBy('tahun') as $thn => $items) {
            self::$pdbNasionalByTahunCache[$thn] = $items;
            self::$totalNasionalCache[$thn] = (float) $items->sum('nilai');
        }
    }

    /**
     * Preload all PDRB & PDB data for bulk calculations in just 3 SQL queries.
     */
    public function preloadPdrbData(array $years = []): void
    {
        // 1. Preload PdrbKabupaten totals & rows
        $kabQuery = PdrbKabupaten::with(['sektor', 'kabupaten']);
        if (!empty($years)) {
            $kabQuery->whereIn('tahun', $years);
        }
        $allKab = $kabQuery->get();

        foreach ($allKab->groupBy(fn($item) => $item->kabupaten_id . '_' . $item->tahun) as $key => $items) {
            self::$pdrbKabupatenCache[$key] = $items;
            self::$totalKabupatenCache[$key] = (float) $items->sum('nilai_pdrb');
            foreach ($items as $item) {
                if ($item->kabupaten && $item->kabupaten->provinsi_id) {
                    self::$provinsiIdCache[$item->kabupaten_id] = $item->kabupaten->provinsi_id;
                }
                self::$pdrbKabupatenBySektorCache[$item->kabupaten_id . '_' . $item->sektor_id . '_' . $item->tahun] = $item;
            }
        }

        // 2. Preload PdrbSumut (Provinsi) totals & rows
        $provQuery = PdrbSumut::with('sektor');
        if (!empty($years)) {
            $provQuery->whereIn('tahun', $years);
        }
        $allProv = $provQuery->get();

        foreach ($allProv->groupBy(fn($item) => $item->provinsi_id . '_' . $item->tahun) as $key => $items) {
            self::$pdrbProvinsiByTahunCache[$key] = $items;
            self::$totalProvinsiCache[$key] = (float) $items->sum('nilai_pdrb');
            foreach ($items as $item) {
                self::$pdrbProvinsiCache[$item->provinsi_id . '_' . $item->sektor_id . '_' . $item->tahun] = $item;
            }
        }

        // 3. Preload PdbNasional totals & rows
        $nasQuery = PdbNasional::with('sektor');
        if (!empty($years)) {
            $nasQuery->whereIn('tahun', $years);
        }
        $allNas = $nasQuery->get();

        foreach ($allNas->groupBy('tahun') as $thn => $items) {
            self::$pdbNasionalByTahunCache[$thn] = $items;
            self::$totalNasionalCache[$thn] = (float) $items->sum('nilai');
        }

        self::$isPreloaded = true;
    }

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
        $key = $kabId . '_' . $tahun;
        if (! isset(self::$totalKabupatenCache[$key])) {
            self::$totalKabupatenCache[$key] = (float) PdrbKabupaten::where('kabupaten_id', $kabId)
                ->where('tahun', $tahun)
                ->sum('nilai_pdrb');
        }

        return self::$totalKabupatenCache[$key];
    }

    /**
     * Total PDRB Provinsi (dari tabel pdrb_sumatera_provinsi)
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
        $key = $kabId . '_' . $tahun;
        if (! isset(self::$pdrbKabupatenCache[$key])) {
            self::$pdrbKabupatenCache[$key] = PdrbKabupaten::with('sektor')
                ->where('kabupaten_id', $kabId)
                ->where('tahun', $tahun)
                ->get();
        }

        return self::$pdrbKabupatenCache[$key];
    }

    /**
     * PDRB sektor provinsi
     */
    protected function getPdrbProvinsi(int $provinsiId, int $sektorId, int $tahun)
    {
        $key = $provinsiId . '_' . $sektorId . '_' . $tahun;
        if (! isset(self::$pdrbProvinsiCache[$key])) {
            self::$pdrbProvinsiCache[$key] = PdrbSumut::where('provinsi_id', $provinsiId)
                ->where('sektor_id', $sektorId)
                ->where('tahun', $tahun)
                ->first();
        }

        return self::$pdrbProvinsiCache[$key];
    }

    /**
     * PDRB sektor kabupaten
     */
    protected function getPdrbKabupatenBySektor(int $kabId, int $sektorId, int $tahun)
    {
        $key = $kabId . '_' . $sektorId . '_' . $tahun;
        if (! isset(self::$pdrbKabupatenBySektorCache[$key])) {
            self::$pdrbKabupatenBySektorCache[$key] = PdrbKabupaten::where('kabupaten_id', $kabId)
                ->where('sektor_id', $sektorId)
                ->where('tahun', $tahun)
                ->first();
        }

        return self::$pdrbKabupatenBySektorCache[$key];
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
        return $this->getPdrbKabupaten($kabId, $tahun);
    }

    protected function getPdbNasionalByTahun(int $tahun)
    {
        if (! isset(self::$pdbNasionalByTahunCache[$tahun])) {
            self::$pdbNasionalByTahunCache[$tahun] = PdbNasional::with('sektor')
                ->where('tahun', $tahun)
                ->get();
        }

        return self::$pdbNasionalByTahunCache[$tahun];
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
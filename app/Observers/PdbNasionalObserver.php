<?php

namespace App\Observers;

use App\Models\PdbNasional;
use App\Services\AnalysisSyncService;
use App\Models\Provinsi;

class PdbNasionalObserver
{
    public function __construct(protected AnalysisSyncService $syncService) {}

    public function saved(PdbNasional $pdb): void
    {
        $provinsis = Provinsi::all();
        foreach ($provinsis as $prov) {
            $this->syncService->syncProvinsi($prov->provinsi_id, $pdb->tahun);
        }
    }

    public function deleted(PdbNasional $pdb): void
    {
        $provinsis = Provinsi::all();
        foreach ($provinsis as $prov) {
            $this->syncService->syncProvinsi($prov->provinsi_id, $pdb->tahun);
        }
    }
}

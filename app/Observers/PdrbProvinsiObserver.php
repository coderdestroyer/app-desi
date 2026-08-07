<?php

namespace App\Observers;

use App\Models\PdrbSumateraProvinsi;
use App\Services\AnalysisSyncService;

class PdrbProvinsiObserver
{
    public function __construct(protected AnalysisSyncService $syncService) {}

    public function saved(PdrbSumateraProvinsi $pdrb): void
    {
        $this->syncService->syncProvinsi($pdrb->provinsi_id, $pdrb->tahun);
    }

    public function deleted(PdrbSumateraProvinsi $pdrb): void
    {
        $this->syncService->syncProvinsi($pdrb->provinsi_id, $pdrb->tahun);
    }
}

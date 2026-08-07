<?php

namespace App\Observers;

use App\Models\PdrbSumateraKabupaten;
use App\Services\AnalysisSyncService;

class PdrbKabupatenObserver
{
    public function __construct(protected AnalysisSyncService $syncService) {}

    public function saved(PdrbSumateraKabupaten $pdrb): void
    {
        $this->syncService->syncKabupaten($pdrb->kabupaten_id, $pdrb->tahun);
    }

    public function deleted(PdrbSumateraKabupaten $pdrb): void
    {
        $this->syncService->syncKabupaten($pdrb->kabupaten_id, $pdrb->tahun);
    }
}

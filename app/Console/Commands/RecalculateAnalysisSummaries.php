<?php

namespace App\Console\Commands;

use App\Services\AnalysisSyncService;
use Illuminate\Console\Command;

class RecalculateAnalysisSummaries extends Command
{
    /**
     * Nama dan tanda tangan command CLI
     */
    protected $signature = 'analysis:recalculate-all';

    /**
     * Deskripsi command
     */
    protected $description = 'Kalkulasi ulang seluruh data analisis (LQ, Klassen, SS, Tipologi Sektor, Indikator) dan simpan ke tabel summary';

    /**
     * Eksekusi command
     */
    public function handle(AnalysisSyncService $syncService): int
    {
        $this->info('Memulai rekalkulasi seluruh analisis makroekonomi...');
        
        $startTime = microtime(true);
        $syncService->syncAll();
        $elapsed = round(microtime(true) - $startTime, 2);

        $this->info("Rekalkulasi selesai dalam {$elapsed} detik!");
        return Command::SUCCESS;
    }
}

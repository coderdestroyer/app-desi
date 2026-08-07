<?php

namespace Database\Seeders;

use App\Services\AnalysisSyncService;
use Illuminate\Database\Seeder;

class AnalysisSummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(AnalysisSyncService $syncService): void
    {
        $this->command->info('Memulai seeding data rekapitulasi analisis...');
        $syncService->syncAll();
        $this->command->info('Data rekapitulasi analisis berhasil di-seed!');
    }
}

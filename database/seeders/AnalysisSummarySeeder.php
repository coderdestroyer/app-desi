<?php

namespace Database\Seeders;

use App\Services\AnalysisSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnalysisSummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(AnalysisSyncService $syncService): void
    {
        $lqCsv = database_path('data/summary_lq_results.csv');

        // Jika CSV sudah di-generate di database/data/, lakukan BATCH SEEDING super cepat (< 1 detik)
        if (file_exists($lqCsv)) {
            $this->command->info('Melakukan fast batch seeding data analisis dari CSV...');
            $this->seedFromCsv('summary_lq_results', database_path('data/summary_lq_results.csv'));
            $this->seedFromCsv('summary_klassen_results', database_path('data/summary_klassen_results.csv'));
            $this->seedFromCsv('summary_shift_share_results', database_path('data/summary_shift_share_results.csv'));
            $this->seedFromCsv('summary_tipologi_sektor_results', database_path('data/summary_tipologi_sektor_results.csv'));
            $this->seedFromCsv('summary_indikator_results', database_path('data/summary_indikator_results.csv'));
            $this->command->info('Fast batch seeding data analisis selesai!');
        } else {
            // Fallback jika file CSV belum ada: hitung secara dinamis
            $this->command->info('File CSV analisis tidak ditemukan, menghitung ulang secara dinamis...');
            $syncService->syncAll();
            $this->command->info('Kalkulasi data rekapitulasi analisis selesai!');
        }
    }

    private function seedFromCsv(string $table, string $filePath): void
    {
        if (!file_exists($filePath)) return;

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return;
        }

        $batch = [];
        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) continue;

            $item = [];
            foreach ($header as $index => $colName) {
                $val = trim($row[$index]);
                if ($val === '') {
                    $item[$colName] = null;
                } elseif (in_array($colName, ['keunggulan_kompetitif', 'spesialisasi'])) {
                    $item[$colName] = ($val === '1' || strtolower($val) === 'true' || strtolower($val) === 't') ? 'true' : 'false';
                } else {
                    $item[$colName] = $val;
                }
            }

            $item['created_at'] = $now;
            $item['updated_at'] = $now;

            $batch[] = $item;

            if (count($batch) >= 500) {
                DB::table($table)->insertOrIgnore($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table($table)->insertOrIgnore($batch);
        }

        fclose($handle);
    }
}

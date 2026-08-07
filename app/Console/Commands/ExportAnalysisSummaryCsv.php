<?php

namespace App\Console\Commands;

use App\Services\AnalysisSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportAnalysisSummaryCsv extends Command
{
    protected $signature = 'analysis:export-csv';

    protected $description = 'Kalkulasi dan ekspor tabel rekapitulasi analisis ke file CSV di database/data/';

    public function handle(AnalysisSyncService $syncService): int
    {
        $this->info('Memulai kalkulasi dan ekspor CSV analisis makroekonomi...');

        // 1. Jalankan sinkronisasi penuh ke database
        $syncService->syncAll();

        // 2. Ekspor summary_lq_results
        $this->exportTableToCsv('summary_lq_results', database_path('data/summary_lq_results.csv'), [
            'tingkat_wilayah', 'provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun', 'nilai_lq', 'kategori', 'persen_daerah', 'persen_acuan'
        ]);

        // 3. Ekspor summary_klassen_results
        $this->exportTableToCsv('summary_klassen_results', database_path('data/summary_klassen_results.csv'), [
            'tingkat_wilayah', 'provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun_awal', 'tahun_akhir', 'growth_daerah', 'growth_pembanding', 'share_daerah', 'share_pembanding', 'kuadran', 'kategori_kuadran'
        ]);

        // 4. Ekspor summary_shift_share_results
        $this->exportTableToCsv('summary_shift_share_results', database_path('data/summary_shift_share_results.csv'), [
            'tingkat_wilayah', 'provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun_awal', 'tahun_akhir', 'n_nij', 'c_cij', 's_sij', 'd_dij', 'keunggulan_kompetitif', 'spesialisasi'
        ]);

        // 5. Ekspor summary_tipologi_sektor_results
        $this->exportTableToCsv('summary_tipologi_sektor_results', database_path('data/summary_tipologi_sektor_results.csv'), [
            'tingkat_wilayah', 'provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun', 'nilai_lq', 'kategori_lq', 'shift_share_net', 'klasifikasi_sektor'
        ]);

        // 6. Ekspor summary_indikator_results
        $this->exportTableToCsv('summary_indikator_results', database_path('data/summary_indikator_results.csv'), [
            'tingkat_wilayah', 'provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun', 'pertumbuhan', 'kontribusi'
        ]);

        $this->info('Ekspor 5 file CSV analisis ke database/data/ berhasil!');
        return Command::SUCCESS;
    }

    private function exportTableToCsv(string $table, string $filePath, array $columns): void
    {
        $handle = fopen($filePath, 'w');
        fputcsv($handle, $columns);

        DB::table($table)->orderBy('id')->chunk(500, function ($rows) use ($handle, $columns) {
            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $col) {
                    $val = $row->{$col};
                    if (is_bool($val)) {
                        $val = $val ? '1' : '0';
                    }
                    $line[] = $val;
                }
                fputcsv($handle, $line);
            }
        });

        fclose($handle);
        $this->info("Berhasil membuat file: " . basename($filePath));
    }
}

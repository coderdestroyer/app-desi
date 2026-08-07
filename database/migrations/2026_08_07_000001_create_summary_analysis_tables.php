<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Summary Location Quotient (LQ)
        Schema::create('summary_lq_results', function (Blueprint $table) {
            $table->id();
            $table->string('tingkat_wilayah', 20); // 'provinsi', 'kabupaten'
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('nilai_lq', 10, 4)->default(0.0000);
            $table->string('kategori', 20); // 'Basis', 'Non Basis'
            $table->decimal('persen_daerah', 10, 4)->default(0.0000);
            $table->decimal('persen_acuan', 10, 4)->default(0.0000);
            $table->timestamps();

            $table->unique(['provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun'], 'unq_summary_lq');
            $table->index(['provinsi_id', 'kabupaten_id'], 'idx_summary_lq_wilayah');
            $table->index('tahun', 'idx_summary_lq_tahun');
            $table->index('kategori', 'idx_summary_lq_kategori');
        });

        // 2. Tabel Summary Tipologi Klassen
        Schema::create('summary_klassen_results', function (Blueprint $table) {
            $table->id();
            $table->string('tingkat_wilayah', 20); // 'provinsi', 'kabupaten'
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnDelete();
            $table->integer('tahun_awal');
            $table->integer('tahun_akhir');
            $table->decimal('growth_daerah', 10, 4)->default(0.0000);
            $table->decimal('growth_pembanding', 10, 4)->default(0.0000);
            $table->decimal('share_daerah', 10, 4)->default(0.0000);
            $table->decimal('share_pembanding', 10, 4)->default(0.0000);
            $table->string('kuadran', 20); // 'Kuadran I', 'Kuadran II', 'Kuadran III', 'Kuadran IV'
            $table->string('kategori_kuadran', 255);
            $table->timestamps();

            $table->unique(['provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun_awal', 'tahun_akhir'], 'unq_summary_klassen');
            $table->index(['provinsi_id', 'kabupaten_id'], 'idx_summary_klassen_wilayah');
            $table->index(['tahun_awal', 'tahun_akhir'], 'idx_summary_klassen_periode');
            $table->index('kuadran', 'idx_summary_klassen_kuadran');
        });

        // 3. Tabel Summary Shift-Share (SS)
        Schema::create('summary_shift_share_results', function (Blueprint $table) {
            $table->id();
            $table->string('tingkat_wilayah', 20); // 'provinsi', 'kabupaten'
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnDelete();
            $table->integer('tahun_awal');
            $table->integer('tahun_akhir');
            $table->decimal('n_nij', 20, 2)->default(0.00); // Pertumbuhan Nasional/Provinsi
            $table->decimal('c_cij', 20, 2)->default(0.00); // Proportional Shift
            $table->decimal('s_sij', 20, 2)->default(0.00); // Differential Shift
            $table->decimal('d_dij', 20, 2)->default(0.00); // Net Change
            $table->boolean('keunggulan_kompetitif')->default(false);
            $table->boolean('spesialisasi')->default(false);
            $table->timestamps();

            $table->unique(['provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun_awal', 'tahun_akhir'], 'unq_summary_shift_share');
            $table->index(['provinsi_id', 'kabupaten_id'], 'idx_summary_ss_wilayah');
            $table->index(['tahun_awal', 'tahun_akhir'], 'idx_summary_ss_periode');
        });

        // 4. Tabel Summary Tipologi Sektor (LQ + Shift Share)
        Schema::create('summary_tipologi_sektor_results', function (Blueprint $table) {
            $table->id();
            $table->string('tingkat_wilayah', 20); // 'provinsi', 'kabupaten'
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('nilai_lq', 10, 4)->default(0.0000);
            $table->string('kategori_lq', 20); // 'Basis', 'Non Basis'
            $table->decimal('shift_share_net', 20, 2)->default(0.00);
            $table->string('klasifikasi_sektor', 100); // 'Sektor Unggulan', 'Sektor Prospektif', 'Sektor Potensial', 'Sektor Tertinggal'
            $table->timestamps();

            $table->unique(['provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun'], 'unq_summary_tipologi_sektor');
            $table->index(['provinsi_id', 'kabupaten_id'], 'idx_summary_tipologi_wilayah');
            $table->index('tahun', 'idx_summary_tipologi_tahun');
            $table->index('klasifikasi_sektor', 'idx_summary_tipologi_klasifikasi');
        });

        // 5. Tabel Summary Indikator Ekonomi (Laju Pertumbuhan & Kontribusi)
        Schema::create('summary_indikator_results', function (Blueprint $table) {
            $table->id();
            $table->string('tingkat_wilayah', 20); // 'provinsi', 'kabupaten'
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('pertumbuhan', 10, 4)->default(0.0000); // Laju Pertumbuhan % (YoY)
            $table->decimal('kontribusi', 10, 4)->default(0.0000); // Kontribusi % terhadap Total PDRB
            $table->timestamps();

            $table->unique(['provinsi_id', 'kabupaten_id', 'sektor_id', 'tahun'], 'unq_summary_indikator');
            $table->index(['provinsi_id', 'kabupaten_id'], 'idx_summary_indikator_wilayah');
            $table->index('tahun', 'idx_summary_indikator_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_indikator_results');
        Schema::dropIfExists('summary_tipologi_sektor_results');
        Schema::dropIfExists('summary_shift_share_results');
        Schema::dropIfExists('summary_klassen_results');
        Schema::dropIfExists('summary_lq_results');
    }
};

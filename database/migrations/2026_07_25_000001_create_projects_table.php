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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Relasi ke Master Data DPMPTSP (Penyesuaian Integrasi)
            $table->unsignedBigInteger('kabupaten_id')->nullable()->comment('Relasi ke Kabupaten/Kota');
            $table->unsignedBigInteger('sektor_id')->nullable()->comment('Relasi ke Sektor Ekonomi');
            $table->unsignedBigInteger('lokasi_id')->nullable()->comment('Relasi ke Koordinat GIS Lokasi');

            $table->string('nama_proyek');
            $table->text('deskripsi')->nullable();
            $table->integer('tahun_awal');
            $table->integer('jangka_waktu_tahun');
            $table->string('status_publikasi', 20)->default('published')->comment('Status draf atau terbit');

            // Parameter P&L (Profit & Loss)
            $table->decimal('pl_persentase_pajak_penghasilan', 5, 2)->default(0)->nullable()->comment('Pajak PPh %');
            $table->decimal('pl_persentase_pajak_daerah', 5, 2)->default(0)->nullable()->comment('Pajak Daerah %');
            $table->decimal('pl_persentase_bot_bgs_fee', 5, 2)->default(0)->nullable()->comment('BOT/BGS Fee %');
            $table->decimal('pl_nominal_bunga', 20, 2)->default(0)->nullable()->comment('Nominal Bunga');
            $table->decimal('pl_nominal_depresiasi', 20, 2)->default(0)->nullable()->comment('Nominal Depresiasi');

            // Parameter Skema Pembiayaan & Arus Kas
            $table->decimal('rasio_modal_sendiri', 5, 2)->default(60.00)->comment('Porsi Modal Sendiri Equity %');
            $table->decimal('rasio_pinjaman_kredit', 5, 2)->default(40.00)->comment('Porsi Pinjaman Kredit Debt %');
            $table->decimal('suku_bunga_kredit', 5, 2)->default(8.05)->comment('Suku Bunga Kredit % per tahun');
            $table->integer('tenor_kredit_tahun')->default(5)->comment('Tenor / Jangka waktu kredit dalam tahun');

            $table->timestamps();

            // Foreign Key Constraints ke Master Data DPMPTSP
            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->nullOnDelete();
            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->nullOnDelete();
            $table->foreign('lokasi_id')->references('id')->on('lokasi')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

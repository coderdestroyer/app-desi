<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('data_investasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_laporan_lkpm')->nullable()->index();
            $table->unsignedBigInteger('id_proyek_nku')->nullable()->index();
            $table->string('nama_perusahaan', 255)->index();
            $table->string('status', 20)->index(); // 'PMDN', 'PMA'
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->nullOnDelete();
            $table->string('nama_sektor', 255)->nullable()->index(); // Sektor LKPM (statis tanpa FK ke master sektor PDRB)
            $table->integer('tahun')->index();
            $table->decimal('nilai_investasi', 20, 2)->default(0);
            $table->timestamps();

            // Composite Indexes untuk Filter & Agregasi Dashboard Cepat
            $table->index(['tahun', 'provinsi_id', 'kabupaten_id']);
            $table->index(['tahun', 'nama_sektor']);
            $table->index(['status', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_investasi');
    }
};

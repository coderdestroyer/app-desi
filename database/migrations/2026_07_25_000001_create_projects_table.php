<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->nullOnDelete();
            $table->foreignId('sektor_id')->nullable()->constrained('sektor', 'sektor_id')->nullOnDelete();
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasi')->nullOnDelete();
            $table->string('nama_proyek');
            $table->text('deskripsi')->nullable();
            $table->integer('tahun_awal');
            $table->integer('jangka_waktu_tahun');
            $table->string('status_publikasi', 20)->default('published');

            $table->decimal('pl_persentase_pajak_penghasilan', 5, 2)->default(0)->nullable();
            $table->decimal('pl_persentase_pajak_daerah', 5, 2)->default(0)->nullable();
            $table->decimal('pl_persentase_bot_bgs_fee', 5, 2)->default(0)->nullable();
            $table->decimal('pl_nominal_bunga', 20, 2)->default(0)->nullable();
            $table->decimal('pl_nominal_depresiasi', 20, 2)->default(0)->nullable();

            $table->decimal('rasio_modal_sendiri', 5, 2)->default(60.00);
            $table->decimal('rasio_pinjaman_kredit', 5, 2)->default(40.00);
            $table->decimal('suku_bunga_kredit', 5, 2)->default(8.05);
            $table->integer('tenor_kredit_tahun')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

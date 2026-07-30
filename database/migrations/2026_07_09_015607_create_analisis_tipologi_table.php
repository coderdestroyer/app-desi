<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('analisis_tipologi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi', 'provinsi_id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tingkat_wilayah', 50)->nullable();
            $table->string('daerah_analisis', 255)->nullable();
            $table->string('daerah_pembanding', 255)->nullable();
            $table->integer('tahun_awal')->nullable();
            $table->integer('tahun_akhir')->nullable();
            $table->integer('tahun')->nullable();
            $table->decimal('nilai_ss', 15, 6)->default(0)->nullable();
            $table->decimal('nilai_lq', 15, 6)->default(0)->nullable();
            $table->string('kuadran', 30)->nullable();
            $table->string('kategori_sektor', 100)->nullable();
            $table->string('tipologi', 100)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('provinsi_id');
            $table->index('kabupaten_id');
            $table->index('sektor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_tipologi');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('analisis_lq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi', 'provinsi_id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('tingkat_wilayah', 50)->nullable();
            $table->string('daerah_analisis', 255)->nullable();
            $table->string('daerah_pembanding', 255)->nullable();
            $table->integer('tahun');
            $table->decimal('pdrb_sektor_analisis', 25, 2)->default(0);
            $table->decimal('total_pdrb_analisis', 25, 2)->default(0);
            $table->decimal('pdrb_sektor_pembanding', 25, 2)->default(0);
            $table->decimal('total_pdrb_pembanding', 25, 2)->default(0);
            $table->decimal('nilai_lq', 15, 6)->default(0);
            $table->string('kategori', 50);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('provinsi_id');
            $table->index('kabupaten_id');
            $table->index('sektor_id');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_lq');
    }
};
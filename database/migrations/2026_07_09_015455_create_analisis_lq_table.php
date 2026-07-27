<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_lq', function (Blueprint $table) {
            $table->id();
            $table->string('tingkat_wilayah', 50);
            $table->string('daerah_analisis', 255);
            $table->string('daerah_pembanding', 255);
            $table->unsignedBigInteger('sektor_id');
            $table->integer('tahun');
            $table->decimal('pdrb_sektor_analisis', 20, 2);
            $table->decimal('total_pdrb_analisis', 20, 2);
            $table->decimal('pdrb_sektor_pembanding', 20, 2);
            $table->decimal('total_pdrb_pembanding', 20, 2);
            $table->decimal('nilai_lq', 10, 4);
            $table->string('kategori', 50);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_lq');
    }
};
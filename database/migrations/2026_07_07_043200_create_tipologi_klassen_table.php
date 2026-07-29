<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_tipologi_klassen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indikator_provinsi_id')->nullable();
            $table->unsignedBigInteger('indikator_kabupaten_id')->nullable();
            $table->unsignedBigInteger('kab_id');
            $table->unsignedBigInteger('sektor_id');
            $table->integer('tahun');
            $table->decimal('laju_pertumbuhan', 10, 5)->nullable();
            $table->decimal('kontribusi_pdrb', 10, 5)->nullable();
            $table->decimal('pertumbuhan_kabupaten', 10, 5)->nullable();
            $table->decimal('pertumbuhan_provinsi', 10, 5)->nullable();
            $table->decimal('kontribusi_kabupaten', 10, 5)->nullable();
            $table->decimal('kontribusi_provinsi', 10, 5)->nullable();
            $table->string('kuadran', 30);
            $table->timestamps();

            $table->foreign('indikator_provinsi_id')->references('id')->on('indikator_provinsi')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('indikator_kabupaten_id')->references('id')->on('indikator_kabupaten')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('kab_id')->references('kab_id')->on('kabupaten')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnUpdate()->cascadeOnDelete();

            $table->unique(['kab_id', 'sektor_id', 'tahun']);
            $table->index('indikator_provinsi_id');
            $table->index('indikator_kabupaten_id');
            $table->index('kab_id');
            $table->index('sektor_id');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_tipologi_klassen');
    }
};
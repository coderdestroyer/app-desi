<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_ss', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kabupaten_id')->nullable();
            $table->unsignedBigInteger('sektor_id');
            $table->integer('tahun_awal');
            $table->integer('tahun_akhir');
            $table->decimal('komponen_n', 20, 2)->default(0);
            $table->decimal('komponen_p', 20, 2)->default(0);
            $table->decimal('komponen_d', 20, 2)->default(0);
            $table->decimal('total_shift', 20, 2)->default(0);
            $table->timestamps();

            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->nullOnDelete();
            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_ss');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_ssa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kabupaten_id');
            $table->unsignedBigInteger('sektor_id');
            $table->integer('tahun_awal');
            $table->integer('tahun_akhir');
            $table->decimal('komponen_n', 15, 2);
            $table->decimal('komponen_p', 15, 2);
            $table->decimal('komponen_d', 15, 2);
            $table->decimal('total_shift', 15, 2);
            $table->timestamps();

            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->cascadeOnDelete();
            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_ssa');
    }
};
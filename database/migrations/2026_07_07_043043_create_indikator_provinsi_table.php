<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indikator_provinsi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provinsi_id');
            $table->integer('tahun');
            $table->string('nama_indikator', 255);
            $table->decimal('nilai', 20, 2);
            $table->string('satuan', 50)->nullable();
            $table->timestamps();

            $table->foreign('provinsi_id')->references('provinsi_id')->on('provinsi')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indikator_provinsi');
    }
};
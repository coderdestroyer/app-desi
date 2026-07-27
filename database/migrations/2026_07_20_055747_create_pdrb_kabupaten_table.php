<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdrb_kabupaten', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kabupaten_id');
            $table->unsignedBigInteger('sektor_id');
            $table->integer('tahun');
            $table->decimal('nilai_pdrb', 20, 2)->default(0);
            $table->timestamps();

            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->cascadeOnDelete();
            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdrb_kabupaten');
    }
};
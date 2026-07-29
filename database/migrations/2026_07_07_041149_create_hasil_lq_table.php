<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_lq', function (Blueprint $table) {
            $table->id('hasil_lq_id');
            $table->unsignedBigInteger('kab_id');
            $table->unsignedBigInteger('sektor_id');
            $table->integer('tahun');
            $table->decimal('nilai_lq', 10, 5);
            $table->string('kategori', 50);
            $table->timestamps();

            $table->foreign('kab_id')->references('kab_id')->on('kabupaten')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnUpdate()->cascadeOnDelete();

            $table->unique(['kab_id', 'sektor_id', 'tahun']);
            $table->index('kab_id');
            $table->index('sektor_id');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_lq');
    }
};
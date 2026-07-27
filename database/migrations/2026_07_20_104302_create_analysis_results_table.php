<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analysis_results', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->unsignedBigInteger('kabupaten_id')->nullable();
            $table->integer('tahun');
            $table->json('payload');
            $table->timestamps();

            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_results');
    }
};

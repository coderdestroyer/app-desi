<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kabupaten_id');
            $table->string('nama_kecamatan', 255)->index();
            $table->timestamps();

            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kecamatan');
    }
};
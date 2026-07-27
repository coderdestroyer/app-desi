<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lokasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kabupaten_id')->nullable();
            $table->string('nama', 255)->index();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->timestamps();

            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi');
    }
};

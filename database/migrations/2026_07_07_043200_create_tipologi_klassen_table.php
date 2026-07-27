<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipologi_klassen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kabupaten_id');
            $table->unsignedBigInteger('sektor_id');
            $table->decimal('laju_pertumbuhan', 8, 4);
            $table->decimal('kontribusi_pdrb', 8, 4);
            $table->string('kuadran', 20);
            $table->timestamps();

            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->cascadeOnDelete();
            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipologi_klassen');
    }
};
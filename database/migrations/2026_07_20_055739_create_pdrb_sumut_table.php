<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdrb_sumut', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sektor_id');
            $table->integer('tahun');
            $table->decimal('nilai_pdrb', 20, 2)->default(0);
            $table->timestamps();

            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdrb_sumut');
    }
};
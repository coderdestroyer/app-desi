<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('indikator_provinsi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->integer('tahun');
            $table->string('nama_indikator', 255);
            $table->decimal('nilai', 10, 4);
            $table->string('satuan', 50)->default('%');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indikator_provinsi');
    }
};
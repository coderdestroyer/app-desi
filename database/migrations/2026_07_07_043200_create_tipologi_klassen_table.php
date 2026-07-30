<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('hasil_tipologi_klassen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_provinsi_id')->nullable()->constrained('indikator_provinsi')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('indikator_kabupaten_id')->nullable()->constrained('indikator_kabupaten')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('kab_id')->constrained('kabupaten', 'kab_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('laju_pertumbuhan', 10, 5)->nullable();
            $table->decimal('kontribusi_pdrb', 10, 5)->nullable();
            $table->decimal('pertumbuhan_kabupaten', 10, 5)->nullable();
            $table->decimal('pertumbuhan_provinsi', 10, 5)->nullable();
            $table->decimal('kontribusi_kabupaten', 10, 5)->nullable();
            $table->decimal('kontribusi_provinsi', 10, 5)->nullable();
            $table->string('kuadran', 30);
            $table->timestamps();

            $table->unique(['kab_id', 'sektor_id', 'tahun']);
            $table->index('indikator_provinsi_id');
            $table->index('indikator_kabupaten_id');
            $table->index('kab_id');
            $table->index('sektor_id');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_tipologi_klassen');
    }
};
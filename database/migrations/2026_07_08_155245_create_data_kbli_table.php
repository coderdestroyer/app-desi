<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('data_kbli', function (Blueprint $table) {
            $table->id();
            $table->string('struktur', 20)->index();
            $table->unsignedSmallInteger('level');
            $table->string('kode', 10)->unique();
            $table->string('kode_induk', 10)->nullable()->index();
            $table->string('kategori_kode', 2)->nullable();
            $table->string('golongan_pokok_kode', 2)->nullable();
            $table->string('golongan_kode', 3)->nullable();
            $table->string('subgolongan_kode', 4)->nullable();
            $table->string('kelompok_kode', 5)->nullable();
            $table->text('judul');
            $table->text('cakupan')->nullable();
            $table->text('tidak_cakupan')->nullable();
            $table->string('no_asli', 20)->nullable();
            $table->string('kode_asli', 10)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_kbli');
    }
};
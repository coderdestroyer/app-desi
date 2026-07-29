<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analisis_ss', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger('provinsi_id')->nullable();
            $table->unsignedBigInteger('kabupaten_id')->nullable();
            $table->unsignedBigInteger('sektor_id');
            $table->string('tingkat_wilayah', 50)->nullable();
            $table->string('daerah_analisis', 255)->nullable();
            $table->string('daerah_pembanding', 255)->nullable();
            $table->integer('tahun_awal');
            $table->integer('tahun_akhir');
            $table->decimal('rij', 15, 6)->default(0);
            $table->decimal('rin', 15, 6)->default(0);
            $table->decimal('rn', 15, 6)->default(0);
            $table->decimal('nij', 30, 10)->default(0);
            $table->decimal('mij', 30, 10)->default(0);
            $table->decimal('cij', 30, 10)->default(0);
            $table->decimal('dij', 30, 10)->default(0);
            $table->decimal('komponen_n', 20, 2)->default(0);
            $table->decimal('komponen_p', 20, 2)->default(0);
            $table->decimal('komponen_d', 20, 2)->default(0);
            $table->decimal('total_shift', 20, 2)->default(0);
            $table->string('status_pertumbuhan', 100)->nullable();
            $table->string('status_daya_saing', 100)->nullable();
            $table->timestamps();

            $table->foreign('provinsi_id')->references('provinsi_id')->on('provinsi')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('kabupaten_id')->references('kab_id')->on('kabupaten')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('sektor_id')->references('sektor_id')->on('sektor')->cascadeOnUpdate()->cascadeOnDelete();

            $table->index('user_id');
            $table->index('provinsi_id');
            $table->index('kabupaten_id');
            $table->index('sektor_id');
            $table->index('tahun_awal');
            $table->index('tahun_akhir');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analisis_ss');
    }
};
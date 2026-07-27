<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel kabupaten.
     */
    public function up(): void
    {
        Schema::create('kabupaten', function (Blueprint $table) {
            $table->id('kab_id');
            $table->unsignedBigInteger('provinsi_id');
            $table->string('nama_kabupaten', 255)->index();
            $table->timestamps();

            $table->foreign('provinsi_id')
                ->references('provinsi_id')
                ->on('provinsi')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('provinsi_id');
        });
    }

    /**
     * Menghapus tabel kabupaten.
     */
    public function down(): void
    {
        Schema::dropIfExists('kabupaten');
    }
};
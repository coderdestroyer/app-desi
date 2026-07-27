<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel sektor.
     */
    public function up(): void
    {
        Schema::create('sektor', function (Blueprint $table) {
            $table->id('sektor_id');
            $table->string('nama_sektor', 255)->index();
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel sektor.
     */
    public function down(): void
    {
        Schema::dropIfExists('sektor');
    }
};
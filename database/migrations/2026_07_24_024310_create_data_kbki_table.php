<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_kbki', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('kode_induk', 20)->nullable();
            $table->unsignedSmallInteger('level');
            $table->text('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->index('kode_induk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_kbki');
    }
};
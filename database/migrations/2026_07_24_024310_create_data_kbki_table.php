<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('data_kbki', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->string('kode_induk', 20)->nullable()->index();
            $table->unsignedSmallInteger('level');
            $table->text('nama');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_kbki');
    }
};
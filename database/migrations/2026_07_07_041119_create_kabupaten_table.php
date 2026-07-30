<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('kabupaten', function (Blueprint $table) {
            $table->id('kab_id');
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nama_kabupaten', 255)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kabupaten');
    }
};
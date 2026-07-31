<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('data_hs_code', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kategori', 50)->nullable();
            $table->string('kode_kelompok', 50)->nullable();
            $table->text('uraian_kelompok')->nullable();
            $table->string('kode_subkelompok', 50)->nullable();
            $table->text('uraian_subkelompok')->nullable();
            $table->string('hs_code', 100)->nullable();
            $table->text('uraian_barang')->nullable();

            $table->string('code', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('category')->nullable();
            $table->timestamps();

            $table->index('hs_code');
            $table->index('kode_kategori');
            $table->index('kode_kelompok');
            $table->index('kode_subkelompok');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_hs_code');
    }
};
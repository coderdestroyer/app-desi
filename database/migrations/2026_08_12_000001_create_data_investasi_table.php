<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('data_investasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->integer('tahun')->index();
            $table->decimal('nilai_investasi', 20, 2)->default(0);
            $table->timestamps();

            $table->index(['tahun', 'provinsi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_investasi');
    }
};

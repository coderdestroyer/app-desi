<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('pdrb_sumatera_provinsi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provinsi_id')->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('nilai_pdrb', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdrb_sumatera_provinsi');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('pdb_nasional', function (Blueprint $table) {
            $table->id();
            $table->string('kode_wilayah', 10)->default('00');
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('nilai', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdb_nasional');
    }
};

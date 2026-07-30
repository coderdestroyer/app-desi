<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('hasil_lq', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kab_id')->constrained('kabupaten', 'kab_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('nilai_lq', 10, 5);
            $table->string('kategori', 50);
            $table->timestamps();

            $table->unique(['kab_id', 'sektor_id', 'tahun']);
            $table->index('kab_id');
            $table->index('sektor_id');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_lq');
    }
};
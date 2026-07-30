<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('hasil_tipologi_sektor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hasil_lq_id')->nullable()->constrained('hasil_lq')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('hasil_ssa_id')->nullable()->constrained('hasil_ssa')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('kab_id')->constrained('kabupaten', 'kab_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('lq', 10, 5)->nullable();
            $table->decimal('cij', 20, 5)->nullable();
            $table->string('kuadran', 30);
            $table->string('kategori_sektor', 100)->nullable();
            $table->timestamps();

            $table->unique(['kab_id', 'sektor_id', 'tahun']);
            $table->index('hasil_lq_id');
            $table->index('hasil_ssa_id');
            $table->index('kab_id');
            $table->index('sektor_id');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_tipologi_sektor');
    }
};
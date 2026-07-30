<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('hasil_ssa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kab_id')->constrained('kabupaten', 'kab_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('sektor_id')->constrained('sektor', 'sektor_id')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('rn', 20, 5)->default(0);
            $table->decimal('rin', 20, 5)->default(0);
            $table->decimal('rij', 20, 5)->default(0);
            $table->decimal('mij', 20, 5)->default(0);
            $table->decimal('cij', 20, 5)->default(0);
            $table->decimal('nij', 20, 5)->nullable();
            $table->decimal('dij', 20, 5)->nullable();
            $table->decimal('komponen_n', 20, 5)->default(0);
            $table->decimal('komponen_p', 20, 5)->default(0);
            $table->decimal('komponen_d', 20, 5)->default(0);
            $table->decimal('total_shift', 20, 5)->default(0);
            $table->string('kategori_pertumbuhan', 100)->nullable();
            $table->string('kategori_daya_saing', 100)->nullable();
            $table->text('periode')->nullable();
            $table->timestamps();

            $table->unique(['kab_id', 'sektor_id', 'tahun']);
            $table->index('kab_id');
            $table->index('sektor_id');
            $table->index('tahun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_ssa');
    }
};
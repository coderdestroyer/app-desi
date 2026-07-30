<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('capex_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('capex_components')->cascadeOnDelete();
            $table->string('nama_komponen');
            $table->decimal('volume', 15, 2)->nullable();
            $table->string('satuan', 50)->nullable();
            $table->decimal('luas', 15, 2)->nullable();
            $table->decimal('harga_m2', 20, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capex_components');
    }
};

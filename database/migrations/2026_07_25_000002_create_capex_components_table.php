<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('capex_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('capex_components')->onDelete('cascade');
            $table->string('nama_komponen');
            $table->decimal('volume', 15, 4)->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('luas', 15, 4)->nullable();
            $table->decimal('harga_m2', 20, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capex_components');
    }
};

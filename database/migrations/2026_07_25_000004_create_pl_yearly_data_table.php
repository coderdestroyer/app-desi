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
        Schema::create('pl_yearly_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pl_component_id')->constrained('pl_components')->onDelete('cascade');
            $table->integer('tahun_ke'); // 1, 2, 3...
            $table->decimal('nilai', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['pl_component_id', 'tahun_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pl_yearly_data');
    }
};

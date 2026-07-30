<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('pl_yearly_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pl_component_id')->constrained('pl_components')->cascadeOnDelete();
            $table->integer('tahun_ke');
            $table->decimal('nilai', 20, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['pl_component_id', 'tahun_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pl_yearly_data');
    }
};

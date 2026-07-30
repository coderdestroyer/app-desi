<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('pl_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('tipe_kategori', 30);
            $table->foreignId('parent_id')->nullable()->constrained('pl_components')->cascadeOnDelete();
            $table->string('nama_komponen');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pl_components');
    }
};

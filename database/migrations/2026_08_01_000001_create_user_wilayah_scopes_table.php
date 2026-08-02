<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('user_wilayah_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provinsi_id')->nullable()->constrained('provinsi', 'provinsi_id')->cascadeOnDelete();
            $table->foreignId('kabupaten_id')->nullable()->constrained('kabupaten', 'kab_id')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'provinsi_id', 'kabupaten_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wilayah_scopes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // Handled directly in data_kbli and data_kbki tables
    }

    public function down(): void
    {
        // Handled directly in data_kbli and data_kbki tables
    }
};
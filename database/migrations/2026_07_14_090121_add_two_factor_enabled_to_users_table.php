<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        // Handled directly in create_users_table
    }

    public function down(): void
    {
        // Handled directly in create_users_table
    }
};

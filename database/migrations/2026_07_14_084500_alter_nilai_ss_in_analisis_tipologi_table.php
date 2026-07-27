<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analisis_tipologi') && !Schema::hasColumn('analisis_tipologi', 'nilai_ss')) {
            Schema::table('analisis_tipologi', function (Blueprint $table) {
                $table->decimal('nilai_ss', 15, 2)->nullable()->after('kategori_sektor');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('analisis_tipologi') && Schema::hasColumn('analisis_tipologi', 'nilai_ss')) {
            Schema::table('analisis_tipologi', function (Blueprint $table) {
                $table->dropColumn('nilai_ss');
            });
        }
    }
};

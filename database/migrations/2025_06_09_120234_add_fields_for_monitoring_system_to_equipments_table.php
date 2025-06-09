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
        Schema::table('equipments', function (Blueprint $table) {
            // Tambahkan kolom type jika belum ada
            if (!Schema::hasColumn('equipments', 'type')) {
                $table->string('type')->default('non-elektrik')->comment('elektrik atau non-elektrik');
            }
            
            // Tambahkan kolom priority jika belum ada
            if (!Schema::hasColumn('equipments', 'priority')) {
                $table->string('priority')->default('hijau')->comment('merah, kuning, hijau');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            if (Schema::hasColumn('equipments', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('equipments', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
};

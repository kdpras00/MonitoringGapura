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
            // Tambahkan kolom last_maintenance_date jika belum ada
            if (!Schema::hasColumn('equipments', 'last_maintenance_date')) {
                $table->date('last_maintenance_date')->nullable();
            }
            
            // Tambahkan kolom next_maintenance_date jika belum ada
            if (!Schema::hasColumn('equipments', 'next_maintenance_date')) {
                $table->date('next_maintenance_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            // Hapus kolom jika ada
            if (Schema::hasColumn('equipments', 'last_maintenance_date')) {
                $table->dropColumn('last_maintenance_date');
            }
            
            if (Schema::hasColumn('equipments', 'next_maintenance_date')) {
                $table->dropColumn('next_maintenance_date');
            }
        });
    }
}; 
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddEquipmentTypeAndPriorityToMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Tambahkan kolom equipment_type jika belum ada
            if (!Schema::hasColumn('maintenances', 'equipment_type')) {
                $table->string('equipment_type')->nullable();
            }
            
            // Tambahkan kolom priority jika belum ada
            if (!Schema::hasColumn('maintenances', 'priority')) {
                $table->string('priority')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            // Hapus kolom jika ada
            if (Schema::hasColumn('maintenances', 'equipment_type')) {
                $table->dropColumn('equipment_type');
            }
            
            if (Schema::hasColumn('maintenances', 'priority')) {
                $table->dropColumn('priority');
            }
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inspections', function (Blueprint $table) {
            // Tambahkan kolom maintenance_id jika belum ada
            if (!Schema::hasColumn('inspections', 'maintenance_id')) {
                $table->unsignedBigInteger('maintenance_id')->nullable()->after('equipment_id');
                
                // Tambahkan foreign key constraint dengan onDelete cascade
                $table->foreign('maintenance_id')
                      ->references('id')
                      ->on('maintenances')
                      ->onDelete('cascade');
            }
        });
        
        // Update data yang sudah ada berdasarkan struktur tabel
        try {
            $hasTechnicianId = Schema::hasColumn('maintenances', 'technician_id');
            
            if ($hasTechnicianId) {
                // Jika technician_id ada di tabel maintenances, gunakan JOIN berdasarkan equipment_id dan technician_id
                DB::statement("
                    UPDATE inspections i
                    JOIN maintenances m ON i.equipment_id = m.equipment_id AND i.technician_id = m.technician_id
                    SET i.maintenance_id = m.id
                    WHERE i.maintenance_id IS NULL
                ");
            } else {
                // Jika tidak, JOIN hanya berdasarkan equipment_id
                DB::statement("
                    UPDATE inspections i
                    JOIN maintenances m ON i.equipment_id = m.equipment_id
                    SET i.maintenance_id = m.id
                    WHERE i.maintenance_id IS NULL
                ");
            }
        } catch (\Exception $e) {
            // Log error
            if (Schema::hasTable('migration_logs')) {
                DB::table('migration_logs')->insert([
                    'migration' => '2025_06_30_133231_add_maintenance_id_to_inspections',
                    'error' => $e->getMessage(),
                    'created_at' => now()
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu melakukan rollback
    }
};

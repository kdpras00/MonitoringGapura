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
        // Periksa struktur tabel terlebih dahulu
        if (!Schema::hasColumn('maintenances', 'technician_id')) {
            // Jika kolom technician_id tidak ada, tidak ada yang perlu diupdate
            return;
        }
        
        try {
            // Update status maintenance yang belum diverifikasi menjadi 'pending'
            DB::table('maintenances')
                ->where('status', 'completed')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('inspections')
                        ->whereRaw('inspections.equipment_id = maintenances.equipment_id')
                        ->whereRaw('inspections.technician_id = maintenances.technician_id')
                        ->where('inspections.status', 'verified');
                })
                ->update(['status' => 'pending']);
        } catch (\Exception $e) {
            // Tangani error jika SQL gagal dijalankan
            DB::table('migration_logs')->insert([
                'migration' => '2025_06_30_000000_update_maintenance_status',
                'error' => $e->getMessage(),
                'created_at' => now()
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak ada yang perlu di-rollback
    }
}; 
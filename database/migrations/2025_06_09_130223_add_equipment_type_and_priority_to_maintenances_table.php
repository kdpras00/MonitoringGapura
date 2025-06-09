<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Equipment;
use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->string('equipment_type')->nullable()->after('technician_id');
            $table->string('priority')->nullable()->after('equipment_type');
        });
        
        // Update data yang sudah ada dari equipment terkait
        $this->updateExistingData();
    }
    
    /**
     * Update data yang sudah ada
     */
    private function updateExistingData(): void
    {
        // Ambil semua maintenance yang memiliki equipment_id
        $maintenances = DB::table('maintenances')
            ->whereNotNull('equipment_id')
            ->get();
            
        foreach ($maintenances as $maintenance) {
            // Ambil equipment terkait
            $equipment = DB::table('equipments')
                ->where('id', $maintenance->equipment_id)
                ->first();
                
            if ($equipment) {
                // Update maintenance dengan data dari equipment
                DB::table('maintenances')
                    ->where('id', $maintenance->id)
                    ->update([
                        'equipment_type' => $equipment->type,
                        'priority' => $equipment->priority
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenances', function (Blueprint $table) {
            $table->dropColumn('equipment_type');
            $table->dropColumn('priority');
        });
    }
};

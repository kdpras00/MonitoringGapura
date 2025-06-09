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
        Schema::create('predictive_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('cascade');
            $table->timestamp('last_maintenance_date')->nullable();
            $table->timestamp('next_maintenance_date')->nullable();
            $table->float('condition_score')->nullable()->comment('Skor kondisi peralatan (0-100)');
            $table->string('recommendation')->nullable();
            $table->timestamps();
        });
        
        // Isi data awal berdasarkan equipment dan maintenance yang ada
        $this->seedInitialData();
    }
    
    /**
     * Seed initial data for predictive maintenance
     */
    private function seedInitialData(): void
    {
        // Ambil semua equipment
        $equipments = DB::table('equipments')->get();
        
        $now = now();
        
        foreach ($equipments as $equipment) {
            // Cari maintenance terakhir untuk equipment ini
            $lastMaintenance = DB::table('maintenances')
                ->where('equipment_id', $equipment->id)
                ->where('status', 'completed')
                ->orderBy('actual_date', 'desc')
                ->first();
            
            $lastMaintenanceDate = $lastMaintenance ? $lastMaintenance->actual_date : null;
            
            // Generate prediksi tanggal maintenance berikutnya
            $nextMaintenanceDate = $lastMaintenanceDate 
                ? date('Y-m-d H:i:s', strtotime($lastMaintenanceDate . ' + ' . rand(30, 90) . ' days')) 
                : date('Y-m-d H:i:s', strtotime('+ ' . rand(10, 30) . ' days'));
            
            // Generate skor kondisi acak (untuk demo)
            $conditionScore = rand(50, 95);
            
            // Generate rekomendasi berdasarkan skor
            $recommendation = $conditionScore > 80 
                ? 'Routine maintenance recommended' 
                : ($conditionScore > 60 
                    ? 'Inspection needed' 
                    : 'Immediate maintenance required');
            
            // Insert data
            DB::table('predictive_maintenances')->insert([
                'equipment_id' => $equipment->id,
                'last_maintenance_date' => $lastMaintenanceDate,
                'next_maintenance_date' => $nextMaintenanceDate,
                'condition_score' => $conditionScore,
                'recommendation' => $recommendation,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictive_maintenances');
    }
};

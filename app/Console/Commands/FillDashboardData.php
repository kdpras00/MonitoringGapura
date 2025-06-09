<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\PredictiveMaintenance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FillDashboardData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:fill-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengisi data untuk dashboard Filament (maintenance calendar dan predictive maintenance)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai mengisi data dashboard...');

        // Bagian 1: Perbarui data maintenance untuk kalender
        $this->fillMaintenanceData();
        
        // Bagian 2: Isi tabel predictive_maintenances
        $this->fillPredictiveMaintenanceData();
        
        $this->info('Selesai mengisi data dashboard!');
        
        return 0;
    }
    
    private function fillMaintenanceData()
    {
        $this->info('Mengisi data maintenance untuk kalender...');
        
        $equipments = Equipment::all();
        $now = Carbon::now();
        
        // Periksa jika maintenance kosong
        if (Maintenance::count() < 5) {
            $this->warn('Data maintenance tidak cukup. Menambahkan data contoh...');
            
            // Hapus semua data lama jika kurang dari 5
            if (Maintenance::count() > 0) {
                $this->warn('Menghapus ' . Maintenance::count() . ' data maintenance lama');
                DB::table('maintenances')->delete();
            }
            
            // Tambah 10 maintenance untuk kalender
            foreach (range(1, 10) as $i) {
                $equipment = $equipments->random();
                $startDate = $now->copy()->addDays(rand(-5, 15))->format('Y-m-d H:i:s');
                
                $status = ['scheduled', 'in-progress', 'completed'][rand(0, 2)];
                
                $maintenanceData = [
                    'equipment_id' => $equipment->id,
                    'schedule_date' => $startDate,
                    'next_service_date' => Carbon::parse($startDate)->addMonths(1)->format('Y-m-d H:i:s'),
                    'technician_id' => 1, // Default user/teknisi
                    'maintenance_type' => ['preventive', 'corrective'][rand(0, 1)],
                    'status' => $status,
                    'result' => $status == 'completed' ? ['good', 'partial', 'failed'][rand(0, 2)] : null,
                    'cost' => rand(50, 500) * 1000,
                    'notes' => 'Maintenance untuk ' . $equipment->name,
                    'equipment_type' => $equipment->type,
                    'priority' => $equipment->priority,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                
                if ($status == 'completed') {
                    $maintenanceData['actual_date'] = Carbon::parse($startDate)->subDays(rand(0, 3))->format('Y-m-d H:i:s');
                    $maintenanceData['approval_status'] = ['pending', 'approved', 'rejected'][rand(0, 2)];
                }
                
                DB::table('maintenances')->insert($maintenanceData);
                $this->line('Menambahkan maintenance untuk ' . $equipment->name);
            }
            
            $this->info('Berhasil menambahkan 10 data maintenance contoh.');
        } else {
            $this->info('Data maintenance tersedia: ' . Maintenance::count() . ' entri.');
            
            // Pastikan semua maintenance memiliki equipment_type dan priority
            $updated = 0;
            foreach (Maintenance::whereNull('equipment_type')->orWhereNull('priority')->get() as $maintenance) {
                if ($maintenance->equipment) {
                    $maintenance->equipment_type = $maintenance->equipment->type;
                    $maintenance->priority = $maintenance->equipment->priority;
                    $maintenance->save();
                    $updated++;
                }
            }
            
            if ($updated > 0) {
                $this->info("Memperbaiki $updated maintenance dengan data equipment.");
            }
        }
    }
    
    private function fillPredictiveMaintenanceData()
    {
        $this->info('Mengisi data predictive maintenance...');
        
        // Periksa apakah tabel predictive_maintenances ada
        if (!Schema::hasTable('predictive_maintenances')) {
            $this->error('Tabel predictive_maintenances tidak ditemukan! Jalankan migrasi terlebih dahulu.');
            $this->warn('php artisan migrate');
            return;
        }
        
        // Periksa data yang ada
        $count = DB::table('predictive_maintenances')->count();
        if ($count > 0) {
            $this->info("Data predictive maintenance tersedia: $count entri.");
            return;
        }
        
        $this->warn('Tidak ada data predictive maintenance. Menambahkan data...');
        
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
            
            $this->line('Menambahkan predictive maintenance untuk equipment ID ' . $equipment->id);
        }
        
        $this->info('Berhasil menambahkan data predictive maintenance untuk ' . count($equipments) . ' equipment.');
    }
} 
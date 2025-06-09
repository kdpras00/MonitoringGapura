<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;

class MaintenanceResultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dapatkan semua maintenance dengan status completed
        $completedMaintenances = Maintenance::where('status', 'completed')->get();
        
        if ($completedMaintenances->count() > 0) {
            foreach ($completedMaintenances as $index => $maintenance) {
                // Gunakan modulus untuk memberi variasi pada nilai result
                $resultValue = $index % 3;
                
                switch ($resultValue) {
                    case 0:
                        $result = 'good';
                        break;
                    case 1:
                        $result = 'partial';
                        break;
                    case 2:
                        $result = 'failed';
                        break;
                }
                
                // Update maintenance dengan nilai result
                $maintenance->result = $result;
                $maintenance->save();
            }
            
            $this->command->info('Data result maintenance berhasil diperbarui!');
        } else {
            $this->command->info('Tidak ada maintenance dengan status completed. Tidak ada data yang diperbarui.');
        }
    }
} 
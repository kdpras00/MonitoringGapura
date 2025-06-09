<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Maintenance;
use App\Models\Equipment;

class SyncMaintenanceEquipmentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:sync-equipment-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi jenis alat dan prioritas di data maintenance dari equipment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi data maintenance...');
        
        // Cari semua maintenance yang memiliki equipment_id tetapi equipment_type atau priority kosong
        $maintenances = Maintenance::whereNotNull('equipment_id')
            ->where(function ($query) {
                $query->whereNull('equipment_type')
                    ->orWhereNull('priority')
                    ->orWhere('equipment_type', '')
                    ->orWhere('priority', '');
            })->get();
        
        $this->info('Ditemukan ' . count($maintenances) . ' maintenance yang perlu diupdate.');
        
        $updated = 0;
        $failed = 0;
        
        $progressBar = $this->output->createProgressBar(count($maintenances));
        $progressBar->start();
        
        foreach ($maintenances as $maintenance) {
            try {
                $equipment = Equipment::find($maintenance->equipment_id);
                
                if ($equipment) {
                    $maintenance->equipment_type = $equipment->type;
                    $maintenance->priority = $equipment->priority;
                    $maintenance->save();
                    $updated++;
                } else {
                    $this->warn('Equipment ID ' . $maintenance->equipment_id . ' tidak ditemukan untuk maintenance ID ' . $maintenance->id);
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error('Error saat update maintenance ID ' . $maintenance->id . ': ' . $e->getMessage());
                $failed++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        // Cari juga semua maintenance yang tidak memiliki equipment_id
        $noEquipmentMaintenances = Maintenance::whereNull('equipment_id')->count();
        if ($noEquipmentMaintenances > 0) {
            $this->warn('Ada ' . $noEquipmentMaintenances . ' maintenance tanpa equipment_id.');
        }
        
        $this->info('Sinkronisasi selesai.');
        $this->info('Total updated: ' . $updated);
        $this->info('Total gagal: ' . $failed);
        
        return 0;
    }
}

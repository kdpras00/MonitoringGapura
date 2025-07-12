<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inspection;
use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;

class CleanOrphanedInspections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspections:clean {--dry-run : Tampilkan inspection yang akan dihapus tanpa menghapusnya}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan inspection yang tidak terkait dengan maintenance yang valid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai membersihkan inspection yang tidak terkait dengan maintenance yang valid...');
        
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info('Mode dry-run aktif. Tidak ada perubahan yang akan dilakukan.');
        }
        
        // 1. Temukan inspection yang tidak terkait dengan maintenance apapun (maintenance_id is null)
        $orphanedInspections1 = Inspection::whereNull('maintenance_id')
            ->get();
            
        if ($orphanedInspections1->count() > 0) {
            $this->info('Inspection yang tidak memiliki maintenance_id: ' . $orphanedInspections1->count());
            
            foreach ($orphanedInspections1 as $inspection) {
                $equipmentName = $inspection->equipment ? $inspection->equipment->name : 'Unknown';
                $this->line("ID: {$inspection->id}, Equipment: {$equipmentName}, Tanggal: {$inspection->inspection_date}");
            }
            
            if (!$isDryRun) {
                $deletedCount = Inspection::whereNull('maintenance_id')->delete();
                $this->info("Berhasil menghapus {$deletedCount} inspection yang tidak terkait dengan maintenance.");
            }
        } else {
            $this->info('Tidak ada inspection yang tidak memiliki maintenance_id.');
        }
        
        // 2. Temukan inspection yang terkait dengan maintenance yang tidak memiliki status sesuai kriteria
        $orphanedInspections2 = Inspection::whereHas('maintenance', function ($query) {
                $query->whereNotIn('status', ['assigned', 'in-progress', 'pending-verification'])
                      ->orWhereNull('technician_id');
            })
            ->get();
            
        if ($orphanedInspections2->count() > 0) {
            $this->info('Inspection yang terkait dengan maintenance tidak valid: ' . $orphanedInspections2->count());
            
            foreach ($orphanedInspections2 as $inspection) {
                $maintenance = $inspection->maintenance;
                $equipmentName = $inspection->equipment ? $inspection->equipment->name : 'Unknown';
                $this->line("ID: {$inspection->id}, Equipment: {$equipmentName}, Maintenance status: {$maintenance->status}");
            }
            
            if (!$isDryRun) {
                $inspectionIds = $orphanedInspections2->pluck('id')->toArray();
                $deletedCount = Inspection::whereIn('id', $inspectionIds)->delete();
                $this->info("Berhasil menghapus {$deletedCount} inspection yang terkait dengan maintenance tidak valid.");
            }
        } else {
            $this->info('Tidak ada inspection yang terkait dengan maintenance tidak valid.');
        }
        
        // 3. Temukan inspection yang maintenance-nya sudah tidak ada (ID tidak valid)
        $orphanedInspections3 = Inspection::whereNotNull('maintenance_id')
            ->whereDoesntHave('maintenance')
            ->get();
            
        if ($orphanedInspections3->count() > 0) {
            $this->info('Inspection yang maintenance-nya tidak ditemukan: ' . $orphanedInspections3->count());
            
            foreach ($orphanedInspections3 as $inspection) {
                $equipmentName = $inspection->equipment ? $inspection->equipment->name : 'Unknown';
                $this->line("ID: {$inspection->id}, Equipment: {$equipmentName}, Maintenance ID: {$inspection->maintenance_id}");
            }
            
            if (!$isDryRun) {
                $deletedCount = Inspection::whereNotNull('maintenance_id')
                    ->whereDoesntHave('maintenance')
                    ->delete();
                $this->info("Berhasil menghapus {$deletedCount} inspection yang maintenance-nya tidak ditemukan.");
            }
        } else {
            $this->info('Tidak ada inspection yang maintenance-nya tidak ditemukan.');
        }
        
        $this->info('Pembersihan inspection selesai!');
        
        return 0;
    }
}

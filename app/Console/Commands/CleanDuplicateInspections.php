<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inspection;
use Illuminate\Support\Facades\DB;

class CleanDuplicateInspections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspections:clean-duplicates {--dry-run : Tampilkan data tanpa menghapus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membersihkan inspeksi duplikat berdasarkan equipment_id, technician_id, dan status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mulai mencari inspeksi duplikat...');
        
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info('Mode dry-run aktif. Tidak ada perubahan yang akan dilakukan.');
        }
        
        // Cari inspeksi yang duplikat berdasarkan equipment_id, technician_id, dan status pending
        $duplicates = DB::table('inspections')
            ->select('equipment_id', 'technician_id', 'status', DB::raw('COUNT(*) as total'))
            ->whereIn('status', ['pending']) // Hanya cari yang status pending
            ->groupBy('equipment_id', 'technician_id', 'status')
            ->having('total', '>', 1)
            ->get();
            
        if ($duplicates->count() == 0) {
            $this->info('Tidak ditemukan inspeksi duplikat.');
            return 0;
        }
        
        $this->info('Ditemukan ' . $duplicates->count() . ' kelompok inspeksi duplikat.');
        
        foreach ($duplicates as $duplicate) {
            // Ambil semua inspeksi yang duplikat
            $inspections = Inspection::where('equipment_id', $duplicate->equipment_id)
                ->where('technician_id', $duplicate->technician_id)
                ->where('status', $duplicate->status)
                ->orderBy('id', 'asc')
                ->get();
            
            // Ambil yang paling pertama (ID terkecil) untuk dipertahankan
            $keepInspection = $inspections->first();
            
            // Filter yang akan dihapus (semua kecuali yang paling pertama)
            $deleteInspections = $inspections->filter(function ($item) use ($keepInspection) {
                return $item->id !== $keepInspection->id;
            });
            
            $this->info("Equipment ID {$duplicate->equipment_id}, Teknisi ID {$duplicate->technician_id}, Status: {$duplicate->status}");
            $this->info("  - Mempertahankan: Inspection #{$keepInspection->id}");
            $this->info("  - Menghapus: " . $deleteInspections->pluck('id')->implode(', '));
            
            if (!$isDryRun) {
                // Hapus yang duplikat
                Inspection::whereIn('id', $deleteInspections->pluck('id')->toArray())->delete();
                $this->info("  - " . $deleteInspections->count() . " inspeksi duplikat telah dihapus.");
            }
        }
        
        if ($isDryRun) {
            $this->info('Selesai preview. Jalankan tanpa opsi --dry-run untuk menghapus data duplikat.');
        } else {
            $this->info('Selesai membersihkan inspeksi duplikat.');
        }
        
        return 0;
    }
} 
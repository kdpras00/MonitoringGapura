<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inspection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanOrphanedInspections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-orphaned-inspections {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus inspeksi yang tidak memiliki referensi ke maintenance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orphanedInspections = Inspection::whereNull('maintenance_id')->get();
        $count = $orphanedInspections->count();
        
        if ($count === 0) {
            $this->info('Tidak ada inspeksi orphaned yang perlu dihapus.');
            return;
        }
        
        $this->info("Ditemukan {$count} inspeksi orphaned yang tidak terkait dengan maintenance.");
        
        // Log informasi inspeksi orphaned
        foreach ($orphanedInspections as $inspection) {
            $this->line("ID: {$inspection->id}, Equipment: {$inspection->equipment_id}, Status: {$inspection->status}");
        }
        
        // Konfirmasi penghapusan kecuali jika flag --force digunakan
        if (!$this->option('force') && !$this->confirm('Apakah Anda yakin ingin menghapus semua inspeksi orphaned ini?')) {
            $this->info('Operasi dibatalkan.');
            return;
        }
        
        // Hapus inspeksi orphaned
        $deleted = DB::table('inspections')->whereNull('maintenance_id')->delete();
        
        $this->info("Berhasil menghapus {$deleted} inspeksi orphaned.");
        Log::info("CleanOrphanedInspections: {$deleted} inspeksi orphaned dihapus.");
    }
}

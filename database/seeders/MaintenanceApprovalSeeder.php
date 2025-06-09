<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaintenanceApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada beberapa data maintenance dengan status = completed
        $completedMaintenances = Maintenance::where('status', 'completed')->get();
        
        if ($completedMaintenances->count() > 0) {
            // Update sebagian maintenance untuk menambahkan approval status
            foreach ($completedMaintenances as $index => $maintenance) {
                // Setiap 3 maintenance, variasikan status approval
                if ($index % 3 == 0) {
                    // Pending approval
                    $maintenance->approval_status = 'pending';
                } elseif ($index % 3 == 1) {
                    // Approved
                    $maintenance->approval_status = 'approved';
                    $maintenance->approval_notes = 'Maintenance telah disetujui dan sesuai standar.';
                    $maintenance->approved_by = 'Supervisor';
                    $maintenance->approval_date = Carbon::now()->subDays(rand(1, 10));
                } else {
                    // Rejected
                    $maintenance->approval_status = 'rejected';
                    $maintenance->approval_notes = 'Maintenance ditolak karena belum sesuai standar. Perlu perbaikan.';
                    $maintenance->approved_by = 'Supervisor';
                    $maintenance->approval_date = Carbon::now()->subDays(rand(1, 10));
                }
                
                $maintenance->save();
            }
            
            $this->command->info('Data approval maintenance berhasil ditambahkan!');
        } else {
            $this->command->info('Tidak ada maintenance dengan status completed. Tidak ada data yang diperbarui.');
        }
    }
} 
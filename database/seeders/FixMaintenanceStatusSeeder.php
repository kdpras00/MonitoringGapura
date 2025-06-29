<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixMaintenanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update semua status 'completed' menjadi 'pending' jika belum diverifikasi
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
            
        // Tambahkan log untuk melihat status yang diperbarui
        $maintenanceStatus = DB::table('maintenances')->select('id', 'status')->get();
        foreach ($maintenanceStatus as $maintenance) {
            $this->command->info("Maintenance ID: {$maintenance->id}, Status: {$maintenance->status}");
        }
    }
} 
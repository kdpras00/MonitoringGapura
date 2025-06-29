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
        // Update status maintenance yang belum diverifikasi menjadi 'pending'
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan status ke 'completed'
        DB::table('maintenances')
            ->where('status', 'pending')
            ->update(['status' => 'completed']);
    }
}; 
<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\Maintenance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = Equipment::firstOrCreate(['name' => 'Test Equipment']);

        Maintenance::create([
            'schedule_date' => now(),
            'actual_date'   => now()->addDay(),
            'equipment_id'  => $equipment->id,
            'technician_id' => null, // Atau isi dengan user id yang valid
        ]);
    }
}

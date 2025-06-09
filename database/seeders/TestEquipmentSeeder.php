<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;

class TestEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat equipment untuk testing
        Equipment::create([
            'name' => 'Test Equipment 1',
            'serial_number' => 'SN12345678',
            'barcode' => 'EQ00000001',
            'location' => 'Test Location',
            'installation_date' => now(),
            'status' => 'active',
            'qr_code' => 'TEST-2023-001',
            'specifications' => 'Test specifications'
        ]);
        
        $this->command->info('Test equipment berhasil dibuat!');
    }
} 
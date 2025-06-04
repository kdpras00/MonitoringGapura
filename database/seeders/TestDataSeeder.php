<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data test lama jika ada
        DB::table('equipments')->where('name', 'like', 'Test%')->delete();

        // Buat data test dengan QR code yang berbeda
        $testData = [
            [
                'name' => 'Test Equipment 1',
                'serial_number' => 'TEST-123-456',
                'location' => 'Test Location',
                'installation_date' => now(),
                'description' => 'Equipment for testing QR code',
                'status' => 'active',
                'specifications' => 'Test specifications',
                'qr_code' => 'TEST-123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test Equipment 2',
                'serial_number' => 'TEST-789-012',
                'location' => 'Test Location 2',
                'installation_date' => now(),
                'description' => 'Equipment with space in QR',
                'status' => 'active',
                'specifications' => 'Test specifications',
                'qr_code' => 'ABC DEF 2023',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test Equipment 3',
                'serial_number' => 'TEST-PLUS-789',
                'location' => 'Test Location 3',
                'installation_date' => now(),
                'description' => 'Equipment with plus in QR',
                'status' => 'active',
                'specifications' => 'Test specifications',
                'qr_code' => 'ABC+DEF 2023',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test Equipment 4',
                'serial_number' => 'TEST-SPECIAL-000',
                'location' => 'Test Location 4',
                'installation_date' => now(),
                'description' => 'Equipment with special chars in QR',
                'status' => 'active',
                'specifications' => 'Test specifications',
                'qr_code' => 'XYZ@123#456',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Test Equipment 5',
                'serial_number' => 'CVB-2023-SERIAL',
                'location' => 'Test Location 5',
                'installation_date' => now(),
                'description' => 'Equipment with requested QR code',
                'status' => 'active',
                'specifications' => 'Test specifications',
                'qr_code' => 'CVB-2023-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Insert data
        DB::table('equipments')->insert($testData);

        $this->command->info('Test equipment data created successfully.');
    }
}

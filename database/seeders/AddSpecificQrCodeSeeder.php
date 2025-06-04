<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddSpecificQrCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kode QR spesifik yang ingin ditambahkan
        $specificQrCodes = [
            [
                'name' => 'Baggage Conveyor BGC-2022',
                'serial_number' => 'BGC-2022-SERIAL',
                'location' => 'Terminal 1 Area 3',
                'installation_date' => '2022-01-15',
                'description' => 'Baggage conveyor belt system installed in 2022',
                'status' => 'active',
                'specifications' => 'Length: 15m, Width: 600mm, Motor: 2.2kW',
                'qr_code' => 'BGC-2022-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Baggage Conveyor BGC-2023',
                'serial_number' => 'BGC-2023-SERIAL',
                'location' => 'Terminal 2 Area 1',
                'installation_date' => '2023-05-20',
                'description' => 'Baggage conveyor belt system installed in 2023',
                'status' => 'active',
                'specifications' => 'Length: 18m, Width: 700mm, Motor: 3.0kW',
                'qr_code' => 'BGC-2023-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Baggage Conveyor BGC-2024',
                'serial_number' => 'BGC-2024-SERIAL',
                'location' => 'Terminal 3 Area 2',
                'installation_date' => '2024-02-10',
                'description' => 'Baggage conveyor belt system installed in 2024',
                'status' => 'active',
                'specifications' => 'Length: 20m, Width: 750mm, Motor: 3.5kW',
                'qr_code' => 'BGC-2024-001',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Cek apakah kode QR sudah ada
        foreach ($specificQrCodes as $equipmentData) {
            $exists = DB::table('equipments')
                ->where('qr_code', $equipmentData['qr_code'])
                ->exists();

            if (!$exists) {
                // Tambahkan ke database
                DB::table('equipments')->insert($equipmentData);
                $this->command->info("Added equipment with QR code: {$equipmentData['qr_code']}");
            } else {
                $this->command->info("QR code {$equipmentData['qr_code']} already exists");
            }
        }

        $this->command->info('Specific QR codes added successfully!');
    }
}

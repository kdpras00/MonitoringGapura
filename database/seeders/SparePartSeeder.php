<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SparePart;

class SparePartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spareParts = [
            [
                'name' => 'Filter Oli Mesin',
                'part_number' => 'FLT-OIL-001',
                'stock' => 10,
                'min_stock' => 3,
            ],
            [
                'name' => 'Filter Udara',
                'part_number' => 'FLT-AIR-002',
                'stock' => 8,
                'min_stock' => 2,
            ],
            [
                'name' => 'Seal Pompa Air',
                'part_number' => 'SEAL-PMP-003',
                'stock' => 15,
                'min_stock' => 5,
            ],
            [
                'name' => 'Bearing Generator',
                'part_number' => 'BRG-GEN-004',
                'stock' => 6,
                'min_stock' => 2,
            ],
            [
                'name' => 'V-Belt',
                'part_number' => 'BLT-005',
                'stock' => 12,
                'min_stock' => 4,
            ],
            [
                'name' => 'Kampas Rem Motor',
                'part_number' => 'BRK-PAD-006',
                'stock' => 20,
                'min_stock' => 6,
            ],
            [
                'name' => 'Seal Hidrolik',
                'part_number' => 'SEAL-HYD-007',
                'stock' => 8,
                'min_stock' => 3,
            ],
            [
                'name' => 'Gasket Kepala Silinder',
                'part_number' => 'GSK-CYL-008',
                'stock' => 4,
                'min_stock' => 2,
            ],
            [
                'name' => 'Thermostat',
                'part_number' => 'THRM-009',
                'stock' => 5,
                'min_stock' => 2,
            ],
            [
                'name' => 'Sensor Oksigen',
                'part_number' => 'SNS-OXY-010',
                'stock' => 3,
                'min_stock' => 1,
            ],
        ];
        
        foreach ($spareParts as $sparePart) {
            SparePart::firstOrCreate(
                ['part_number' => $sparePart['part_number']],
                $sparePart
            );
        }
        
        $this->command->info('Data spare parts berhasil ditambahkan!');
    }
} 
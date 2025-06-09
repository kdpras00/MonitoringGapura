<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;

class GenerateEquipmentBarcodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipments = Equipment::all();
        
        if ($equipments->count() > 0) {
            foreach ($equipments as $equipment) {
                // Generate barcode jika belum ada
                if (empty($equipment->barcode)) {
                    $equipment->barcode = 'EQ' . str_pad($equipment->id, 8, '0', STR_PAD_LEFT);
                    $equipment->save();
                }
            }
            
            $this->command->info('Barcode equipment berhasil dibuat!');
        } else {
            $this->command->info('Tidak ada data equipment. Tidak ada barcode yang dibuat.');
        }
    }
} 
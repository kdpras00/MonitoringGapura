<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SparePart;

class GenerateSparePartBarcodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spareParts = SparePart::all();
        
        if ($spareParts->count() > 0) {
            foreach ($spareParts as $sparePart) {
                // Generate barcode jika belum ada
                if (empty($sparePart->barcode)) {
                    $sparePart->barcode = 'SP' . str_pad($sparePart->id, 8, '0', STR_PAD_LEFT);
                    $sparePart->save();
                }
            }
            
            $this->command->info('Barcode spare parts berhasil dibuat!');
        } else {
            $this->command->info('Tidak ada data spare parts. Tidak ada barcode yang dibuat.');
        }
    }
} 
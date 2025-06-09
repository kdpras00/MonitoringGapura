<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SparePart;

class UpdateSparePartsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spareParts = SparePart::all();
        
        if ($spareParts->count() > 0) {
            foreach ($spareParts as $index => $part) {
                // Buat harga acak antara 50.000 - 2.000.000
                $price = rand(5, 200) * 10000;
                
                // Update spare part
                $part->price = $price;
                
                // Update status berdasarkan stok
                if ($part->stock <= 0) {
                    $part->status = 'out_of_stock';
                } elseif ($part->stock <= $part->min_stock) {
                    $part->status = 'low_stock';
                } else {
                    $part->status = 'available';
                }
                
                $part->save();
            }
            
            $this->command->info('Data spare parts berhasil diperbarui dengan harga dan status!');
        } else {
            $this->command->info('Tidak ada data spare parts. Tidak ada yang diperbarui.');
        }
    }
} 
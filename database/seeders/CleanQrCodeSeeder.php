<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CleanQrCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua peralatan
        $equipments = DB::table('equipments')->get();

        foreach ($equipments as $equipment) {
            // Jika qr_code berisi XML atau terlalu panjang, buat kode baru
            if (Str::contains($equipment->qr_code, '<?xml') || strlen($equipment->qr_code) > 50) {
                // Buat kode QR baru
                $year = date('Y');
                $serialPrefix = Str::substr($equipment->serial_number, 0, 3);
                $uniqueCode = strtoupper($serialPrefix) . '-' . $year . '-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);

                // Update kode QR di database
                DB::table('equipments')
                    ->where('id', $equipment->id)
                    ->update([
                        'qr_code' => $uniqueCode
                    ]);

                $this->command->info("Fixed equipment #{$equipment->id}: {$equipment->name} with new QR code: {$uniqueCode}");
            }
        }

        $this->command->info('QR codes cleaned successfully!');
    }
}

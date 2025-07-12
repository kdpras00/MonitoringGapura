<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Equipment;
use Carbon\Carbon;

class ElektrikNonElektrikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array untuk peralatan elektrik
        $elektrikEquipment = [
            [
                'name' => 'Panel Listrik Utama',
                'serial_number' => 'ELK-2023-001',
                'type' => 'elektrik',
                'location' => 'Terminal 1 - Ruang Mesin',
                'installation_date' => Carbon::now()->subMonths(6),
                'status' => 'active',
                'priority' => 'merah',
                'specifications' => 'Tegangan: 380V, Arus: 100A, 3 Phase',
                'checklist' => json_encode([
                    ['step' => 'Periksa isolasi kabel'],
                    ['step' => 'Periksa suhu MCB'],
                    ['step' => 'Periksa koneksi ground'],
                    ['step' => 'Periksa stabilitas tegangan']
                ])
            ],
            [
                'name' => 'Generator Backup',
                'serial_number' => 'ELK-2023-002',
                'type' => 'elektrik',
                'location' => 'Terminal 1 - Area Teknis',
                'installation_date' => Carbon::now()->subMonths(8),
                'status' => 'active',
                'priority' => 'merah',
                'specifications' => 'Daya: 500kVA, Bahan bakar: Solar, RPM: 1500',
                'checklist' => json_encode([
                    ['step' => 'Periksa level oli'],
                    ['step' => 'Periksa sistem starter'],
                    ['step' => 'Periksa output tegangan'],
                    ['step' => 'Periksa sistem pendingin']
                ])
            ],
            [
                'name' => 'UPS Sistem',
                'serial_number' => 'ELK-2023-003',
                'type' => 'elektrik',
                'location' => 'Terminal 2 - Server Room',
                'installation_date' => Carbon::now()->subMonths(3),
                'status' => 'active',
                'priority' => 'kuning',
                'specifications' => 'Kapasitas: 10kVA, Runtime: 30 menit, Baterai: 12V x 8',
                'checklist' => json_encode([
                    ['step' => 'Periksa tegangan baterai'],
                    ['step' => 'Periksa suhu inverter'],
                    ['step' => 'Periksa kapasitas beban'],
                    ['step' => 'Test switch-over time']
                ])
            ],
            [
                'name' => 'Sistem CCTV',
                'serial_number' => 'ELK-2023-004',
                'type' => 'elektrik',
                'location' => 'Terminal 1 - Area Publik',
                'installation_date' => Carbon::now()->subMonths(5),
                'status' => 'maintenance',
                'priority' => 'kuning',
                'specifications' => 'Kamera: 32 unit, Storage: 10TB, Resolution: 1080p',
                'checklist' => json_encode([
                    ['step' => 'Periksa kualitas gambar'],
                    ['step' => 'Periksa kapasitas harddisk'],
                    ['step' => 'Periksa koneksi jaringan'],
                    ['step' => 'Periksa fungsi PTZ']
                ])
            ],
            [
                'name' => 'Sistem Penerangan Landasan',
                'serial_number' => 'ELK-2023-005',
                'type' => 'elektrik',
                'location' => 'Area Landasan Pacu',
                'installation_date' => Carbon::now()->subMonths(12),
                'status' => 'active',
                'priority' => 'hijau',
                'specifications' => 'Jumlah lampu: 120, Daya: 200W per unit, Kontrol: PLC',
                'checklist' => json_encode([
                    ['step' => 'Periksa intensitas cahaya'],
                    ['step' => 'Periksa sistem kontrol'],
                    ['step' => 'Periksa kabel power'],
                    ['step' => 'Periksa grounding']
                ])
            ],
        ];

        // Array untuk peralatan non-elektrik
        $nonElektrikEquipment = [
            [
                'name' => 'Tangga Darurat',
                'serial_number' => 'NEL-2023-001',
                'type' => 'non-elektrik',
                'location' => 'Terminal 1 - Area Barat',
                'installation_date' => Carbon::now()->subMonths(24),
                'status' => 'active',
                'priority' => 'merah',
                'specifications' => 'Material: Baja Galvanis, Lebar: 1.2m, Tinggi: 4 lantai',
                'checklist' => json_encode([
                    ['step' => 'Periksa kondisi railing'],
                    ['step' => 'Periksa anti-slip pada pijakan'],
                    ['step' => 'Periksa struktur penopang'],
                    ['step' => 'Periksa pintu darurat']
                ])
            ],
            [
                'name' => 'Sistem Hidran',
                'serial_number' => 'NEL-2023-002',
                'type' => 'non-elektrik',
                'location' => 'Terminal 2 - Area Publik',
                'installation_date' => Carbon::now()->subMonths(18),
                'status' => 'active',
                'priority' => 'merah',
                'specifications' => 'Tekanan: 7 bar, Pipa: 4 inch, Pompa: Diesel backup',
                'checklist' => json_encode([
                    ['step' => 'Periksa tekanan air'],
                    ['step' => 'Periksa kebocoran pipa'],
                    ['step' => 'Periksa valve dan selang'],
                    ['step' => 'Periksa nozzle']
                ])
            ],
            [
                'name' => 'Troli Bagasi',
                'serial_number' => 'NEL-2023-003',
                'type' => 'non-elektrik',
                'location' => 'Terminal 1 - Area Kedatangan',
                'installation_date' => Carbon::now()->subMonths(4),
                'status' => 'active',
                'priority' => 'kuning',
                'specifications' => 'Material: Stainless Steel, Kapasitas: 100kg, Roda: 4',
                'checklist' => json_encode([
                    ['step' => 'Periksa roda'],
                    ['step' => 'Periksa handle'],
                    ['step' => 'Periksa struktur troli'],
                    ['step' => 'Periksa rem']
                ])
            ],
            [
                'name' => 'Pintu Otomatis',
                'serial_number' => 'NEL-2023-004',
                'type' => 'non-elektrik',
                'location' => 'Terminal 1 - Pintu Utama',
                'installation_date' => Carbon::now()->subMonths(10),
                'status' => 'maintenance',
                'priority' => 'kuning',
                'specifications' => 'Material: Kaca Tempered 12mm, Lebar: 2m, Sensor: Infrared',
                'checklist' => json_encode([
                    ['step' => 'Periksa mekanisme sliding'],
                    ['step' => 'Periksa kondisi seal'],
                    ['step' => 'Periksa kerangka pintu'],
                    ['step' => 'Periksa sistem pengaman']
                ])
            ],
            [
                'name' => 'Kursi Tunggu Terminal',
                'serial_number' => 'NEL-2023-005',
                'type' => 'non-elektrik',
                'location' => 'Terminal 2 - Area Keberangkatan',
                'installation_date' => Carbon::now()->subMonths(7),
                'status' => 'active',
                'priority' => 'hijau',
                'specifications' => 'Material: Stainless Steel & Kulit Sintetis, Unit: 50 set',
                'checklist' => json_encode([
                    ['step' => 'Periksa kondisi dudukan'],
                    ['step' => 'Periksa struktur kursi'],
                    ['step' => 'Periksa kaki kursi'],
                    ['step' => 'Periksa armrest']
                ])
            ],
        ];

        // Menggabungkan dan menyimpan data equipment
        $allEquipment = array_merge($elektrikEquipment, $nonElektrikEquipment);
        
        foreach ($allEquipment as $equipment) {
            Equipment::create($equipment);
        }
        
        $this->command->info('Dummy equipment elektrik dan non-elektrik berhasil dibuat!');
    }
} 
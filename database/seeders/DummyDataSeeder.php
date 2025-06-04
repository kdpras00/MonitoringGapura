<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\User;
use App\Models\SparePart;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat beberapa equipment dengan status berbeda
        $this->createEquipments();

        // Buat data maintenance historis dan yang akan datang
        $this->createMaintenances();

        // Buat spare parts
        $this->createSpareParts();
    }

    private function createEquipments(): void
    {
        $equipments = [
            [
                'name' => 'Conveyor Belt A1',
                'serial_number' => 'CVB-2023-001',
                'location' => 'Terminal 1 - Area Bagasi',
                'installation_date' => Carbon::now()->subMonths(12),
                'status' => 'active',
                'specifications' => 'Panjang: 15m, Kecepatan: 0.5m/s, Motor: 2.2kW',
                'checklist' => json_encode([
                    ['step' => 'Periksa tegangan belt'],
                    ['step' => 'Periksa motor penggerak'],
                    ['step' => 'Periksa roller'],
                    ['step' => 'Periksa sistem kontrol']
                ])
            ],
            [
                'name' => 'Conveyor Belt A2',
                'serial_number' => 'CVB-2023-002',
                'location' => 'Terminal 1 - Area Bagasi',
                'installation_date' => Carbon::now()->subMonths(10),
                'status' => 'active',
                'specifications' => 'Panjang: 12m, Kecepatan: 0.5m/s, Motor: 1.8kW',
                'checklist' => json_encode([
                    ['step' => 'Periksa tegangan belt'],
                    ['step' => 'Periksa motor penggerak'],
                    ['step' => 'Periksa roller'],
                    ['step' => 'Periksa sistem kontrol']
                ])
            ],
            [
                'name' => 'X-Ray Scanner B1',
                'serial_number' => 'XRS-2023-001',
                'location' => 'Terminal 1 - Security Check',
                'installation_date' => Carbon::now()->subMonths(8),
                'status' => 'maintenance',
                'specifications' => 'Resolusi: 1024x1024, Penetrasi: 35mm baja, Daya: 1.5kW',
                'checklist' => json_encode([
                    ['step' => 'Periksa sistem radiasi'],
                    ['step' => 'Kalibrasi sensor'],
                    ['step' => 'Periksa monitor display'],
                    ['step' => 'Periksa sistem conveyor']
                ])
            ],
            [
                'name' => 'Passenger Boarding Bridge C1',
                'serial_number' => 'PBB-2022-001',
                'location' => 'Terminal 2 - Gate 5',
                'installation_date' => Carbon::now()->subMonths(18),
                'status' => 'active',
                'specifications' => 'Panjang: 22m, Beban maksimum: 300 orang, Sistem: Hidrolik',
                'checklist' => json_encode([
                    ['step' => 'Periksa sistem hidrolik'],
                    ['step' => 'Periksa sistem kontrol'],
                    ['step' => 'Periksa struktur'],
                    ['step' => 'Periksa sistem keamanan']
                ])
            ],
            [
                'name' => 'Passenger Boarding Bridge C2',
                'serial_number' => 'PBB-2022-002',
                'location' => 'Terminal 2 - Gate 6',
                'installation_date' => Carbon::now()->subMonths(18),
                'status' => 'active',
                'specifications' => 'Panjang: 22m, Beban maksimum: 300 orang, Sistem: Hidrolik',
                'checklist' => json_encode([
                    ['step' => 'Periksa sistem hidrolik'],
                    ['step' => 'Periksa sistem kontrol'],
                    ['step' => 'Periksa struktur'],
                    ['step' => 'Periksa sistem keamanan']
                ])
            ],
            [
                'name' => 'Baggage Carousel D1',
                'serial_number' => 'BGC-2022-001',
                'location' => 'Terminal 1 - Arrival Hall',
                'installation_date' => Carbon::now()->subMonths(16),
                'status' => 'active',
                'specifications' => 'Diameter: 18m, Kecepatan: 0.3m/s, Motor: 3kW',
                'checklist' => json_encode([
                    ['step' => 'Periksa motor penggerak'],
                    ['step' => 'Periksa sistem belt'],
                    ['step' => 'Periksa struktur'],
                    ['step' => 'Periksa sistem kontrol']
                ])
            ],
            [
                'name' => 'Elevator E1',
                'serial_number' => 'ELV-2021-001',
                'location' => 'Terminal 1 - Main Hall',
                'installation_date' => Carbon::now()->subMonths(24),
                'status' => 'maintenance',
                'specifications' => 'Kapasitas: 1000kg, Kecepatan: 1.5m/s, Lantai: 4',
                'checklist' => json_encode([
                    ['step' => 'Periksa sistem traksi'],
                    ['step' => 'Periksa pintu otomatis'],
                    ['step' => 'Periksa sistem keamanan'],
                    ['step' => 'Periksa sistem kontrol']
                ])
            ],
            [
                'name' => 'Escalator E2',
                'serial_number' => 'ESC-2021-001',
                'location' => 'Terminal 1 - Main Hall',
                'installation_date' => Carbon::now()->subMonths(24),
                'status' => 'active',
                'specifications' => 'Kecepatan: 0.5m/s, Lebar: 1m, Motor: 5.5kW',
                'checklist' => json_encode([
                    ['step' => 'Periksa rantai penggerak'],
                    ['step' => 'Periksa step'],
                    ['step' => 'Periksa handrail'],
                    ['step' => 'Periksa sistem keamanan']
                ])
            ],
            [
                'name' => 'HVAC System F1',
                'serial_number' => 'HVAC-2021-001',
                'location' => 'Terminal 1 - Main Building',
                'installation_date' => Carbon::now()->subMonths(30),
                'status' => 'active',
                'specifications' => 'Kapasitas: 500 ton, Daya: 350kW, Tipe: Chiller',
                'checklist' => json_encode([
                    ['step' => 'Periksa kompresor'],
                    ['step' => 'Periksa kondensor'],
                    ['step' => 'Periksa evaporator'],
                    ['step' => 'Periksa sistem kontrol']
                ])
            ],
            [
                'name' => 'Generator G1',
                'serial_number' => 'GEN-2020-001',
                'location' => 'Terminal 1 - Power Room',
                'installation_date' => Carbon::now()->subMonths(36),
                'status' => 'retired',
                'specifications' => 'Kapasitas: 1000kVA, Bahan bakar: Diesel, RPM: 1500',
                'checklist' => json_encode([
                    ['step' => 'Periksa mesin diesel'],
                    ['step' => 'Periksa alternator'],
                    ['step' => 'Periksa sistem bahan bakar'],
                    ['step' => 'Periksa sistem pendingin']
                ])
            ],
        ];

        foreach ($equipments as $equipment) {
            Equipment::create($equipment);
        }
    }

    private function createMaintenances(): void
    {
        // Dapatkan semua equipment dan teknisi
        $equipments = Equipment::all();
        $technicians = User::where('role', 'technician')->get();

        if ($technicians->isEmpty()) {
            // Buat teknisi dummy jika belum ada
            $technicians = $this->createTechnicians();
        }

        // Maintenance yang sudah selesai (historis)
        $this->createHistoricalMaintenances($equipments, $technicians);

        // Maintenance yang sedang berlangsung
        $this->createOngoingMaintenances($equipments, $technicians);

        // Maintenance yang direncanakan
        $this->createPlannedMaintenances($equipments, $technicians);
    }

    private function createHistoricalMaintenances($equipments, $technicians): void
    {
        // Buat maintenance historis untuk 6 bulan terakhir
        for ($month = 6; $month >= 1; $month--) {
            // Preventive maintenance
            $preventiveCount = rand(3, 5); // 3-5 preventive maintenance per bulan
            for ($i = 0; $i < $preventiveCount; $i++) {
                $equipment = $equipments->random();
                $technician = $technicians->random();
                $date = Carbon::now()->subMonths($month)->subDays(rand(0, 28));

                Maintenance::create([
                    'equipment_id' => $equipment->id,
                    'technician_id' => $technician->id,
                    'schedule_date' => $date,
                    'actual_date' => $date->copy()->addHours(rand(1, 5)),
                    'maintenance_type' => 'preventive',
                    'status' => 'completed',
                    'cost' => rand(500000, 2000000),
                    'notes' => 'Maintenance preventif rutin. Semua sistem berfungsi normal.',
                    'next_service_date' => $date->copy()->addDays(30),
                    'attachments' => json_encode(['maintenance_report.pdf'])
                ]);
            }

            // Corrective maintenance
            $correctiveCount = rand(1, 3); // 1-3 corrective maintenance per bulan
            for ($i = 0; $i < $correctiveCount; $i++) {
                $equipment = $equipments->random();
                $technician = $technicians->random();
                $date = Carbon::now()->subMonths($month)->subDays(rand(0, 28));

                Maintenance::create([
                    'equipment_id' => $equipment->id,
                    'technician_id' => $technician->id,
                    'schedule_date' => $date,
                    'actual_date' => $date->copy()->addHours(rand(1, 5)),
                    'maintenance_type' => 'corrective',
                    'status' => 'completed',
                    'cost' => rand(1000000, 5000000),
                    'notes' => 'Perbaikan karena kerusakan pada ' . ['motor', 'belt', 'kontrol', 'sensor'][rand(0, 3)] . '. Sudah diperbaiki dan berfungsi normal.',
                    'next_service_date' => $date->copy()->addDays(30),
                    'attachments' => json_encode(['repair_report.pdf', 'invoice.pdf'])
                ]);
            }
        }
    }

    private function createOngoingMaintenances($equipments, $technicians): void
    {
        // Buat 2-4 maintenance yang sedang berlangsung
        $maintenanceEquipments = $equipments->where('status', 'maintenance');

        // Jika tidak ada equipment dengan status maintenance, gunakan equipment random
        if ($maintenanceEquipments->isEmpty()) {
            $maintenanceEquipments = $equipments->where('status', 'active')->take(2);
        }

        foreach ($maintenanceEquipments as $equipment) {
            $technician = $technicians->random();
            $date = Carbon::now()->subDays(rand(1, 3));

            Maintenance::create([
                'equipment_id' => $equipment->id,
                'technician_id' => $technician->id,
                'schedule_date' => $date,
                'actual_date' => $date,
                'maintenance_type' => rand(0, 1) ? 'preventive' : 'corrective',
                'status' => 'in-progress',
                'cost' => rand(1000000, 3000000),
                'notes' => 'Maintenance sedang berlangsung. ' . ['Penggantian part', 'Kalibrasi sistem', 'Pemeriksaan menyeluruh', 'Perbaikan kerusakan'][rand(0, 3)] . '.',
                'next_service_date' => $date->copy()->addDays(30),
                'attachments' => json_encode(['work_order.pdf'])
            ]);
        }
    }

    private function createPlannedMaintenances($equipments, $technicians): void
    {
        // Buat maintenance yang direncanakan untuk 30 hari ke depan
        for ($day = 1; $day <= 30; $day++) {
            // Tidak setiap hari ada maintenance
            if (rand(0, 100) < 70) { // 70% kemungkinan ada maintenance
                $count = rand(1, 2); // 1-2 maintenance per hari
                for ($i = 0; $i < $count; $i++) {
                    $activeEquipments = $equipments->where('status', 'active');

                    if ($activeEquipments->isEmpty()) {
                        continue;
                    }

                    $equipment = $activeEquipments->random();
                    $technician = $technicians->random();
                    $date = Carbon::now()->addDays($day);

                    Maintenance::create([
                        'equipment_id' => $equipment->id,
                        'technician_id' => $technician->id,
                        'schedule_date' => $date,
                        'actual_date' => null,
                        'maintenance_type' => rand(0, 100) < 80 ? 'preventive' : 'corrective', // 80% preventive
                        'status' => 'planned',
                        'cost' => rand(500000, 3000000),
                        'notes' => 'Maintenance terjadwal untuk ' . $equipment->name,
                        'next_service_date' => $date->copy()->addDays(30),
                        'attachments' => json_encode(['maintenance_schedule.pdf'])
                    ]);
                }
            }
        }

        // Buat beberapa maintenance yang terlambat (overdue)
        $count = rand(2, 4);
        $activeEquipments = $equipments->where('status', 'active');

        if ($activeEquipments->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $equipment = $activeEquipments->random();
            $technician = $technicians->random();
            $date = Carbon::now()->subDays(rand(1, 10));

            Maintenance::create([
                'equipment_id' => $equipment->id,
                'technician_id' => $technician->id,
                'schedule_date' => $date,
                'actual_date' => null,
                'maintenance_type' => rand(0, 1) ? 'preventive' : 'corrective',
                'status' => 'planned', // Masih planned tapi sudah lewat jadwal
                'cost' => rand(500000, 3000000),
                'notes' => 'Maintenance terjadwal untuk ' . $equipment->name . ' (terlambat)',
                'next_service_date' => $date->copy()->addDays(30),
                'attachments' => json_encode(['maintenance_schedule.pdf'])
            ]);
        }
    }

    private function createTechnicians(): array
    {
        $technicians = [];

        $names = [
            'Budi Santoso',
            'Ahmad Hidayat',
            'Rudi Hermawan',
            'Dedi Supriadi',
            'Eko Prasetyo'
        ];

        foreach ($names as $index => $name) {
            $technician = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'technician',
                'is_approved' => true
            ]);

            $technicians[] = $technician;
        }

        return $technicians;
    }

    private function createSpareParts(): void
    {
        $spareParts = [
            [
                'name' => 'Belt Conveyor 500mm',
                'part_number' => 'BC-500',
                'quantity' => rand(5, 15),
                'price' => 2500000,
                'status' => 'available',
                'description' => 'Belt conveyor lebar 500mm untuk conveyor bagasi'
            ],
            [
                'name' => 'Motor Penggerak 2.2kW',
                'part_number' => 'MP-22',
                'quantity' => rand(2, 8),
                'price' => 4500000,
                'status' => 'available',
                'description' => 'Motor penggerak 2.2kW untuk conveyor'
            ],
            [
                'name' => 'Roller Conveyor 50mm',
                'part_number' => 'RC-50',
                'quantity' => rand(10, 30),
                'price' => 350000,
                'status' => 'available',
                'description' => 'Roller conveyor diameter 50mm'
            ],
            [
                'name' => 'Sensor Proximity',
                'part_number' => 'SP-100',
                'quantity' => rand(5, 15),
                'price' => 750000,
                'status' => 'available',
                'description' => 'Sensor proximity untuk deteksi objek'
            ],
            [
                'name' => 'Kontrol Panel PLC',
                'part_number' => 'KP-PLC',
                'quantity' => rand(1, 5),
                'price' => 8500000,
                'status' => 'available',
                'description' => 'Panel kontrol dengan PLC Siemens'
            ],
            [
                'name' => 'Bearing 6205',
                'part_number' => 'B-6205',
                'quantity' => rand(20, 50),
                'price' => 150000,
                'status' => 'available',
                'description' => 'Bearing standar untuk roller'
            ],
            [
                'name' => 'Seal Hidrolik',
                'part_number' => 'SH-100',
                'quantity' => rand(10, 30),
                'price' => 250000,
                'status' => 'available',
                'description' => 'Seal untuk sistem hidrolik boarding bridge'
            ],
            [
                'name' => 'Oli Hidrolik 20L',
                'part_number' => 'OH-20',
                'quantity' => rand(5, 15),
                'price' => 1200000,
                'status' => 'available',
                'description' => 'Oli hidrolik 20L untuk sistem boarding bridge'
            ],
            [
                'name' => 'Kompresor AC 5HP',
                'part_number' => 'KAC-5',
                'quantity' => rand(1, 3),
                'price' => 12500000,
                'status' => 'low_stock',
                'description' => 'Kompresor AC untuk sistem HVAC'
            ],
            [
                'name' => 'Filter HVAC',
                'part_number' => 'FH-100',
                'quantity' => rand(10, 20),
                'price' => 350000,
                'status' => 'available',
                'description' => 'Filter untuk sistem HVAC'
            ],
            [
                'name' => 'Saklar Darurat',
                'part_number' => 'SD-100',
                'quantity' => rand(5, 15),
                'price' => 450000,
                'status' => 'available',
                'description' => 'Saklar darurat untuk eskalator'
            ],
            [
                'name' => 'Rantai Eskalator',
                'part_number' => 'RE-100',
                'quantity' => rand(1, 3),
                'price' => 15000000,
                'status' => 'low_stock',
                'description' => 'Rantai penggerak untuk eskalator'
            ],
            [
                'name' => 'Kabel Traksi Elevator',
                'part_number' => 'KTE-100',
                'quantity' => rand(1, 2),
                'price' => 22000000,
                'status' => 'low_stock',
                'description' => 'Kabel traksi untuk elevator'
            ],
            [
                'name' => 'Sensor X-Ray',
                'part_number' => 'SXR-100',
                'quantity' => rand(1, 3),
                'price' => 35000000,
                'status' => 'out_of_stock',
                'description' => 'Sensor untuk mesin X-Ray'
            ],
            [
                'name' => 'Modul Kontrol Generator',
                'part_number' => 'MKG-100',
                'quantity' => rand(1, 3),
                'price' => 18000000,
                'status' => 'low_stock',
                'description' => 'Modul kontrol untuk generator'
            ],
        ];

        foreach ($spareParts as $sparePart) {
            SparePart::create($sparePart);
        }
    }
}

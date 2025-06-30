<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Maintenance;
use App\Models\Inspection;
use App\Models\User;

class AddDummyVerificationDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari equipment yang sudah ada atau buat baru
        $equipment = Equipment::first();
        if (!$equipment) {
            $equipment = Equipment::create([
                'name' => 'Equipment Test Verifikasi',
                'description' => 'Equipment untuk testing verifikasi',
                'type' => 'Elektronik',
                'status' => 'active',
                'location' => 'Terminal 1',
                'priority' => 'high',
                'installation_date' => now()->subYear(),
                'last_maintenance_date' => now()->subMonth(),
            ]);
        }

        // Cari teknisi atau buat user baru
        $technician = User::where('role', 'technician')->first();
        if (!$technician) {
            $technician = User::create([
                'name' => 'Teknisi Test',
                'email' => 'teknisi@test.com',
                'password' => bcrypt('password'),
                'role' => 'technician',
            ]);
        }

        // Cari supervisor atau buat user baru
        $supervisor = User::where('role', 'supervisor')->first();
        if (!$supervisor) {
            $supervisor = User::create([
                'name' => 'Supervisor Test',
                'email' => 'supervisor@test.com',
                'password' => bcrypt('password'),
                'role' => 'supervisor',
            ]);
        }

        // Buat maintenance
        $maintenance = Maintenance::create([
            'equipment_id' => $equipment->id,
            'schedule_date' => now(),
            'next_service_date' => now()->addMonth(),
            'technician_id' => $technician->id,
            'status' => 'in-progress',
            'priority' => 'high',
            'description' => 'Maintenance test untuk verifikasi',
            'notes' => 'Catatan untuk maintenance test',
            'cost' => 0,
            'maintenance_type' => 'preventive',
            'equipment_type' => 'elektronik'
        ]);

        // Buat inspeksi yang sudah selesai (completed) tapi belum diverifikasi
        $inspection = Inspection::create([
            'equipment_id' => $equipment->id,
            'technician_id' => $technician->id,
            'inspection_date' => now(),
            'schedule_date' => now()->subDay(),
            'status' => 'completed',
            'notes' => 'Inspeksi selesai, menunggu verifikasi',
            'before_image' => 'test_images/before.jpg',
            'after_image' => 'test_images/after.jpg',
            'completion_date' => now(),
        ]);

        // Buat inspeksi dengan status in-progress (hanya foto before)
        $inspectionInProgress = Inspection::create([
            'equipment_id' => $equipment->id,
            'technician_id' => $technician->id,
            'inspection_date' => now()->subHour(),
            'schedule_date' => now()->subDay(),
            'status' => 'in-progress',
            'notes' => 'Inspeksi sedang dikerjakan, baru upload foto sebelum',
            'before_image' => 'test_images/before.jpg',
            'completion_date' => null,
        ]);

        // Buat inspeksi dengan status pending (belum dikerjakan)
        $inspectionPending = Inspection::create([
            'equipment_id' => $equipment->id,
            'technician_id' => $technician->id,
            'inspection_date' => now()->addDay(),
            'schedule_date' => now()->addDay(),
            'status' => 'pending',
            'notes' => 'Inspeksi belum dimulai',
            'completion_date' => null,
        ]);

        // Output informasi
        $this->command->info('Data dummy untuk verifikasi berhasil dibuat:');
        $this->command->info("- Equipment: {$equipment->name}");
        $this->command->info("- Teknisi: {$technician->name}");
        $this->command->info("- Maintenance: ID {$maintenance->id}");
        $this->command->info("- Inspeksi (completed): ID {$inspection->id}");
        $this->command->info("- Inspeksi (in-progress): ID {$inspectionInProgress->id}");
        $this->command->info("- Inspeksi (pending): ID {$inspectionPending->id}");
    }
} 
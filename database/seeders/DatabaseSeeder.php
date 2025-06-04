<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat role
        $adminRole = Role::create(['name' => 'admin']);
        $technicianRole = Role::create(['name' => 'technician']);
        $viewerRole = Role::create(['name' => 'viewer']);

        // Buat user admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_approved' => true
        ]);
        $admin->assignRole($adminRole);

        // Buat user teknisi
        $technician = User::create([
            'name' => 'Technician User',
            'email' => 'technician@example.com',
            'password' => bcrypt('password'),
            'role' => 'technician',
            'is_approved' => true
        ]);
        $technician->assignRole($technicianRole);

        // Buat user viewer
        $viewer = User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@example.com',
            'password' => bcrypt('password'),
            'role' => 'viewer',
            'is_approved' => true
        ]);
        $viewer->assignRole($viewerRole);

        // Jalankan dummy data seeder
        $this->call([
            DummyDataSeeder::class,
        ]);
    }
}

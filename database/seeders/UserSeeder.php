<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_approved' => true
            ]
        );
        $admin->assignRole('admin');

        // Create Technician user
        $technician = User::firstOrCreate(
            ['email' => 'technician@example.com'],
            [
                'name' => 'Technician User',
                'password' => Hash::make('password'),
                'role' => 'technician',
                'is_approved' => true
            ]
        );
        $technician->assignRole('technician');

        // Create Viewer user
        $viewer = User::firstOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Viewer User',
                'password' => Hash::make('password'),
                'role' => 'viewer',
                'is_approved' => true
            ]
        );
        $viewer->assignRole('viewer');

        $this->command->info('Users seeded successfully!');
    }
} 
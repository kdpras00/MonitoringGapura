<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SupervisorUserSeeder extends Seeder
{
    /**
     * Run database seeds.
     */
    public function run(): void
    {
        // Buat permission untuk approval maintenance jika belum ada
        Permission::firstOrCreate(['name' => 'approve maintenance']);
        
        // Buat role supervisor jika belum ada
        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor']);
        
        // Berikan permission ke role supervisor
        $supervisorRole->givePermissionTo('approve maintenance');
        
        // Periksa apakah supervisor sudah ada
        $exists = User::where('email', 'supervisor@gapura.com')->exists();
        
        if (!$exists) {
            // Buat user supervisor
            $supervisor = User::create([
                'name' => 'Supervisor Gapura',
                'email' => 'supervisor@gapura.com',
                'username' => 'supervisor',
                'password' => Hash::make('supervisor123'), // Ganti dengan password yang lebih kuat
                'role' => 'supervisor',
                'is_approved' => true,
            ]);
            
            // Assign role
            $supervisor->assignRole('supervisor');
            
            $this->command->info('User supervisor berhasil dibuat!');
        } else {
            $this->command->info('User supervisor sudah ada');
        }
        
        // Pastikan admin juga memiliki akses untuk approve maintenance
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo('approve maintenance');
        
        $this->command->info('Role admin berhasil diberi permission approve maintenance!');
    }
} 
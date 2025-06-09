<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Filament\Forms\Components\CheckboxList;


class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permissions tanpa duplikasi
        $permissions = [
            'manage equipment',
            'manage maintenance',
            'view reports',
            'approve maintenance',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Buat roles tanpa duplikasi
        $roles = [
            'admin' => ['manage equipment', 'manage maintenance', 'view reports', 'approve maintenance'],
            'technician' => ['manage maintenance'],
            'viewer' => ['view reports'],
            'supervisor' => ['approve maintenance', 'view reports'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions); // Gunakan syncPermissions agar tidak duplikat
        }
    }
}

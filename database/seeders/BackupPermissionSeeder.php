<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BackupPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'create-backup',
            'download-backup',
            'delete-backup',
        ];

        // Buat permission jika belum ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Berikan permission ke role super_admin
        $role = Role::findByName('super_admin', 'web');

        $role->givePermissionTo($permissions);

        // Bersihkan cache permission
        app(\Spatie\Permission\PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}

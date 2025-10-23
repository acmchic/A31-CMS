<?php

namespace Modules\FileSharing\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FileSharingPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo permissions cho File Sharing
        $permissions = [
            // Module permissions
            'file_sharing.view',
            'file_sharing.create',
            'file_sharing.update',
            'file_sharing.delete',
            
            // CRUD permissions
            'shared_file.view',
            'shared_file.create',
            'shared_file.update',
            'shared_file.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Gán permissions cho các role
        $this->assignPermissionsToRoles();
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(): void
    {
        // Admin role - Full access
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo([
            'file_sharing.view',
            'file_sharing.create',
            'file_sharing.update',
            'file_sharing.delete',
            'shared_file.view',
            'shared_file.create',
            'shared_file.update',
            'shared_file.delete',
        ]);

        // Manager role - Can view and create, limited update/delete
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $managerRole->givePermissionTo([
            'file_sharing.view',
            'file_sharing.create',
            'shared_file.view',
            'shared_file.create',
        ]);

        // User role - Can view and create own files
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userRole->givePermissionTo([
            'file_sharing.view',
            'file_sharing.create',
        ]);

        // Guest role - Can only view public files
        $guestRole = Role::firstOrCreate(['name' => 'Guest']);
        $guestRole->givePermissionTo([
            'file_sharing.view',
        ]);
    }
}

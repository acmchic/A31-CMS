<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AddApprovalCenterPermission extends Seeder
{
    /**
     * Add approval_center.view permission to the system
     * This can be run without resetting existing permissions
     */
    public function run()
    {
        $this->command->info('Adding approval_center.view permission...');

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permission if it doesn't exist
        $permission = Permission::firstOrCreate(
            ['name' => 'approval_center.view'],
            ['guard_name' => 'web']
        );

        $this->command->info("✓ Permission 'approval_center.view' created/found");

        // Optionally assign to specific roles if needed
        // Uncomment and modify as needed:
        /*
        $rolesToAssign = ['Ban Giam Doc', 'Truong Phong', 'Thẩm định'];
        foreach ($rolesToAssign as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role && !$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
                $this->command->info("✓ Assigned to role: {$roleName}");
            }
        }
        */

        $this->command->info('Done!');
    }
}


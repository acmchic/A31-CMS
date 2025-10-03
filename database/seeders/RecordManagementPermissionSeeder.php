<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RecordManagementPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'record_management.view',
            'record_management.create',
            'record_management.edit',
            'record_management.delete',
            'salary_up_record.view',
            'salary_up_record.create',
            'salary_up_record.edit',
            'salary_up_record.delete',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'backpack']
            );
        }

        // Assign permissions to admin role (both web and backpack guards)
        $adminRoleWeb = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if ($adminRoleWeb) {
            // Create web guard permissions
            foreach ($permissions as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
            $adminRoleWeb->givePermissionTo($permissions);
        }
        
        $adminRoleBackpack = Role::where('name', 'admin')->where('guard_name', 'backpack')->first();
        if ($adminRoleBackpack) {
            $adminRoleBackpack->givePermissionTo($permissions);
        }

        $this->command->info('Record Management permissions created successfully!');
    }
}


<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ProductionManagementPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'material_plan.view' => 'Xem danh sách phương án vật tư',
            'material_plan.create' => 'Tạo phương án vật tư',
            'material_plan.edit' => 'Sửa phương án vật tư',
            'material_plan.delete' => 'Xóa phương án vật tư',
            'material_plan.approve' => 'Phê duyệt phương án vật tư',
            'material.view' => 'Xem danh sách vật tư',
            'material.create' => 'Tạo vật tư',
            'material.edit' => 'Sửa vật tư',
            'material.delete' => 'Xóa vật tư',
        ];

        $this->command->info('Creating Production Management permissions...');

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => 'web'
                ],
                [
                    'name' => $name,
                    'guard_name' => 'web'
                ]
            );
            $this->command->line("  ✓ {$name}");
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo(array_keys($permissions));
            $this->command->info('✓ Permissions assigned to Admin role');
        }

        $this->command->info('Production Management permissions created successfully!');
    }
}




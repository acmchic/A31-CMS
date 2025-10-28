<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AnDuongRecordPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo permissions cho sổ đăng ký an dưỡng, bồi dưỡng
        $permissions = [
            'an_duong_record.view',
            'an_duong_record.create',
            'an_duong_record.update',
            'an_duong_record.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Gán permissions cho các role
        $this->assignPermissionsToRoles($permissions);
    }

    private function assignPermissionsToRoles(array $permissions): void
    {
        // Super Admin - có tất cả quyền
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo($permissions);

        // Admin - có tất cả quyền
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo($permissions);

        // Manager - có quyền xem và tạo
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $managerRole->givePermissionTo([
            'an_duong_record.view',
            'an_duong_record.create',
            'an_duong_record.update',
        ]);

        // User - chỉ có quyền xem
        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userRole->givePermissionTo([
            'an_duong_record.view',
        ]);

        // Department Head - có quyền xem và tạo trong phòng ban
        $deptHeadRole = Role::firstOrCreate(['name' => 'Department Head']);
        $deptHeadRole->givePermissionTo([
            'an_duong_record.view',
            'an_duong_record.create',
            'an_duong_record.update',
        ]);
    }
}

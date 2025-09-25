<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Tạo permissions
        Permission::create(['name' => 'view-users']);
        Permission::create(['name' => 'create-users']);
        Permission::create(['name' => 'edit-users']);
        Permission::create(['name' => 'delete-users']);

        Permission::create(['name' => 'view-roles']);
        Permission::create(['name' => 'create-roles']);
        Permission::create(['name' => 'edit-roles']);
        Permission::create(['name' => 'delete-roles']);

        Permission::create(['name' => 'view-permissions']);
        Permission::create(['name' => 'create-permissions']);
        Permission::create(['name' => 'edit-permissions']);
        Permission::create(['name' => 'delete-permissions']);

        // Tạo roles
        $admin = Role::create(['name' => 'admin']);
        $banGiamDoc = Role::create(['name' => 'ban_giam_doc']);
        $truongPhong = Role::create(['name' => 'truong_phong']);
        $nhanVien = Role::create(['name' => 'nhan_vien']);

        // Gán tất cả quyền cho admin
        $admin->givePermissionTo(Permission::all());

        // Gán quyền cho ban giám đốc
        $banGiamDoc->givePermissionTo([
            'view-users', 'create-users', 'edit-users',
            'view-roles'
        ]);

        // Gán quyền cho trưởng phòng
        $truongPhong->givePermissionTo([
            'view-users'
        ]);

        // Gán role admin cho user hiện có
        $user = User::where('username', 'admin')->first();
        if ($user) {
            $user->assignRole('admin');
        }

        echo "Created roles and permissions successfully\n";
    }
}

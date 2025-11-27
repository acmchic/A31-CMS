<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'leave.view.all' => 'Xem dữ liệu nghỉ phép toàn bộ',
            'vehicle_registration.view.all' => 'Xem dữ liệu đăng ký xe toàn bộ',
            'material_plan.view.all' => 'Xem dữ liệu quản lý sản xuất toàn bộ',
        ];

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
        }
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'leave.view.all',
            'vehicle_registration.view.all',
            'material_plan.view.all',
        ];

        foreach ($permissions as $name) {
            $permission = Permission::where('name', $name)
                ->where('guard_name', 'web')
                ->first();
            
            if ($permission) {
                $permission->delete();
            }
        }
    }
};

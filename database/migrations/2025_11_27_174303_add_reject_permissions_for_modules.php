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
            'leave.reject' => 'Từ chối đơn nghỉ phép',
            'vehicle_registration.reject' => 'Từ chối đăng ký xe',
            'material_plan.reject' => 'Từ chối phương án vật tư',
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
            'leave.reject',
            'vehicle_registration.reject',
            'material_plan.reject',
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

<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create leave.review.officer permission if it doesn't exist
        Permission::firstOrCreate(
            [
                'name' => 'leave.review.officer',
                'guard_name' => 'web'
            ],
            [
                'name' => 'leave.review.officer',
                'guard_name' => 'web'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Remove permission if exists
        $permission = Permission::where('name', 'leave.review.officer')
            ->where('guard_name', 'web')
            ->first();
        
        if ($permission) {
            $permission->delete();
        }
    }
};

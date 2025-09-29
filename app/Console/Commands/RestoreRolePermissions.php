<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RestoreRolePermissions extends Command
{
    protected $signature = 'role:restore {id}';
    protected $description = 'Restore all permissions to a role';

    public function handle()
    {
        $roleId = $this->argument('id');
        $role = Role::find($roleId);
        
        if (!$role) {
            $this->error("Role {$roleId} not found");
            return;
        }
        
        // Restore original permissions for Nhân viên role
        $originalPermissions = [1,2,3,4,5,6,7,8,9,10,30,11,13,14,17,29,15,22,23,24,27,18,19,20,21];
        
        $this->info("Restoring permissions for role: {$role->name}");
        $this->info("Current permissions count: {$role->permissions->count()}");
        $this->info("Restoring with " . count($originalPermissions) . " permissions");
        
        $result = $role->permissions()->sync($originalPermissions);
        
        // Clear cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Check result
        $role = $role->fresh();
        $this->info("After restore - permissions count: {$role->permissions->count()}");
        
        $this->info("Permissions restored successfully!");
    }
}

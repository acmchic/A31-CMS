<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TestRoleSync extends Command
{
    protected $signature = 'role:test-sync {id}';
    protected $description = 'Test role permission sync';

    public function handle()
    {
        $roleId = $this->argument('id');
        $role = Role::find($roleId);
        
        if (!$role) {
            $this->error("Role {$roleId} not found");
            return;
        }
        
        $this->info("Testing role: {$role->name}");
        $this->info("Current permissions count: {$role->permissions->count()}");
        
        // Test syncing with fewer permissions - just 3 permissions
        $testPermissions = [1, 2, 3]; // view dashboard, view own data, edit own data
        
        $this->info("Syncing with permissions: " . implode(', ', $testPermissions));
        
        $result = $role->permissions()->sync($testPermissions);
        
        $this->info("Sync result:");
        $this->line(json_encode($result, JSON_PRETTY_PRINT));
        
        // Clear cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        // Check result
        $role = $role->fresh();
        $this->info("After sync - permissions count: {$role->permissions->count()}");
        $this->info("Permissions:");
        foreach ($role->permissions as $permission) {
            $this->line("- {$permission->name} (ID: {$permission->id})");
        }
    }
}

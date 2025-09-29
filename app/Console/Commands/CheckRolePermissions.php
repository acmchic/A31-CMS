<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckRolePermissions extends Command
{
    protected $signature = 'check:role {id}';
    protected $description = 'Check role permissions';

    public function handle()
    {
        $roleId = $this->argument('id');
        $role = Role::find($roleId);
        
        if (!$role) {
            $this->error("Role {$roleId} not found");
            return;
        }
        
        $this->info("Role: {$role->name}");
        $this->info("Permissions count: {$role->permissions->count()}");
        
        if ($role->permissions->count() > 0) {
            $this->line("Permissions:");
            foreach ($role->permissions as $permission) {
                $this->line("- {$permission->name} (ID: {$permission->id})");
            }
        } else {
            $this->warn("No permissions assigned!");
        }
    }
}

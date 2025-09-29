<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckRole extends Command
{
    protected $signature = 'role:check {id}';
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
        
        foreach ($role->permissions as $permission) {
            $this->line("- {$permission->name} (ID: {$permission->id})");
        }
    }
}

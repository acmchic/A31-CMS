<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUser extends Command
{
    protected $signature = 'check:user {username}';
    protected $description = 'Check user details';

    public function handle()
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();
        
        if (!$user) {
            $this->error("User '{$username}' not found");
            return;
        }
        
        $this->info("User: {$user->name} ({$user->username})");
        $this->info("Department ID: {$user->department_id}");
        
        $this->line('');
        $this->info('Roles:');
        if ($user->roles->count() > 0) {
            foreach ($user->roles as $role) {
                $this->line("- {$role->name}");
            }
        } else {
            $this->warn('No roles assigned!');
        }
        
        $this->line('');
        $this->info('Permissions:');
        $permissions = $user->getAllPermissions();
        if ($permissions->count() > 0) {
            foreach ($permissions as $permission) {
                $this->line("- {$permission->name} (ID: {$permission->id})");
            }
        } else {
            $this->warn('No permissions!');
        }
        
        // Test key permissions
        $this->line('');
        $this->info('Permission Tests:');
        $this->line('- dashboard.view: ' . ($user->hasPermissionTo('dashboard.view') ? 'YES' : 'NO'));
        $this->line('- user.view: ' . ($user->hasPermissionTo('user.view') ? 'YES' : 'NO'));
        $this->line('- role.view: ' . ($user->hasPermissionTo('role.view') ? 'YES' : 'NO'));
    }
}

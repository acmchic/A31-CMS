<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CheckUserPermission extends Command
{
    protected $signature = 'user:check-permission {email} {permission}';
    protected $description = 'Kiểm tra và gán permission cho user';

    public function handle()
    {
        $email = $this->argument('email');
        $permissionName = $this->argument('permission');

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Không tìm thấy user với email: {$email}");
            return 1;
        }

        $this->info("User: {$user->name} ({$user->email})");
        $this->info("User ID: {$user->id}");
        $this->line('');

        // Hiển thị roles
        $this->info("Roles:");
        foreach ($user->roles as $role) {
            $this->line("  - {$role->name}");
        }
        $this->line('');

        // Kiểm tra permission
        $hasPermission = $user->hasPermissionTo($permissionName);
        $this->info("Has permission '{$permissionName}': " . ($hasPermission ? 'YES ✅' : 'NO ❌'));
        $this->line('');

        // Hiển thị tất cả permissions của user
        $this->info("All permissions:");
        $permissions = $user->getAllPermissions();
        if ($permissions->isEmpty()) {
            $this->warn("  User không có permission nào");
        } else {
            foreach ($permissions as $perm) {
                $marker = $perm->name === $permissionName ? ' ⭐' : '';
                $this->line("  - {$perm->name}{$marker}");
            }
        }
        $this->line('');

        // Kiểm tra permission có tồn tại không
        $permission = Permission::where('name', $permissionName)->first();
        if (!$permission) {
            $this->warn("⚠️  Permission '{$permissionName}' chưa tồn tại trong database!");
            $this->info("Chạy seeder để tạo permission:");
            $this->line("  php artisan db:seed --class=Database\\Seeders\\VehicleRegistrationPermissionSeeder");
            return 1;
        }

        // Nếu chưa có permission, hỏi có muốn gán không
        if (!$hasPermission) {
            if ($this->confirm("Bạn có muốn gán permission '{$permissionName}' cho user này không?")) {
                $user->givePermissionTo($permissionName);
                $this->info("✅ Đã gán permission '{$permissionName}' cho user!");
                
                // Clear cache
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                $this->info("✅ Đã clear permission cache");
            }
        }

        return 0;
    }
}


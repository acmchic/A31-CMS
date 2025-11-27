<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GrantVehicleAssignPermission extends Command
{
    protected $signature = 'vehicle:grant-assign-permission {email}';
    protected $description = 'Gán quyền vehicle_registration.assign cho user';

    public function handle()
    {
        $email = $this->argument('email');
        $permissionName = 'vehicle_registration.assign';

        // Đảm bảo permission tồn tại
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['guard_name' => 'web']
        );

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Không tìm thấy user với email: {$email}");
            return 1;
        }

        // Gán permission
        if (!$user->hasPermissionTo($permissionName)) {
            $user->givePermissionTo($permissionName);
            $this->info("✅ Đã gán permission '{$permissionName}' cho user: {$user->name} ({$user->email})");
        } else {
            $this->info("ℹ️  User đã có permission '{$permissionName}' rồi");
        }

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info("✅ Đã clear permission cache");

        // Hiển thị thông tin
        $this->line('');
        $this->info("Thông tin user:");
        $this->line("  Name: {$user->name}");
        $this->line("  Email: {$user->email}");
        $this->line("  Roles: " . $user->roles->pluck('name')->join(', '));
        $this->line("  Has permission: " . ($user->hasPermissionTo($permissionName) ? 'YES ✅' : 'NO ❌'));

        return 0;
    }
}


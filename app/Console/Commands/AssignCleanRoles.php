<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class AssignCleanRoles extends Command
{
    protected $signature = 'assign:clean-roles';
    protected $description = 'Assign clean roles to all users';

    public function handle()
    {
        $this->info('👥 Assigning clean roles to users...');

        // Remove all existing role assignments
        User::all()->each(function($user) {
            $user->syncRoles([]);
        });

        // Assign Admin role
        $adminUsers = User::whereIn('username', ['admin'])->get();
        foreach ($adminUsers as $user) {
            $user->assignRole('Admin');
            $this->line("- {$user->name}: Admin (Quản trị viên)");
        }

        // Assign Ban Giam Doc (users in department 1)
        $banGiamDocUsers = User::where('department_id', 1)->whereNotIn('username', ['admin'])->get();
        foreach ($banGiamDocUsers as $user) {
            $user->assignRole('Ban Giam Doc');
            $this->line("- {$user->name}: Ban Giam Doc (Ban Giám Đốc)");
        }

        // Assign Truong Phong (department heads)
        $truongPhongUsers = User::where('is_department_head', true)
            ->where('department_id', '!=', 1)
            ->whereNotIn('username', ['admin'])
            ->get();
        foreach ($truongPhongUsers as $user) {
            $user->assignRole('Truong Phong');
            $this->line("- {$user->name}: Truong Phong (Trưởng Phòng)");
        }

        // Assign Nhan Vien (everyone else)
        $nhanVienUsers = User::whereDoesntHave('roles')->get();
        foreach ($nhanVienUsers as $user) {
            $user->assignRole('Nhan Vien');
            $this->line("- {$user->name}: Nhan Vien (Nhân sự)");
        }

        // Clear cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('✅ Role assignment completed!');
        $this->line('');
        $this->info('📊 Summary:');
        $this->line('- Admin: ' . User::role('Admin')->count() . ' users');
        $this->line('- Ban Giam Doc: ' . User::role('Ban Giam Doc')->count() . ' users');
        $this->line('- Truong Phong: ' . User::role('Truong Phong')->count() . ' users');
        $this->line('- Nhan Vien: ' . User::role('Nhan Vien')->count() . ' users');
    }
}

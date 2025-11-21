<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@a31cms.com'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('123456'),
            ]
        );
        
        // Assign Admin role to admin user if not already assigned
        $adminRole = Role::where('name', 'Admin')->first();
        if ($adminRole && !$admin->hasRole('Admin')) {
            $admin->assignRole($adminRole);
        }
        
        // Create or update test user
        $user = User::updateOrCreate(
            ['email' => 'user@a31cms.com'],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => Hash::make('user123'),
            ]
        );
        
        // Assign Nhan Vien role to test user if not already assigned
        $userRole = Role::where('name', 'Nhan Vien')->first();
        if ($userRole && !$user->hasRole('Nhan Vien')) {
            $user->assignRole($userRole);
        }
    }
}

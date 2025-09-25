<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@a31cms.com',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
        ]);
        
        // Create test user
        User::create([
            'name' => 'Test User',
            'email' => 'user@a31cms.com', 
            'username' => 'testuser',
            'password' => Hash::make('user123'),
        ]);
    }
}

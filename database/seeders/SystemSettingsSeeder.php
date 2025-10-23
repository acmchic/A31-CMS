<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set default font family
        SystemSetting::setFontFamily('Segoe UI');
        
        // Set default background color
        SystemSetting::setBackgroundColor('#f8f9fa');
    }
}

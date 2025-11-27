<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class ClearPermissionCache extends Command
{
    protected $signature = 'permission:clear-cache';
    protected $description = 'Clear permission cache (Spatie Permission)';

    public function handle()
    {
        try {
            app()[PermissionRegistrar::class]->forgetCachedPermissions();
            $this->info('✅ Đã clear permission cache thành công!');
            
            // Clear tất cả cache
            \Artisan::call('cache:clear');
            $this->info('✅ Đã clear tất cả cache!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Lỗi khi clear cache: ' . $e->getMessage());
            return 1;
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\ApprovalWorkflow\Models\ApprovalFlow;

class CheckApprovalFlow extends Command
{
    protected $signature = 'approval:check-flow';
    protected $description = 'Kiểm tra và seed ApprovalFlow nếu chưa có';

    public function handle()
    {
        $this->info('🔍 Kiểm tra ApprovalFlow...');

        $flows = ApprovalFlow::all();
        
        if ($flows->isEmpty()) {
            $this->warn('⚠️  Chưa có ApprovalFlow nào!');
            $this->info('Chạy seeder để tạo:');
            $this->line('  php artisan db:seed --class="Modules\\ApprovalWorkflow\\Database\\Seeders\\ApprovalFlowSeeder"');
            return 1;
        }

        $this->info('✅ Đã có ApprovalFlow:');
        foreach ($flows as $flow) {
            $stepsCount = $flow->steps()->count();
            $this->line("  - {$flow->module_type}: {$flow->name} ({$stepsCount} steps)");
        }

        // Kiểm tra các module cần thiết
        $requiredModules = ['leave', 'vehicle'];
        $missingModules = [];

        foreach ($requiredModules as $module) {
            $flow = ApprovalFlow::getByModuleType($module);
            if (!$flow) {
                $missingModules[] = $module;
            }
        }

        if (!empty($missingModules)) {
            $this->warn('⚠️  Thiếu ApprovalFlow cho các module: ' . implode(', ', $missingModules));
            $this->info('Chạy seeder để tạo:');
            $this->line('  php artisan db:seed --class="Modules\\ApprovalWorkflow\\Database\\Seeders\\ApprovalFlowSeeder"');
            return 1;
        }

        $this->info('✅ Tất cả ApprovalFlow đã sẵn sàng!');
        return 0;
    }
}


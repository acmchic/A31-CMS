<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\VehicleRegistration\Models\VehicleRegistration;
use Modules\ApprovalWorkflow\Observers\ApprovalRequestObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đăng ký Observer để tự động tạo ApprovalRequest khi model được tạo
        EmployeeLeave::observe(ApprovalRequestObserver::class);
        VehicleRegistration::observe(ApprovalRequestObserver::class);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\OrganizationStructure\Models\Department;
use Modules\OrganizationStructure\Models\Employee;
use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\PersonnelReport\Models\DailyPersonnelReport;
use App\Helpers\PermissionHelper;

class DashboardController extends Controller
{
    /**
     * Show the custom dashboard
     */
    public function dashboard()
    {
        // Check dashboard permission
        if (!PermissionHelper::userCan('dashboard.view')) {
            abort(403, 'Bạn không có quyền truy cập dashboard.');
        }
        
        // Get statistics
        $stats = [
            'departments' => Department::count(),
            'employees' => Employee::count(),
            'leave_requests' => EmployeeLeave::count(),
            'reports' => DailyPersonnelReport::count(),
            'users' => User::count(),
        ];

        // Base modules that all users can see
        $modules = [];
        
        // System Management Module
        if (PermissionHelper::userCan('user.view') || PermissionHelper::userCan('role.view')) {
            $modules[] = [
                'name' => 'Quản lý hệ thống',
                'description' => 'Quản lý người dùng, vai trò và quyền hạn',
                'icon' => 'la la-cogs',
                'url' => PermissionHelper::userCan('user.view') ? backpack_url('user') : backpack_url('role'),
                'status' => 'active',
                'count' => $stats['users'],
                'color' => 'danger'
            ];
        }

        // Department Management
        if (PermissionHelper::userCan('department.view')) {
            $modules[] = [
                'name' => 'Quản lý phòng ban',
                'description' => 'Quản lý cơ cấu phòng ban, phân xưởng',
                'icon' => 'la la-building',
                'url' => backpack_url('department'),
                'status' => 'active',
                'count' => $stats['departments'],
                'color' => 'success'
            ];
        }
        
        // Employee Management
        if (PermissionHelper::userCan('employee.view')) {
            $modules[] = [
                'name' => 'Quản lý nhân sự',
                'description' => 'Quản lý thông tin cán bộ, nhân viên',
                'icon' => 'la la-users',
                'url' => backpack_url('employee'),
                'status' => 'active',
                'count' => $stats['employees'],
                'color' => 'info'
            ];
        }

        // Reports
        if (PermissionHelper::userCan('report.view')) {
            $modules[] = [
                'name' => 'Báo cáo quân số',
                'description' => 'Báo cáo và thống kê nhân sự',
                'icon' => 'la la-chart-bar',
                'url' => backpack_url('daily-personnel-report'),
                'status' => 'active',
                'count' => $stats['reports'],
                'color' => 'primary'
            ];
        }

        // Leave Requests
        if (PermissionHelper::userCan('leave.view')) {
            $modules[] = [
                'name' => 'Đăng ký nghỉ phép',
                'description' => 'Quản lý đơn xin nghỉ phép',
                'icon' => 'la la-calendar-check',
                'url' => backpack_url('leave-request'),
                'status' => 'active',
                'count' => $stats['leave_requests'],
                'color' => 'warning'
            ];
        }

        // No more hardcode - everything handled above

        $data = [
            'title' => 'Bảng điều khiển - A31 CMS',
            'modules' => $modules
        ];

        return view('admin.dashboard', $data);
    }
}

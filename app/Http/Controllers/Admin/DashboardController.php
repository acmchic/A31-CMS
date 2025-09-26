<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\OrganizationStructure\Models\Department;
use Modules\OrganizationStructure\Models\Employee;
use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\PersonnelReport\Models\DailyPersonnelReport;

class DashboardController extends Controller
{
    /**
     * Show the custom dashboard
     */
    public function dashboard()
    {
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
        
        // Add department management only for admin and BAN GIÁM ĐỐC
        if (backpack_user()->hasRole('Admin') || backpack_user()->department_id == 1) {
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
        
        // Add other modules
        $modules = array_merge($modules, [
            [
                'name' => 'Quản lý nhân sự',
                'description' => 'Quản lý thông tin cán bộ, nhân viên',
                'icon' => 'la la-users',
                'url' => backpack_url('employee'),
                'status' => 'active',
                'count' => $stats['employees'],
                'color' => 'info'
            ],
            [
                'name' => 'Báo cáo quân số',
                'description' => 'Báo cáo và thống kê nhân sự',
                'icon' => 'la la-chart-bar',
                'url' => backpack_url('daily-personnel-report'),
                'status' => 'active',
                'count' => $stats['reports'],
                'color' => 'primary'
            ],
            [
                'name' => 'Đăng ký nghỉ phép',
                'description' => 'Quản lý đơn xin nghỉ phép',
                'icon' => 'la la-calendar-check',
                'url' => backpack_url('leave-request'),
                'status' => 'active',
                'count' => $stats['leave_requests'],
                'color' => 'warning'
            ],
            [
                'name' => 'Cài đặt hệ thống',
                'description' => 'Cấu hình và thiết lập hệ thống',
                'icon' => 'la la-cog',
                'url' => '#',
                'status' => 'development',
                'count' => 0,
                'color' => 'secondary'
            ]
        ]);

        // Add user management modules only for admin
        if (backpack_user()->hasRole('Admin')) {
            $userManagementModules = [
                [
                    'name' => 'Quản lý người dùng',
                    'description' => 'Quản lý thông tin người dùng hệ thống',
                    'icon' => 'la la-user',
                    'url' => backpack_url('user'),
                    'status' => 'active',
                    'count' => $stats['users'],
                    'color' => 'success'
                ],
                [
                    'name' => 'Quản lý vai trò',
                    'description' => 'Quản lý vai trò và phân quyền',
                    'icon' => 'la la-user-shield',
                    'url' => backpack_url('role'),
                    'status' => 'active',
                    'count' => \Spatie\Permission\Models\Role::count(),
                    'color' => 'danger'
                ],
                [
                    'name' => 'Quản lý quyền',
                    'description' => 'Quản lý quyền trong hệ thống',
                    'icon' => 'la la-key',
                    'url' => backpack_url('permission'),
                    'status' => 'active',
                    'count' => \Spatie\Permission\Models\Permission::count(),
                    'color' => 'info'
                ]
            ];
            
            // Insert user management modules at the beginning
            $modules = array_merge($userManagementModules, $modules);
        }

        $data = [
            'title' => 'Bảng điều khiển - A31 CMS',
            'modules' => $modules
        ];

        return view('admin.dashboard', $data);
    }
}

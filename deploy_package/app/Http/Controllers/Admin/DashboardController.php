<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\OrganizationStructure\Models\Department;
use Modules\OrganizationStructure\Models\Employee;
use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\PersonnelReport\Models\DailyPersonnelReport;
use Modules\VehicleRegistration\Models\VehicleRegistration;
use Modules\RecordManagement\Models\RecordType;
use Modules\FileSharing\Models\SharedFile;
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
            'vehicle_registrations' => VehicleRegistration::count(),
            'shared_files' => SharedFile::count(),
            'users' => User::count(),
        ];

        // Base modules that all users can see - Sắp xếp theo thứ tự ưu tiên
        $modules = [];

        // 1. Reports - Báo cáo quân số (ưu tiên cao nhất)
        if (PermissionHelper::userCan('report.view')) {
            // Ưu tiên: Nếu có quyền xem tổng hợp → link đến summary
            // Nếu không → link đến create-2 (phòng ban)
            $reportUrl = backpack_user()->hasPermissionTo('report.view.company')
                ? backpack_url('daily-personnel-report')
                : backpack_url('daily-personnel-report/create-2');

            $modules[] = [
                'name' => 'Báo cáo quân số',
                'description' => 'Báo cáo và thống kê nhân sự',
                'icon' => 'la la-chart-bar',
                'url' => $reportUrl,
                'status' => 'active',
                'count' => $stats['reports'],
                'color' => 'primary'
            ];
        }

        // 2. Department Management
        if (PermissionHelper::userCan('department.view')) {
            $modules[] = [
                'name' => 'Cơ cấu tổ chức',
                'description' => 'Quản lý cơ cấu phòng ban, phân xưởng',
                'icon' => 'la la-building',
                'url' => backpack_url('department'),
                'status' => 'active',
                'count' => $stats['departments'],
                'color' => 'success'
            ];
        }

        // 3. Employee Management
        if (PermissionHelper::userCan('employee.view')) {
            $modules[] = [
                'name' => 'Quản lý nhân sự',
                'description' => 'Quản lý thông tin cán bộ, Nhân sự',
                'icon' => 'la la-users',
                'url' => backpack_url('employee?department=all'),
                'status' => 'active',
                'count' => $stats['employees'],
                'color' => 'info'
            ];
        }

        

        // 5. Record Management
        if (PermissionHelper::userCan('record_management.view')) {
            $totalRecords = 0;
            try {
                $totalRecords = \Modules\RecordManagement\Models\SalaryUpRecord::count();
                // TODO: Thêm count cho các loại sổ khác khi có
                // $totalRecords += \Modules\RecordManagement\Models\PersonnelRecord::count();
            } catch (\Exception $e) {
                $totalRecords = 0;
            }

            $modules[] = [
                'name' => 'Quản lý sổ sách',
                'description' => 'Quản lý các loại sổ sách',
                'icon' => 'la la-book',
                'url' => backpack_url('record-management'),
                'status' => 'active',
                'count' => $totalRecords,
                'color' => 'info'
            ];
        }

        // 6. Leave Requests
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

        // 4. Vehicle Registration
        if (PermissionHelper::userCan('vehicle_registration.view')) {
            $modules[] = [
                'name' => 'Đăng ký xe',
                'description' => 'Quản lý đăng ký sử dụng xe công vụ',
                'icon' => 'la la-car',
                'url' => backpack_url('vehicle-registration'),
                'status' => 'active',
                'count' => $stats['vehicle_registrations'],
                'color' => 'secondary'
            ];
        }

        // 7. File Sharing
        if (PermissionHelper::userCan('file_sharing.view')) {
            $modules[] = [
                'name' => 'Chia sẻ File',
                'description' => 'Upload và chia sẻ file trong hệ thống',
                'icon' => 'la la-share-alt',
                'url' => url('file-sharing'),
                'status' => 'active',
                'count' => $stats['shared_files'],
                'color' => 'primary'
            ];
        }

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

        // No more hardcode - everything handled above

        $data = [
            'title' => 'Bảng điều khiển - A31 CMS',
            'modules' => $modules
        ];

        return view('admin.dashboard', $data);
    }
}

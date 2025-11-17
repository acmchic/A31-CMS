{{-- Clean menu system using PermissionHelper --}}

{{-- Dashboard --}}
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}">
        <i class="la la-home nav-icon"></i> Trang chủ
    </a>
</li>

{{-- Personnel Reports --}}
@if(backpack_user()->hasPermissionTo('report.view.company') || backpack_user()->hasPermissionTo('report.view') || \App\Helpers\PermissionHelper::userCan('leave.view') || \App\Helpers\PermissionHelper::userCan('leave.create'))
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-reports" data-bs-toggle="dropdown" role="button">
        <i class="la la-chart-bar nav-icon"></i> Báo cáo quân số
    </a>
    <div class="dropdown-menu">
        @php
            // Logic: Ưu tiên "Tổng hợp báo cáo quân số" nếu có quyền report.view.company
            // Nếu không có quyền đó nhưng có report.view thì hiển thị "Báo cáo quân số"
            $hasCompanyReport = backpack_user()->hasPermissionTo('report.view.company');
            $hasDepartmentReport = backpack_user()->hasPermissionTo('report.view');
        @endphp
        
        @if($hasCompanyReport)
        {{-- Hiển thị "Tổng hợp báo cáo quân số" --}}
        <a class="dropdown-item" href="{{ backpack_url('daily-personnel-report') }}">
            <i class="la la-file-text"></i> Tổng hợp báo cáo quân số
        </a>
        @elseif($hasDepartmentReport)
        {{-- Hiển thị "Báo cáo quân số" --}}
        <a class="dropdown-item" href="{{ backpack_url('daily-personnel-report/create-2') }}">
            <i class="la la-chart-line"></i> Báo cáo quân số
        </a>
        @endif
        
        @if(\App\Helpers\PermissionHelper::userCan('leave.view') || \App\Helpers\PermissionHelper::userCan('leave.create'))
        <a class="dropdown-item" href="{{ backpack_url('leave-request') }}">
            <i class="la la-calendar-check"></i> Đăng ký nghỉ phép
        </a>
        @endif
    </div>
</li>
@endif

{{-- Organization Structure --}}
@if(\App\Helpers\PermissionHelper::userCan('department.view') || \App\Helpers\PermissionHelper::userCan('employee.view'))
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-org" data-bs-toggle="dropdown" role="button">
        <i class="la la-building nav-icon"></i> Cơ cấu tổ chức
    </a>
    <div class="dropdown-menu">
        @if(\App\Helpers\PermissionHelper::userCan('employee.view'))
        <a class="dropdown-item" href="{{ backpack_url('employee?department=all') }}">
            <i class="la la-users"></i> Nhân sự
        </a>
        @endif
        
        @if(\App\Helpers\PermissionHelper::userCan('department.view'))
        <a class="dropdown-item" href="{{ backpack_url('department') }}">
            <i class="la la-building"></i> Phòng ban
        </a>
        @endif
    </div>
</li>
@endif

{{-- Vehicle Registration --}}
@if(\App\Helpers\PermissionHelper::userCan('vehicle_registration.view'))
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('vehicle-registration') }}">
        <i class="la la-car nav-icon"></i> Đăng ký xe
    </a>
</li>
@endif

{{-- Record Management --}}
@if(\App\Helpers\PermissionHelper::userCan('record_management.view'))
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('record-management') }}">
        <i class="la la-book nav-icon"></i> Quản lý sổ sách
    </a>
</li>
@endif

{{-- System Management --}}
@if(\App\Helpers\PermissionHelper::userCan('user.view') || \App\Helpers\PermissionHelper::userCan('role.view') || \App\Helpers\PermissionHelper::userCan('permission.view'))
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-system" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
        <i class="la la-cogs nav-icon"></i> Quản lý hệ thống
    </a>
    <div class="dropdown-menu">
        @if(\App\Helpers\PermissionHelper::userCan('user.view'))
        <a class="dropdown-item" href="{{ backpack_url('user') }}">
            <i class="la la-user"></i> Người dùng
        </a>
        @endif
        
        @if(\App\Helpers\PermissionHelper::userCan('role.view'))
        <a class="dropdown-item" href="{{ backpack_url('role') }}">
            <i class="la la-user-shield"></i> Vai trò
        </a>
        @endif
        
        @if(\App\Helpers\PermissionHelper::userCan('permission.view'))
        <a class="dropdown-item" href="{{ backpack_url('permission') }}">
            <i class="la la-key"></i> Quyền hạn
        </a>
        @endif
        
        @if(\App\Helpers\PermissionHelper::userCan('user.view'))
        <a class="dropdown-item" href="{{ backpack_url('system-settings') }}">
            <i class="la la-cog"></i> Cài đặt
        </a>
        @endif
    </div>
</li>
@endif
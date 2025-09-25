{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> Dashboard</a></li>

{{-- User Management Menu --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-extra" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
        <i class="la la-users nav-icon"></i>
        Quản lý người dùng
    </a>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ backpack_url('user') }}">
            <i class="la la-user"></i> Người dùng
        </a>
        <a class="dropdown-item" href="{{ backpack_url('role') }}">
            <i class="la la-user-shield"></i> Vai trò
        </a>
        <a class="dropdown-item" href="{{ backpack_url('permission') }}">
            <i class="la la-key"></i> Quyền
        </a>
    </div>
</li>

{{-- Organization Structure Menu --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-org" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
        <i class="la la-building nav-icon"></i>
        Cơ cấu tổ chức
    </a>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="{{ backpack_url('department') }}">
            <i class="la la-building"></i> Phòng ban
        </a>
        <a class="dropdown-item" href="{{ backpack_url('employee') }}">
            <i class="la la-users"></i> Nhân sự
        </a>
    </div>
</li>

{{-- Reports Menu --}}
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#navbar-reports" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
        <i class="la la-chart-bar nav-icon"></i>
        Báo cáo
    </a>
    <div class="dropdown-menu">
        <a class="dropdown-item" href="#" onclick="alert('Đang phát triển')">
            <i class="la la-chart-line"></i> Báo cáo quân số
        </a>
        <a class="dropdown-item" href="#" onclick="alert('Đang phát triển')">
            <i class="la la-calendar"></i> Báo cáo nghỉ phép
        </a>
    </div>
</li>

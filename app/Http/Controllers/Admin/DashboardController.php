<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show the custom dashboard
     */
    public function dashboard()
    {
        // You can add any data you need for the dashboard here
        $data = [
            'title' => 'Dashboard - A31 CMS',
            'modules' => [
                [
                    'name' => 'Quản lý người dùng',
                    'description' => 'Quản lý thông tin người dùng hệ thống',
                    'icon' => 'la la-users',
                    'url' => backpack_url('user'),
                    'status' => 'active'
                ],
                [
                    'name' => 'Quản lý vai trò',
                    'description' => 'Quản lý vai trò và phân quyền',
                    'icon' => 'la la-user-shield',
                    'url' => backpack_url('role'),
                    'status' => 'active'
                ],
                [
                    'name' => 'Quản lý quyền',
                    'description' => 'Quản lý quyền trong hệ thống',
                    'icon' => 'la la-key',
                    'url' => backpack_url('permission'),
                    'status' => 'active'
                ],
                [
                    'name' => 'Quản lý phòng ban',
                    'description' => 'Quản lý cơ cấu phòng ban, phân xưởng',
                    'icon' => 'la la-building',
                    'url' => backpack_url('department'),
                    'status' => 'active'
                ],
                [
                    'name' => 'Quản lý nhân sự',
                    'description' => 'Quản lý thông tin cán bộ, nhân viên',
                    'icon' => 'la la-users',
                    'url' => backpack_url('employee'),
                    'status' => 'active'
                ],
                [
                    'name' => 'Báo cáo quân số',
                    'description' => 'Báo cáo và thống kê nhân sự',
                    'icon' => 'la la-chart-bar',
                    'url' => '#',
                    'status' => 'development'
                ],
                [
                    'name' => 'Cài đặt hệ thống',
                    'description' => 'Cấu hình và thiết lập hệ thống',
                    'icon' => 'la la-cog',
                    'url' => '#',
                    'status' => 'development'
                ]
            ]
        ];
        
        return view('admin.dashboard', $data);
    }
}

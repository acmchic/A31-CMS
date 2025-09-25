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
                    'name' => 'Quản lý nội dung',
                    'description' => 'Quản lý bài viết, trang nội dung',
                    'icon' => 'la la-file-text',
                    'url' => '#',
                    'status' => 'active'
                ],
                [
                    'name' => 'Cài đặt hệ thống',
                    'description' => 'Cấu hình và thiết lập hệ thống',
                    'icon' => 'la la-cog',
                    'url' => '#',
                    'status' => 'development'
                ],
                [
                    'name' => 'Báo cáo thống kê',
                    'description' => 'Xem báo cáo và thống kê hệ thống',
                    'icon' => 'la la-chart-bar',
                    'url' => '#',
                    'status' => 'development'
                ],
                [
                    'name' => 'Quản lý file',
                    'description' => 'Upload và quản lý file, hình ảnh',
                    'icon' => 'la la-folder',
                    'url' => '#',
                    'status' => 'development'
                ],
                [
                    'name' => 'Logs hệ thống',
                    'description' => 'Theo dõi logs và hoạt động hệ thống',
                    'icon' => 'la la-list',
                    'url' => '#',
                    'status' => 'development'
                ]
            ]
        ];
        
        return view('admin.dashboard', $data);
    }
}

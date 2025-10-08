<?php

namespace Modules\RecordManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecordManagementController extends Controller
{
    /**
     * Display a listing of all record types (sổ sách)
     */
    public function index()
    {
        if (!(\App\Helpers\PermissionHelper::userCan('record_management.view'))) {
            abort(403, 'Không có quyền truy cập');
        }

        // Danh sách các loại sổ
        $recordTypes = [
            [
                'name' => 'Sổ Danh sách Quân nhân',
                'description' => 'Danh sách chi tiết thông tin quân nhân trong đơn vị',
                'icon' => 'la la-id-card',
                'color' => 'primary',
                'route' => 'quan-nhan-record',
                'permission' => 'record_management.view',
                'count' => \Modules\RecordManagement\Models\QuanNhanRecord::count(),
            ],
            [
                'name' => 'Sổ Đăng ký Điều động nội bộ',
                'description' => 'Quản lý điều động nội bộ (Sĩ quan, QNCN, CN & VCQP)',
                'icon' => 'la la-exchange-alt',
                'color' => 'info',
                'route' => 'so-dieu-dong-record',
                'permission' => 'record_management.view',
                'count' => \Modules\RecordManagement\Models\SoDieuDongRecord::count(),
            ],
            [
                'name' => 'Sổ nâng lương',
                'description' => 'Quản lý nâng lương, nâng loại, chuyển nhóm',
                'icon' => 'la la-money-bill-wave',
                'color' => 'success',
                'route' => 'salary-up-record',
                'permission' => 'record_management.view',
                'count' => \Modules\RecordManagement\Models\SalaryUpRecord::count(),
            ],
            // TODO: Thêm các loại sổ khác ở đây
        ];

        // Filter by permission
        $recordTypes = array_filter($recordTypes, function($type) {
            return \App\Helpers\PermissionHelper::userCan($type['permission']);
        });

        return view('recordmanagement::index', compact('recordTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('recordmanagement::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('recordmanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('recordmanagement::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}

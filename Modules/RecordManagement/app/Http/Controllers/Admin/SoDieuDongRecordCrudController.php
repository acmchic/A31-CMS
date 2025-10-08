<?php

namespace Modules\RecordManagement\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\RecordManagement\Models\SoDieuDongRecord;
use Modules\RecordManagement\Traits\EmployeeSelectionTrait;
use App\Helpers\PermissionHelper;

class SoDieuDongRecordCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use EmployeeSelectionTrait;

    public function setup()
    {
        CRUD::setModel(SoDieuDongRecord::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/so-dieu-dong-record');
        CRUD::setEntityNameStrings('sổ điều động', 'sổ điều động');
    }

    protected function setupListOperation()
    {
        if (!PermissionHelper::userCan('so_dieu_dong_record.view')) {
            abort(403, 'Không có quyền truy cập');
        }

        CRUD::orderBy('id', 'asc');

        CRUD::column('employee.name')->label('Họ tên')->priority(1)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhereHas('employee', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%'.$searchTerm.'%');
            });
        });

        CRUD::column('department_name')
            ->label('Phòng ban')
            ->type('closure')
            ->function(function($entry) {
                return $entry->department ? $entry->department->name : 'Chưa có';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas('department', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            })
            ->priority(2);

        CRUD::column('so_quyet_dinh')->label('Số quyết định')->priority(3)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('so_quyet_dinh', 'like', '%'.$searchTerm.'%');
        });

        CRUD::column('ngay_quyet_dinh')->label('Ngày quyết định')->type('date')->priority(4);

        CRUD::column('tu_don_vi')->label('Từ đơn vị')->priority(5)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('tu_don_vi', 'like', '%'.$searchTerm.'%');
        });

        CRUD::column('den_don_vi')->label('Đến đơn vị')->priority(6)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('den_don_vi', 'like', '%'.$searchTerm.'%');
        });

        CRUD::column('chuc_vu_moi')->label('Chức vụ mới')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('chuc_vu_moi', 'like', '%'.$searchTerm.'%');
        });

        CRUD::column('ngay_hieu_luc')->label('Ngày hiệu lực')->type('date');
    }

    protected function setupCreateOperation()
    {
        if (!PermissionHelper::userCan('so_dieu_dong_record.create')) {
            abort(403, 'Không có quyền tạo mới');
        }

        // Thông tin cơ bản
        $this->addDepartmentField();
        $this->addEmployeeField();

        // Thông tin cá nhân (chỉ hiển thị nhập ngũ và chức vụ)
        $this->addEmployeeInfoFields(['nhap_ngu', 'chuc_vu']);

        // Thông tin điều động
        $this->addDieuDongFields();

        // Add JavaScript
        CRUD::addField([
            'name' => 'auto_fill_script',
            'type' => 'custom_html',
            'value' => $this->getEmployeeSelectionScript() . $this->getEmployeeInfoScript([
                'nhap-ngu-field' => 'enlist_date',
                'chuc-vu-field' => 'position_name'
            ]),
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        if (!PermissionHelper::userCan('so_dieu_dong_record.view')) {
            abort(403, 'Không có quyền xem');
        }

        CRUD::set('show.setFromDb', false);
        
        // Get the entry
        $entry = $this->crud->getCurrentEntry();
        $employee = $entry->employee;

        // Thông tin cá nhân
        if ($employee) {
            CRUD::column('emp_ho_ten')->label('Họ tên')->type('text')->value($employee->name);
            CRUD::column('emp_nhap_ngu')->label('Nhập ngũ')->type('text')->value($employee->enlist_date);
            CRUD::column('emp_chuc_vu')->label('Chức vụ CNQC')->type('text')->value($employee->position ? $employee->position->name : 'Chưa có');
        }

        // Thông tin điều động
        CRUD::column('so_quyet_dinh')->label('Số quyết định')->type('text');
        CRUD::column('ngay_quyet_dinh')->label('Ngày quyết định')->type('date');
        // CRUD::column('nguoi_ky')->label('Người ký')->type('text'); // Ẩn trường này
        // CRUD::column('chuc_vu_nguoi_ky')->label('Chức vụ người ký')->type('text'); // Ẩn trường này
        // CRUD::column('ly_do_dieu_dong')->label('Lý do điều động')->type('text'); // Ẩn trường này
        CRUD::column('tu_don_vi')->label('Từ đơn vị')->type('text');
        CRUD::column('den_don_vi')->label('Đến đơn vị')->type('text');
        // CRUD::column('chuc_vu_cu')->label('Chức vụ cũ')->type('text'); // Ẩn trường này
        // CRUD::column('chuc_vu_moi')->label('Chức vụ mới')->type('text'); // Ẩn trường này
        CRUD::column('ngay_hieu_luc')->label('Ngày hiệu lực')->type('date');
        CRUD::column('ghi_chu')->label('Ghi chú')->type('textarea');
    }


    private function addDieuDongFields()
    {
        CRUD::addField([
            'name' => 'tu_don_vi',
            'label' => 'Đơn vị đi',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'den_don_vi',
            'label' => 'Đơn vị đến',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'so_quyet_dinh',
            'label' => 'Số QĐ',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'ngay_quyet_dinh',
            'label' => 'Ngày ký QĐ',
            'type' => 'date',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'ngay_hieu_luc',
            'label' => 'Ngày có mặt',
            'type' => 'date',
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

    }

    // API endpoints
    public function getEmployeesByDepartment($departmentId)
    {
        try {
            \Log::info('API called: getEmployeesByDepartment', ['departmentId' => $departmentId]);
            
            $employees = \Modules\OrganizationStructure\Models\Employee::where('department_id', $departmentId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);

            \Log::info('Found employees', ['count' => $employees->count()]);
            return response()->json($employees);
        } catch (\Exception $e) {
            \Log::error('API error: getEmployeesByDepartment', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getEmployeeInfo($employeeId)
    {
        try {
            $employee = \Modules\OrganizationStructure\Models\Employee::with('department', 'position')->find($employeeId);
            
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }

            return response()->json([
                'id' => $employee->id,
                'name' => $employee->name,
                'enlist_date' => $employee->enlist_date,
                'position_name' => $employee->position ? $employee->position->name : null,
                'department_id' => $employee->department_id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

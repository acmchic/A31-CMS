<?php

namespace Modules\PersonnelReport\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\PersonnelReport\Models\DailyPersonnelReport;
use Modules\OrganizationStructure\Models\Department;

class DailyPersonnelReportCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(DailyPersonnelReport::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/daily-personnel-report');
        CRUD::setEntityNameStrings('báo cáo quân số', 'báo cáo quân số');

        CRUD::orderBy('report_date', 'DESC');

        // Apply department filtering based on user permissions
        $this->applyDepartmentFilter();
        
        // Setup buttons based on user role
        $this->setupButtonsForRole();
    }

    /**
     * Apply department filtering based on user permissions
     */
    private function applyDepartmentFilter()
    {
        $user = backpack_user();

        // Admin and BAN GIÁM ĐỐC can see all data
        if ($user->hasRole('admin') || $user->department_id == 1) {
            return; // No filtering for admin and BAN GIÁM ĐỐC
        }

        // First try to get department from user's direct department_id
        $departmentId = $user->department_id;

        // Fallback to employee's department if user doesn't have direct department
        if (!$departmentId && $user->employee) {
            $departmentId = $user->employee->department_id;
        }

        if ($departmentId) {
            CRUD::addClause('where', 'department_id', $departmentId);
        } else {
            // If user has no department, show no reports
            CRUD::addClause('where', 'id', 0);
        }
    }

    /**
     * Setup buttons based on user role
     */
    private function setupButtonsForRole()
    {
        $user = backpack_user();
        
        // For Nhân viên role - only view, no create/edit/delete
        if ($user->hasRole('Nhân viên')) {
            CRUD::removeButton('create');
            CRUD::removeButton('edit');
            CRUD::removeButton('delete');
        }
    }

    protected function setupListOperation()
    {
        CRUD::column('report_date')
            ->label('Ngày báo cáo')
            ->type('date');

        CRUD::column('department_name')
            ->label('Phòng ban')
            ->type('closure')
            ->function(function($entry) {
                return $entry->department ? $entry->department->name : 'N/A';
            });

        CRUD::column('total_employees')
            ->label('Tổng số')
            ->type('number');

        CRUD::column('present_count')
            ->label('Có mặt')
            ->type('number');

        CRUD::column('on_leave_count')
            ->label('Nghỉ phép')
            ->type('number');

        CRUD::column('absent_count')
            ->label('Vắng mặt')
            ->type('number');


        // Note: Filters removed to avoid Backpack PRO requirement
        // You can add custom filtering logic in the controller if needed
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'department_id' => 'required|exists:departments,id',
            'report_date' => 'required|date',
        ]);

        // Filter departments based on user's department
        $departmentOptions = [];
        $user = backpack_user();
        if ($user->hasRole('admin') || $user->department_id == 1) {
            $departmentOptions = Department::pluck('name', 'id')->toArray();
        } else {
            // First try to get department from user's direct department_id
            $departmentId = $user->department_id;

            // Fallback to employee's department if user doesn't have direct department
            if (!$departmentId && $user->employee) {
                $departmentId = $user->employee->department_id;
            }

            if ($departmentId) {
                $department = Department::find($departmentId);
                if ($department) {
                    $departmentOptions = [$departmentId => $department->name];
                }
            }
        }

        CRUD::field('department_id')
            ->label('Phòng ban')
            ->type('select_from_array')
            ->options($departmentOptions)
            ->tab('Thông tin cơ bản');

        CRUD::field('report_date')
            ->label('Ngày báo cáo')
            ->type('date')
            ->default(today())
            ->tab('Thông tin cơ bản');

        // Read-only fields that will be calculated
        CRUD::field('total_employees')
            ->label('Tổng số nhân viên')
            ->type('number')
            ->attributes(['readonly' => true])
            ->tab('Thống kê');

        CRUD::field('present_count')
            ->label('Số người có mặt')
            ->type('number')
            ->tab('Thống kê');

        CRUD::field('absent_count')
            ->label('Số người vắng mặt')
            ->type('number')
            ->tab('Thống kê');

        CRUD::field('on_leave_count')
            ->label('Số người nghỉ phép')
            ->type('number')
            ->tab('Thống kê');

        CRUD::field('sick_count')
            ->label('Công tác')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');

        CRUD::field('annual_leave_count')
            ->label('Cơ Động')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');

        CRUD::field('personal_leave_count')
            ->label('Đi học')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');

        CRUD::field('military_leave_count')
            ->label('Nghỉ phép')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');

        CRUD::field('other_leave_count')
            ->label('Khác')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();

        CRUD::column('sick_count')->label('Công tác');
        CRUD::column('annual_leave_count')->label('Cơ Động');
        CRUD::column('personal_leave_count')->label('Đi học');
        CRUD::column('military_leave_count')->label('Nghỉ phép');
        CRUD::column('other_leave_count')->label('Khác');
        CRUD::column('created_by')->label('Người tạo');
        CRUD::column('updated_by')->label('Người cập nhật');
    }

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $user = backpack_user();
        $request = $this->crud->getRequest();

        // Auto-generate report data
        $departmentId = $request->input('department_id');
        $reportDate = $request->input('report_date');

        // Generate the report data automatically
        $reportData = DailyPersonnelReport::generateReport($departmentId, $reportDate);

        if ($reportData) {
            $request->merge($reportData->toArray());
        }

        $request->merge([
            'created_by' => $user->name ?: $user->username,
            'updated_by' => $user->name ?: $user->username
        ]);

        $this->crud->setRequest($request);
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitStore();
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $user = backpack_user();
        $request = $this->crud->getRequest();

        $request->merge(['updated_by' => $user->name ?: $user->username]);

        $this->crud->setRequest($request);
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }
}

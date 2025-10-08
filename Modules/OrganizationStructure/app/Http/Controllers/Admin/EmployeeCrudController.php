<?php

namespace Modules\OrganizationStructure\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\OrganizationStructure\Models\Employee;
use Modules\OrganizationStructure\Models\Department;
use Modules\OrganizationStructure\Models\Position;
use App\Models\User;
use App\Helpers\DateHelper;

class EmployeeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation { index as traitIndex; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Employee::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/employee');
        CRUD::setEntityNameStrings('nhân sự', 'nhân sự');

        // Order by id ASC
        CRUD::orderBy('id', 'ASC');

        // Setup buttons based on user role
        $this->setupButtonsForRole();

        // Apply department filtering logic
        $this->applyDepartmentFiltering();
    }

    /**
     * Apply department filtering logic
     */
    private function applyDepartmentFiltering()
    {
        // Xử lý filter department từ URL parameter
        if (request()->has('department') && request()->get('department')) {
            $departmentParam = request()->get('department');
            
            if ($departmentParam === 'all') {
                // Hiển thị tất cả nhân sự - không filter gì
                return;
            } else {
                // Filter theo department ID cụ thể
                $departmentId = $departmentParam;
                $this->crud->addClause('where', 'department_id', $departmentId);
            }
        } else {
            // Nếu không có parameter department, redirect đến ?department=all
            $currentUrl = request()->fullUrl();
            if (strpos($currentUrl, '?department=') === false) {
                return redirect()->to($currentUrl . (strpos($currentUrl, '?') !== false ? '&' : '?') . 'department=all');
            }
        }
    }

    /**
     * Apply department filtering based on user permissions
     */
    private function applyDepartmentFilter()
    {
        $user = backpack_user();

        // Admin và BAN GIÁM ĐỐC có thể xem tất cả
        if ($user->hasRole('Admin') || $user->department_id == 1) {
            return; // No filtering for admin and BAN GIÁM ĐỐC
        }

        // Lấy department_id từ user
        $departmentId = $user->department_id;

        // Fallback: nếu user không có department_id, thử lấy từ employee record
        if (!$departmentId) {
            $userEmployee = Employee::where('user_id', $user->id)->first();
            if ($userEmployee) {
                $departmentId = $userEmployee->department_id;
            }
        }

        if ($departmentId) {
            CRUD::addClause('where', 'department_id', $departmentId);
        } else {
            // Nếu không có department_id, không hiển thị gì
            CRUD::addClause('where', 'id', 0);
        }
    }

    /**
     * Setup buttons based on user role
     */
    private function setupButtonsForRole()
    {
        $user = backpack_user();

        // For Nhân sự role - only view, no create/edit/delete
        if ($user->hasRole('Nhân sự')) {
            CRUD::removeButton('create');
            CRUD::removeButton('edit');
            CRUD::removeButton('delete');
        }
    }


    /**
     * Override index to add department filter
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        // Get all departments for filter
        $departments = Department::orderBy('id', 'ASC')->get();
        $currentDepartment = request()->get('department', 'all');
        
        // Pass data to default view
        $this->data['departments'] = $departments;
        $this->data['currentDepartment'] = $currentDepartment;
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? 'Nhân sự';

        // Use default Backpack list view but with custom data
        return $this->traitIndex();
    }

    protected function setupListOperation()
    {
        // Set default ordering by ID ascending
        $this->crud->orderBy('id', 'ASC');

        // Add STT column (sequential number)
        CRUD::column('stt')
            ->label('STT')
            ->type('text')
            ->orderable(false)
            ->priority(1)
            ->value(''); // Empty value, will be filled by JavaScript

        CRUD::column('name')
            ->label('Họ và tên')
            ->orderable(false)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('name', 'like', '%'.$searchTerm.'%');
            });

        CRUD::column('rank_code')
            ->label('Cấp bậc')
            ->orderable(false)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('rank_code', 'like', '%'.$searchTerm.'%');
            });

        CRUD::column('position')
            ->label('Chức vụ')
            ->type('select')
            ->entity('position')
            ->attribute('name')
            ->model('Modules\OrganizationStructure\Models\Position')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas('position', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            });

        CRUD::column('department')
            ->label('Đơn vị')
            ->type('select')
            ->entity('department')
            ->attribute('name')
            ->model('Modules\OrganizationStructure\Models\Department')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas('department', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            });

        CRUD::column('date_of_birth')
            ->label('Ngày sinh')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->date_of_birth);
            })
            ->orderable(false)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('date_of_birth', 'like', '%'.$searchTerm.'%');
            });

        CRUD::column('address')
            ->label('Quê quán')
            ->orderable(false)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('address', 'like', '%'.$searchTerm.'%');
            });
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'rank_code' => 'required|string|max:50',
            'CCCD' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:15',
            'address' => 'nullable|string|max:500',
            'gender' => 'nullable|boolean',
            'date_of_birth' => 'nullable|date',
            'enlist_date' => 'nullable|string|max:20',
            'start_date' => 'nullable|date',
            'quit_date' => 'nullable|date',
            'max_leave_allowed' => 'nullable|integer|min:0',
            'annual_leave_balance' => 'nullable|integer|min:0',
            'annual_leave_total' => 'nullable|integer|min:0',
            'annual_leave_used' => 'nullable|integer|min:0',
            'delay_counter' => 'nullable|integer|min:0',
            'hourly_counter' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Thông tin cơ bản
        CRUD::field('name')
            ->label('Họ và tên')
            ->type('text')
            ->tab('Thông tin cơ bản');

        CRUD::field('department_id')
            ->label('Phòng ban')
            ->type('select_from_array')
            ->options(Department::pluck('name', 'id')->toArray())
            ->tab('Thông tin cơ bản');

        CRUD::field('position_id')
            ->label('Chức vụ')
            ->type('select_from_array')
            ->options(Position::pluck('name', 'id')->toArray())
            ->allows_null(true)
            ->tab('Thông tin cơ bản');

        CRUD::field('rank_code')
            ->label('Cấp bậc')
            ->type('text')
            ->tab('Thông tin cơ bản');

        CRUD::field('CCCD')
            ->label('CCCD/CMND')
            ->type('text')
            ->tab('Thông tin cơ bản');

        CRUD::field('date_of_birth')
            ->label('Ngày sinh')
            ->type('date')
            ->tab('Thông tin cơ bản');

        // Chuyển Ngày nhập ngũ sang tab Thông tin cơ bản
        CRUD::field('enlist_date')
            ->label('Ngày nhập ngũ')
            ->type('text')
            ->hint('Format: mm/yyyy (ví dụ: 09/1991)')
            ->tab('Thông tin cơ bản');

        // Thông tin cá nhân
        CRUD::field('phone')
            ->label('Số điện thoại')
            ->type('text')
            ->tab('Thông tin cá nhân');

        CRUD::field('address')
            ->label('Địa chỉ')
            ->type('textarea')
            ->tab('Thông tin cá nhân');

        CRUD::field('gender')
            ->label('Giới tính')
            ->type('select_from_array')
            ->options([
                1 => 'Nam',
                0 => 'Nữ'
            ])
            ->allows_null(true)
            ->tab('Thông tin cá nhân');


        CRUD::field('max_leave_allowed')
            ->label('Số ngày phép tối đa/năm')
            ->type('number')
            ->default(0)
            ->tab('Thông tin nghỉ phép');

        CRUD::field('annual_leave_balance')
            ->label('Số ngày phép còn lại')
            ->type('number')
            ->default(0)
            ->tab('Thông tin nghỉ phép');

        CRUD::field('annual_leave_total')
            ->label('Tổng số ngày phép/năm')
            ->type('number')
            ->default(0)
            ->tab('Thông tin nghỉ phép');

        CRUD::field('annual_leave_used')
            ->label('Số ngày phép đã dùng')
            ->type('number')
            ->default(0)
            ->tab('Thông tin nghỉ phép');

        CRUD::field('delay_counter')
            ->label('Số lần đi muộn')
            ->type('number')
            ->default(0)
            ->tab('Thông tin chấm công');

        CRUD::field('hourly_counter')
            ->label('Số giờ làm thêm')
            ->type('number')
            ->default(0)
            ->tab('Thông tin chấm công');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        CRUD::column('name')->label('Họ tên');

        CRUD::column('department_name')
            ->label('Phòng ban')
            ->type('closure')
            ->function(function($entry) {
                return $entry->department ? $entry->department->name : 'Chưa có';
            });

        CRUD::column('position_name')
            ->label('Chức vụ')
            ->type('closure')
            ->function(function($entry) {
                return $entry->position ? $entry->position->name : 'Chưa có';
            });

        CRUD::column('rank_code')->label('Cấp bậc');
        CRUD::column('CCCD')->label('CCCD/CMND');
        CRUD::column('phone')->label('Điện thoại');
        CRUD::column('address')->label('Địa chỉ');

        CRUD::column('gender')->label('Giới tính')
            ->type('closure')
            ->function(function($entry) {
                return $entry->gender === 1 ? 'Nam' : ($entry->gender === 0 ? 'Nữ' : '-');
            });

        CRUD::column('date_of_birth')->label('Ngày sinh')
            ->type('closure')
            ->function(function($entry) {
                return \App\Helpers\DateHelper::formatDate($entry->date_of_birth);
            })
            ->orderable(false)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('date_of_birth', 'like', '%'.$searchTerm.'%');
            });

        CRUD::column('enlist_date')->label('Ngày nhập ngũ')
            ->type('closure')
            ->function(function($entry) {
                return $entry->enlist_date ?? '--';
            });


    }

    public function store()
    {
        $this->crud->addField(['type' => 'hidden', 'name' => 'created_by', 'value' => backpack_user()->name]);
        $this->crud->addField(['type' => 'hidden', 'name' => 'updated_by', 'value' => backpack_user()->name]);

        return $this->traitStore();
    }

    public function update()
    {
        $this->crud->addField(['type' => 'hidden', 'name' => 'updated_by', 'value' => backpack_user()->name]);

        return $this->traitUpdate();
    }
}

<?php

namespace Modules\OrganizationStructure\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\OrganizationStructure\Models\Employee;
use Modules\OrganizationStructure\Models\Department;
use Modules\OrganizationStructure\Models\Position;
use App\Models\User;

class EmployeeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
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

        // PHÂN QUYỀN THEO DEPARTMENT (tạm tắt để debug)
        // $this->applyDepartmentFilter();
    }

    /**
     * Apply department filtering based on user permissions
     */
    private function applyDepartmentFilter()
    {
        $user = backpack_user();

        // Nếu không phải admin, chỉ thấy nhân sự trong department của mình
        if (!$user->hasRole('admin')) {
            $userEmployee = Employee::where('user_id', $user->id)->first();
            if ($userEmployee) {
                CRUD::addClause('where', 'department_id', $userEmployee->department_id);
            } else {
                // Nếu user không có employee record, không show gì
                CRUD::addClause('where', 'id', 0);
            }
        }
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');
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
            'enlist_date' => 'nullable|date',
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
            ->type('date')
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
                return $entry->gender === 1 ? 'Nam' : ($entry->gender === 0 ? 'Nữ' : 'Chưa xác định');
            });

        CRUD::column('date_of_birth')->label('Ngày sinh')
            ->type('closure')
            ->function(function($entry) {
                return $entry->date_of_birth ? $entry->date_of_birth->format('d/m/Y') : '';
            });

        CRUD::column('enlist_date')->label('Ngày nhập ngũ')
            ->type('closure')
            ->function(function($entry) {
                return $entry->enlist_date ? $entry->enlist_date->format('d/m/Y') : '';
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

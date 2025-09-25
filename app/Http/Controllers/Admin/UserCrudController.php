<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as BaseUserCrudController;
use Modules\OrganizationStructure\Models\Department;

class UserCrudController extends BaseUserCrudController
{
    public function setup()
    {
        parent::setup();
        
        // Check if user has Admin role
        if (!backpack_user()->hasRole('Admin')) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }
    }

    public function setupListOperation()
    {
        parent::setupListOperation();
        
        // Add department column
        $this->crud->addColumn([
            'name'  => 'department_name',
            'label' => 'Phòng ban',
            'type'  => 'closure',
            'function' => function($entry) {
                return $entry->department ? $entry->department->name : 'N/A';
            },
        ]);
    }

    public function setupCreateOperation()
    {
        parent::setupCreateOperation();
        $this->addDepartmentField();
    }

    public function setupUpdateOperation()
    {
        parent::setupUpdateOperation();
        $this->addDepartmentField();
    }

    public function setupShowOperation()
    {
        parent::setupShowOperation();
        
        // Add department column to show
        $this->crud->addColumn([
            'name'  => 'department_name',
            'label' => 'Phòng ban',
            'type'  => 'closure',
            'function' => function($entry) {
                return $entry->department ? $entry->department->name : 'N/A';
            },
        ]);
    }

    protected function addDepartmentField()
    {
        // Get all departments for dropdown
        $departments = Department::all()->pluck('name', 'id')->toArray();
        
        // Add department field
        $this->crud->addField([
            'name'  => 'department_id',
            'label' => 'Phòng ban',
            'type'  => 'select_from_array',
            'options' => $departments,
            'allows_null' => true,
            'tab' => 'Thông tin cơ bản',
        ]);
    }
}

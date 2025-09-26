<?php

namespace Modules\OrganizationStructure\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\OrganizationStructure\Models\Department;

class DepartmentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Department::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/department');
        CRUD::setEntityNameStrings('phòng ban', 'phòng ban');
        
        // Order by id ASC
        CRUD::orderBy('id', 'ASC');
        
        // Apply department filtering based on user permissions
        $this->applyDepartmentFilter();
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
        
        if ($departmentId) {
            CRUD::addClause('where', 'id', $departmentId);
        } else {
            // Nếu không có department_id, không hiển thị gì
            CRUD::addClause('where', 'id', 0);
        }
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('name')->label('Tên phòng ban');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');
        CRUD::column('created_by')->label('Người tạo');
        
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'name' => 'required|string|max:255|unique:departments',
        ]);

        CRUD::field('name')
            ->label('Tên phòng ban')
            ->type('text')
            ->hint('Ví dụ: Phòng Kế hoạch, Phân xưởng 1, Tổ sản xuất A...');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        
        CRUD::setValidation([
            'name' => 'required|string|max:255|unique:departments,name,' . CRUD::getCurrentEntryId(),
        ]);
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();
        
        CRUD::column('updated_by')->label('Người cập nhật');
    }

    public function store()
    {
        // Add created_by before saving
        request()->merge(['created_by' => backpack_user()->name]);
        
        return $this->backpackStore();
    }

    public function update()
    {
        // Add updated_by before saving
        request()->merge(['updated_by' => backpack_user()->name]);
        
        return $this->backpackUpdate();
    }
}

<?php

namespace Modules\OrganizationStructure\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\OrganizationStructure\Models\Department;
use App\Helpers\PermissionHelper;

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
     * Apply department filtering based on user permissions - clean approach
     */
    private function applyDepartmentFilter()
    {
        $user = backpack_user();
        $scope = PermissionHelper::getUserScope($user);

        switch ($scope) {
            case 'all':
            case 'company':
                // No filtering - can see all departments
                break;

            case 'department':
                // Can see own department only
                if ($user->department_id) {
                    CRUD::addClause('where', 'id', $user->department_id);
                } else {
                    CRUD::addClause('where', 'id', 0);
                }
                break;

            case 'own':
            default:
                // Can see own department only
                if ($user->department_id) {
                    CRUD::addClause('where', 'id', $user->department_id);
                } else {
                    CRUD::addClause('where', 'id', 0);
                }
                break;
        }
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('name')->label('Tên phòng ban');
        // CRUD::column('created_at')->label('Ngày tạo')->type('datetime'); // Ẩn cột này
        // CRUD::column('created_by')->label('Người tạo'); // Ẩn cột này

    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'name' => 'required|string|max:255|unique:departments',
        ]);

        CRUD::field('name')
            ->label('Tên phòng ban')
            ->type('text')
            ->hint('');
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

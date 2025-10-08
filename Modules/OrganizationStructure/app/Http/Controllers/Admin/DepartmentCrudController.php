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
        // Load employees count for each department
        CRUD::addClause('withCount', 'employees');
        
        // Don't show default columns since we're using custom view
        // CRUD::column('id')->label('ID');
        // CRUD::column('name')->label('Tên phòng ban');
    }

    /**
     * Override index method to use custom view
     */
    public function index()
    {
        $this->crud->hasAccessOrFail('list');

        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? mb_ucfirst($this->crud->entity_name_plural);

        // Apply the same filtering logic as setupListOperation
        $query = $this->crud->model->withCount('employees');
        
        // Apply department filtering based on user permissions (same as setup method)
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
                    $query->where('id', $user->department_id);
                } else {
                    $query->where('id', 0);
                }
                break;

            case 'own':
            default:
                // Can see own department only
                if ($user->department_id) {
                    $query->where('id', $user->department_id);
                } else {
                    $query->where('id', 0);
                }
                break;
        }

        // Apply ordering
        $query->orderBy('id', 'ASC');

        $entries = $query->get();
        
        // Add computed data for each department
        $entries->each(function ($department) {
            $iconData = $this->getDepartmentIcon($department->id, $department->name);
            $department->icon = $iconData['icon'];
            $department->icon_color = $iconData['color'];
            $department->progress_percentage = $this->getProgressPercentage($department->employee_count);
            $department->progress_text = $this->getProgressText($department->employee_count);
        });

        $this->data['entries'] = $entries;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view('organizationstructure::admin.department.index', $this->data);
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

    /**
     * Get department icon and color based on department name
     */
    public function getDepartmentIcon($departmentId, $departmentName = null)
    {
        // Define icon mapping by ID (more reliable)
        switch ($departmentId) {
            case 1: // BAN GIÁM ĐỐC
                return ['icon' => 'star', 'color' => 'warning'];
            case 2: // Phòng Kế hoạch
                return ['icon' => 'chart-line', 'color' => 'primary'];
            case 3: // Ban Chính trị
                return ['icon' => 'flag', 'color' => 'danger'];
            case 4: // Phòng Kỹ thuật
                return ['icon' => 'cogs', 'color' => 'warning'];
            case 5: // Phòng Cơ điện
                return ['icon' => 'bolt', 'color' => 'info'];
            case 6: // Phòng Vật tư
                return ['icon' => 'box', 'color' => 'warning'];
            case 7: // Phòng kiểm tra chất lượng
                return ['icon' => 'check-circle', 'color' => 'info'];
            case 8: // Phòng Tài chính
                return ['icon' => 'calculator', 'color' => 'info'];
            case 9: // Phòng Hành chính-Hậu cần
                return ['icon' => 'clipboard', 'color' => 'secondary'];
            case 10: // PX1: Đài điều khiển
            case 11: // PX2: BỆ PHÓNG
            case 12: // PX3: SC XE ĐẶC CHỦNG
            case 13: // PX4: CƠ KHÍ
            case 14: // PX5: KÍP, ĐẠN TÊN LỬA
            case 15: // PX6: XE MÁY-TNĐ
            case 16: // PX7: ĐO LƯỜNG
            case 17: // PX8: ĐỘNG CƠ-BIẾN THẾ
            case 18: // PX 9: HÓA NGHIỆM PHỤC HỒI
                return ['icon' => 'industry', 'color' => 'secondary'];
            default:
                return ['icon' => 'building', 'color' => 'secondary'];
        }
    }

    /**
     * Get progress percentage based on employee count
     */
    public function getProgressPercentage($employeeCount)
    {
        // Scale based on realistic department sizes (0-100 employees)
        $maxEmployees = 100;
        $percentage = ($employeeCount / $maxEmployees) * 100;
        return min(100, max(5, $percentage)); // Minimum 5%, maximum 100%
    }

    /**
     * Get progress text based on employee count
     */
    public function getProgressText($employeeCount)
    {
        if ($employeeCount == 0) {
            return 'Chưa có nhân sự';
        } elseif ($employeeCount < 10) {
            return 'Phòng ban nhỏ';
        } elseif ($employeeCount < 30) {
            return 'Phòng ban vừa';
        } elseif ($employeeCount < 50) {
            return 'Phòng ban lớn';
        } else {
            return 'Phòng ban rất lớn';
        }
    }
}

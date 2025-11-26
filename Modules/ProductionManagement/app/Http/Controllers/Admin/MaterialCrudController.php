<?php

namespace Modules\ProductionManagement\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\ProductionManagement\Models\Material;
use App\Helpers\PermissionHelper;

class MaterialCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Material::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material');
        CRUD::setEntityNameStrings('vật tư', 'vật tư');

        CRUD::orderBy('code', 'ASC');

        // Setup buttons based on permissions
        $this->setupButtonsBasedOnPermissions();
    }

    /**
     * Setup buttons based on permissions
     */
    private function setupButtonsBasedOnPermissions()
    {
        $user = backpack_user();

        if (!PermissionHelper::can($user, 'material.create')) {
            CRUD::denyAccess('create');
        }

        if (!PermissionHelper::can($user, 'material.edit')) {
            CRUD::denyAccess('update');
        }

        if (!PermissionHelper::can($user, 'material.delete')) {
            CRUD::denyAccess('delete');
        }
    }

    protected function setupListOperation()
    {
        CRUD::column('code')->label('Mã vật tư');
        CRUD::column('name')->label('Tên vật tư');
        CRUD::column('unit')->label('Đơn vị');
        CRUD::column('status')->label('Trạng thái')
            ->type('select_from_array')
            ->options(['active' => 'Hoạt động', 'inactive' => 'Ngừng hoạt động']);
        CRUD::column('can_import')->label('Nhập')
            ->type('boolean')
            ->options([1 => 'Cho phép', 0 => 'Dừng nhập']);
        CRUD::column('can_export')->label('Xuất')
            ->type('boolean')
            ->options([1 => 'Cho phép', 0 => 'Cấm xuất']);
        CRUD::addColumn([
            'name' => 'min_stock_level',
            'label' => 'Tồn tối thiểu',
            'type' => 'number',
            'decimals' => 2,
            'value' => function($entry) {
                return $entry->min_stock_level !== null ? (float)$entry->min_stock_level : 0;
            }
        ]);
        CRUD::addColumn([
            'name' => 'max_stock_level',
            'label' => 'Tồn tối đa',
            'type' => 'text',
            'value' => function($entry) {
                if ($entry->max_stock_level === null || $entry->max_stock_level === '') {
                    return '-';
                }
                return number_format((float)$entry->max_stock_level, 2, '.', ',');
            }
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::field('code')->label('Mã vật tư')->type('text')->attributes(['required' => true]);
        CRUD::field('name')->label('Tên vật tư')->type('text')->attributes(['required' => true]);
        CRUD::field('unit')->label('Đơn vị tính')->type('text')->attributes(['required' => true]);
        CRUD::field('description')->label('Mô tả')->type('textarea');
        CRUD::field('min_stock_level')->label('Mức tồn kho tối thiểu')->type('number')->attributes(['step' => '0.01', 'min' => '0']);
        CRUD::field('max_stock_level')->label('Mức tồn kho tối đa')->type('number')->attributes(['step' => '0.01', 'min' => '0']);
        CRUD::field('status')->label('Trạng thái')
            ->type('select_from_array')
            ->options(['active' => 'Hoạt động', 'inactive' => 'Ngừng hoạt động'])
            ->default('active');
        CRUD::field('can_import')->label('Cho phép nhập')->type('boolean')->default(true);
        CRUD::field('can_export')->label('Cho phép xuất')->type('boolean')->default(true);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Search materials via AJAX (for Tom Select)
     */
    public function searchAjax()
    {
        $searchTerm = request()->get('q', '');
        $page = request()->get('page', 1);
        $perPage = 20;

        $query = Material::query();

        // Search by code or name
        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('code', 'like', '%' . $searchTerm . '%')
                  ->orWhere('name', 'like', '%' . $searchTerm . '%');
            });
        }

        // Only active materials
        $query->where('status', 'active');

        // Order by code
        $query->orderBy('code', 'ASC');

        $materials = $query->paginate($perPage, ['*'], 'page', $page);

        // Format response for Tom Select
        $formattedData = $materials->map(function($material) {
            return [
                'id' => $material->id,
                'text' => $material->code . ' - ' . $material->name,
                'code' => $material->code,
                'name' => $material->name,
                'unit' => $material->unit,
            ];
        });

        return response()->json([
            'results' => $formattedData,
            'pagination' => [
                'more' => $materials->hasMorePages(),
            ],
        ]);
    }
}

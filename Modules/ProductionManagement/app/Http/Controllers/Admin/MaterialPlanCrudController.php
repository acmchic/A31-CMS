<?php

namespace Modules\ProductionManagement\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\ProductionManagement\Models\MaterialPlan;
use Modules\ProductionManagement\Models\Material;
use Modules\OrganizationStructure\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class MaterialPlanCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(MaterialPlan::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/material-plan');
        CRUD::setEntityNameStrings('phương án vật tư', 'phương án vật tư');
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('ten_khi_tai')->label('Tên khí tài');
        CRUD::column('ky_hieu_khi_tai')->label('Ký hiệu');
        CRUD::column('so_hieu')->label('Số hiệu');
        CRUD::column('trang_thai')->label('Trạng thái')
            ->type('closure')
            ->function(function($entry) {
                return $entry->trang_thai_display;
            });
        CRUD::column('nguoiLap')->label('Người lập')
            ->type('relationship')
            ->attribute('name');
        CRUD::column('created_at')->label('Ngày tạo');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'ten_khi_tai' => 'required|string|max:255',
        ], [
            'ten_khi_tai.required' => 'Vui lòng nhập tên khí tài.',
            'ten_khi_tai.string' => 'Tên khí tài phải là chuỗi ký tự.',
            'ten_khi_tai.max' => 'Tên khí tài không được vượt quá 255 ký tự.',
        ]);

        // Cột trái: Tên khí tài, Ký hiệu, Đơn vị, Số hiệu
        CRUD::field('ten_khi_tai')->label('Tên khí tài')->type('text')
            ->attributes(['required' => true])
            ->wrapper(['class' => 'form-group col-md-6 material-plan-inline-field']);
        CRUD::field('ky_hieu_khi_tai')->label('Ký hiệu')->type('text')
            ->wrapper(['class' => 'form-group col-md-6 material-plan-inline-field']);
        CRUD::field('don_vi_co_khi_tai')->label('Đơn vị')->type('text')
            ->wrapper(['class' => 'form-group col-md-6 material-plan-inline-field']);
        CRUD::field('so_hieu')->label('Số hiệu')->type('text')
            ->wrapper(['class' => 'form-group col-md-6 material-plan-inline-field']);

        CRUD::field('muc_sua_chua')->label('Mức sửa chữa')->type('text')
            ->wrapper(['class' => 'form-group col-md-6 material-plan-inline-field']);

        $departments = Department::where(function($q) {
            // Lọc Phân xưởng 1-9 (có thể là "Phân xưởng 1", "Phan xuong 1", "PX1", "px1", etc.)
            for ($i = 1; $i <= 9; $i++) {
                $q->orWhere('name', 'like', "Phân xưởng {$i}%")
                  ->orWhere('name', 'like', "Phan xuong {$i}%")
                  ->orWhere('name', 'like', "PX{$i}%")
                  ->orWhere('name', 'like', "px{$i}%");
            }
        })->orderBy('name')->pluck('name', 'id')->toArray();

        CRUD::field('don_vi_sua_chua')->label('Đơn vị sửa chữa')
            ->type('select_from_array')
            ->options($departments)
            ->allows_multiple(true)
            ->attributes([
                'class' => 'form-control select2-multiple',
                'id' => 'don_vi_sua_chua_select',
            ])
            ->wrapper(['class' => 'form-group col-md-6 material-plan-inline-field']);
        CRUD::field('ngay_vao_sua_chua')->label('Ngày vào sửa chữa')
            ->type('view')
            ->view('productionmanagement::fields.date_picker')
            ->wrapper(['class' => 'form-group col-md-6 material-plan-inline-field']);
        CRUD::field('du_kien_thoi_gian_sua_chua')->label('Dự kiến thời gian sửa chữa')->type('text')
            ->wrapper(['class' => 'form-group col-md-6 material-plan-inline-field']);

        // Trạng thái
        CRUD::field('trang_thai')->label('Trạng thái')
            ->type('select_from_array')
            ->options([
                'nhap' => 'Nhập',
                'cho_phe_duyet' => 'Chờ phê duyệt',
            ])
            ->default('nhap');

        // Table editor cho vật tư - Custom field
        CRUD::field('items')->label('Danh sách vật tư')
            ->type('view')
            ->view('productionmanagement::fields.material_table');

        // Approvers selection card
        CRUD::field('selected_approvers_card')->label('Người phê duyệt')
            ->type('view')
            ->view('productionmanagement::fields.approvers_card');

        // Add Select2 initialization script for don_vi_sua_chua
        CRUD::field('don_vi_sua_chua')->after('du_kien_thoi_gian_sua_chua');

        // Set người lập
        $this->crud->getRequest()->merge(['nguoi_lap_id' => backpack_user()->id]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function store()
    {
        $this->crud->hasAccessOrFail('create');

        // Validate
        $request = $this->crud->validateRequest();

        // Validate items field
        $itemsJson = $request->input('items', '[]');
        if (is_string($itemsJson)) {
            $items = json_decode($itemsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Alert::error('Dữ liệu vật tư không hợp lệ. Vui lòng thử lại.')->flash();
                return back()->withInput();
            }
        } else {
            $items = is_array($itemsJson) ? $itemsJson : [];
        }

        // Validate required fields
        $request->validate([
            'ten_khi_tai' => 'required|string|max:255',
        ], [
            'ten_khi_tai.required' => 'Vui lòng nhập tên khí tài.',
            'ten_khi_tai.string' => 'Tên khí tài phải là chuỗi ký tự.',
            'ten_khi_tai.max' => 'Tên khí tài không được vượt quá 255 ký tự.',
        ], [
            'ten_khi_tai' => 'Tên khí tài',
        ]);

        // Validate items có ít nhất 1 item
        if (empty($items) || count($items) === 0) {
            \Alert::error('Vui lòng thêm ít nhất một vật tư.')->flash();
            return back()->withInput();
        }

        // Remove items from request before creating (it's a custom field, not in material_plans table)
        $requestData = $request->except(['items', 'selected_approvers_card']);

        // Process selected_approvers - convert JSON string to array if needed
        if ($request->has('selected_approvers')) {
            $selectedApprovers = $request->input('selected_approvers');
            if (is_string($selectedApprovers)) {
                $selectedApprovers = json_decode($selectedApprovers, true);
            }
            $requestData['selected_approvers'] = is_array($selectedApprovers) ? $selectedApprovers : [];
            
            // Nếu có selected_approvers, set workflow_status để xuất hiện trong approval center
            // MaterialPlan workflow: pending -> approved_by_department_head -> approved_by_reviewer (BGD) -> approved
            if (!empty($requestData['selected_approvers'])) {
                // Nếu có selected_approvers, có nghĩa là đã được thẩm định và chờ BGD phê duyệt
                // Nhưng khi tạo mới, thường sẽ là pending, sau đó mới chuyển sang approved_by_department_head
                // Tạm thời giữ nguyên pending, sẽ được chuyển khi submit để phê duyệt
            }
        }
        
        // Set nguoi_lap_id nếu chưa có
        if (!isset($requestData['nguoi_lap_id'])) {
            $requestData['nguoi_lap_id'] = backpack_user()->id;
        }
        
        // Set workflow_status mặc định nếu chưa có
        if (!isset($requestData['workflow_status'])) {
            $requestData['workflow_status'] = 'pending';
        }

        // Create material plan
        $entry = $this->crud->create($requestData);
        
        // Sync với ApprovalRequest
        if (class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
            $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
            $service->syncFromModel($entry, 'material_plan', [
                'title' => "Phương án vật tư: {$entry->ten_khi_tai}" . 
                           ($entry->ky_hieu_khi_tai ? " ({$entry->ky_hieu_khi_tai})" : ''),
            ]);
        }

        // Save items
        foreach ($items as $index => $item) {
            if (!empty($item['material_id'])) {
                $entry->items()->create([
                    'material_id' => $item['material_id'],
                    'so_thu_tu' => $item['so_thu_tu'] ?? ($index + 1),
                    'so_luong' => isset($item['so_luong']) && $item['so_luong'] !== '' ? (int)$item['so_luong'] : 0,
                    'doi_cu' => isset($item['doi_cu']) && $item['doi_cu'] !== '' ? (int)$item['doi_cu'] : 0,
                    'cap_moi' => isset($item['cap_moi']) && $item['cap_moi'] !== '' ? (int)$item['cap_moi'] : 0,
                    'ghi_chu' => $item['ghi_chu'] ?? '',
                ]);
            }
        }

        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        return \Redirect::to($this->crud->route);
    }

    protected function update()
    {
        $this->crud->hasAccessOrFail('update');

        // Validate
        $request = $this->crud->validateRequest();

        // Validate items field
        $itemsJson = $request->input('items', '[]');
        if (is_string($itemsJson)) {
            $items = json_decode($itemsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Alert::error('Dữ liệu vật tư không hợp lệ. Vui lòng thử lại.')->flash();
                return back()->withInput();
            }
        } else {
            $items = is_array($itemsJson) ? $itemsJson : [];
        }

        // Validate required fields
        $request->validate([
            'ten_khi_tai' => 'required|string|max:255',
        ], [
            'ten_khi_tai.required' => 'Vui lòng nhập tên khí tài.',
            'ten_khi_tai.string' => 'Tên khí tài phải là chuỗi ký tự.',
            'ten_khi_tai.max' => 'Tên khí tài không được vượt quá 255 ký tự.',
        ], [
            'ten_khi_tai' => 'Tên khí tài',
        ]);

        // Validate items có ít nhất 1 item
        if (empty($items) || count($items) === 0) {
            \Alert::error('Vui lòng thêm ít nhất một vật tư.')->flash();
            return back()->withInput();
        }

        // Remove items from request before updating (it's a custom field, not in material_plans table)
        $requestData = $request->except(['items', 'selected_approvers_card']);

        // Process selected_approvers - convert JSON string to array if needed
        if ($request->has('selected_approvers')) {
            $selectedApprovers = $request->input('selected_approvers');
            if (is_string($selectedApprovers)) {
                $selectedApprovers = json_decode($selectedApprovers, true);
            }
            $requestData['selected_approvers'] = is_array($selectedApprovers) ? $selectedApprovers : [];
        }

        // Update material plan
        $entry = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $requestData
        );
        
        // Sync với ApprovalRequest
        if (class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
            $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
            $service->syncFromModel($entry, 'material_plan', [
                'title' => "Phương án vật tư: {$entry->ten_khi_tai}" . 
                           ($entry->ky_hieu_khi_tai ? " ({$entry->ky_hieu_khi_tai})" : ''),
            ]);
        }

        // Delete existing items
        $entry->items()->delete();

        // Save new items
        foreach ($items as $index => $item) {
            if (!empty($item['material_id'])) {
                $entry->items()->create([
                    'material_id' => $item['material_id'],
                    'so_thu_tu' => $item['so_thu_tu'] ?? ($index + 1),
                    'so_luong' => isset($item['so_luong']) && $item['so_luong'] !== '' ? (int)$item['so_luong'] : 0,
                    'doi_cu' => isset($item['doi_cu']) && $item['doi_cu'] !== '' ? (int)$item['doi_cu'] : 0,
                    'cap_moi' => isset($item['cap_moi']) && $item['cap_moi'] !== '' ? (int)$item['cap_moi'] : 0,
                    'ghi_chu' => $item['ghi_chu'] ?? '',
                ]);
            }
        }

        \Alert::success(trans('backpack::crud.update_success'))->flash();

        return \Redirect::to($this->crud->route);
    }

    /**
     * Fetch Material for select2 (AJAX endpoint)
     */
    public function fetchMaterial(Request $request)
    {
        $query = $request->get('q', '');
        $id = $request->get('id');

        // If specific ID requested, return that material
        if ($id) {
            $material = Material::find($id);
            if ($material) {
                // Chỉ hiển thị tên vật tư, quy cách, ký hiệu - không có đơn vị
                $text = $material->ten_vat_tu;
                if ($material->quy_cach) {
                    $text .= ' - ' . $material->quy_cach;
                }
                if ($material->ky_hieu) {
                    $text .= ' - ' . $material->ky_hieu;
                }

                return response()->json([
                    'results' => [[
                        'id' => $material->id,
                        'text' => $text,
                        'ten_vat_tu' => $material->ten_vat_tu,
                        'quy_cach' => $material->quy_cach,
                        'ky_hieu' => $material->ky_hieu,
                        'don_vi_tinh' => $material->don_vi_tinh,
                    ]]
                ]);
            }
            return response()->json(['results' => []]);
        }

        // Search materials
        $materials = Material::where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('ten_vat_tu', 'like', "%{$query}%")
                  ->orWhere('quy_cach', 'like', "%{$query}%")
                  ->orWhere('ky_hieu', 'like', "%{$query}%")
                  ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();

        $results = $materials->map(function($material) {
            // Chỉ hiển thị tên vật tư, quy cách, ký hiệu - không có đơn vị
            $text = $material->ten_vat_tu;
            if ($material->quy_cach) {
                $text .= ' - ' . $material->quy_cach;
            }
            if ($material->ky_hieu) {
                $text .= ' - ' . $material->ky_hieu;
            }

            return [
                'id' => $material->id,
                'text' => $text,
                'ten_vat_tu' => $material->ten_vat_tu,
                'quy_cach' => $material->quy_cach,
                'ky_hieu' => $material->ky_hieu,
                'don_vi_tinh' => $material->don_vi_tinh,
            ];
        });

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => false]
        ]);
    }

    /**
     * Get list of approvers (BGD and Trưởng phòng)
     */
    public function getApprovers(Request $request)
    {
        // Get Ban Giám Đốc users
        $bgdUsers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc']);
        })->get();

        // Get Trưởng Phòng users
        $truongPhongUsers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng']);
        })->get();

        $bgd = $bgdUsers->map(function($user) {
            $employee = $user->getCorrectEmployee();
            $position = $employee && $employee->position ? $employee->position->name : null;
            
            return [
                'id' => $user->id,
                'name' => $user->display_name ?? $user->name,
                'username' => $user->username,
                'avatar' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
                'position' => $position,
                'level' => 'bgd',
            ];
        });

        $truongPhong = $truongPhongUsers->map(function($user) {
            $employee = $user->getCorrectEmployee();
            $position = $employee && $employee->position ? $employee->position->name : null;
            
            return [
                'id' => $user->id,
                'name' => $user->display_name ?? $user->name,
                'username' => $user->username,
                'avatar' => $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : null,
                'position' => $position,
                'level' => 'truong_phong',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'bgd' => $bgd,
                'truong_phong' => $truongPhong,
            ]
        ]);
    }
}

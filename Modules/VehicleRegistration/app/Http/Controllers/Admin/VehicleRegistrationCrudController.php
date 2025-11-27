<?php

namespace Modules\VehicleRegistration\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\VehicleRegistration\Models\VehicleRegistration;
use App\Helpers\PermissionHelper;
use App\Helpers\DateHelper;
use Illuminate\Http\Request;
use App\Services\VehicleRegistrationPdfService;
use Illuminate\Support\Facades\Storage;

class VehicleRegistrationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(VehicleRegistration::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/vehicle-registration');
        CRUD::setEntityNameStrings('đăng ký xe', 'đăng ký xe');

        CRUD::orderBy('id', 'DESC');

        // Apply data filtering based on user scope
        $this->applyDataFilter();

        // Setup buttons based on permissions
        $this->setupButtonsBasedOnPermissions();
    }

    /**
     * Apply data filtering using clean permission approach
     */
    private function applyDataFilter()
    {
        $user = backpack_user();
        
        // Check if user has permission to view all vehicle data
        if (PermissionHelper::can($user, 'vehicle_registration.view.all')) {
            // No filtering - see all registrations
            return;
        }
        
        $scope = PermissionHelper::getUserScope($user);

        switch ($scope) {
            case 'all':
            case 'company':
                // No filtering - see all registrations
                break;

            case 'department':
                // See department's registrations
                CRUD::addClause('whereHas', 'user', function($query) use ($user) {
                    $query->where('department_id', $user->department_id);
                });
                break;

            case 'own':
                // See only own registrations
                CRUD::addClause('where', 'user_id', $user->id);
                break;
        }
    }

    /**
     * Setup buttons based on permissions - clean approach
     */
    private function setupButtonsBasedOnPermissions()
    {
        $user = backpack_user();

        // CRUD buttons - Deny access to operations if no permission
        if (!PermissionHelper::can($user, 'vehicle_registration.create')) {
            CRUD::denyAccess('create');
        }

        if (!PermissionHelper::can($user, 'vehicle_registration.edit')) {
            CRUD::denyAccess('update');
        }

        if (!PermissionHelper::can($user, 'vehicle_registration.delete')) {
            CRUD::denyAccess('delete');
        }

        // ✅ Workflow Buttons using ApprovalWorkflow module

        // Step 1: Đội trưởng xe phân công (specific to VehicleRegistration)
        if (PermissionHelper::can($user, 'vehicle_registration.assign')) {
            CRUD::addButtonFromModelFunction('line', 'assign_vehicle', 'assignVehicleButton', 'beginning');
        }

        // Step 2: Ban Giám Đốc phê duyệt (using ApprovalWorkflow buttons)
        if (PermissionHelper::can($user, 'vehicle_registration.approve')) {
            CRUD::addButtonFromModelFunction('line', 'approve', 'approveButton', 'beginning');
            CRUD::addButtonFromModelFunction('line', 'reject', 'rejectButton', 'beginning');
        }

        // Download PDF (using ApprovalWorkflow button)
        if (PermissionHelper::can($user, 'vehicle_registration.view')) {
            CRUD::addButtonFromModelFunction('line', 'download_pdf', 'downloadPdfButton', 'beginning');
        }
    }

    protected function setupListOperation()
    {
        CRUD::column('id')->label('ID');
        CRUD::column('user_id')->label('Người đăng ký')->type('select')->entity('user')->attribute('name');

        // Gộp Ngày đi và Ngày về thành 1 column hiển thị 2 dòng
        CRUD::column('date_range')
            ->label('Ngày đi / Ngày về')
            ->type('closure')
            ->escaped(false) // Cho phép render HTML
            ->function(function($entry) {
                $departure = DateHelper::formatDate($entry->departure_datetime);
                $return = DateHelper::formatDate($entry->return_datetime);
                return '<div style="line-height: 1.6;">
                    <div><strong>Đi:</strong> ' . $departure . '</div>
                    <div><strong>Về:</strong> ' . $return . '</div>
                </div>';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                // Cho phép search theo ngày đi và ngày về
                $query->orWhere('departure_datetime', 'like', '%'.$searchTerm.'%')
                      ->orWhere('return_datetime', 'like', '%'.$searchTerm.'%');
            });

        CRUD::column('route')->label('Tuyến đường')->limit(50);
        CRUD::column('purpose')->label('Mục đích')->limit(50);
        CRUD::column('passenger_count')->label('Số người');

        // Show vehicle and driver info if assigned
        CRUD::column('vehicle_id')
            ->label('Xe được giao')
            ->type('select')
            ->entity('vehicle')
            ->attribute('full_name')
            ->default('--')
            ->searchLogic(function ($query, $column, $searchTerm) {
                // Search trong columns thực (name, license_plate) thay vì accessor (full_name)
                $query->orWhereHas('vehicle', function($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%')
                      ->orWhere('license_plate', 'like', '%'.$searchTerm.'%');
                });
            });
        CRUD::column('driver_name')->label('Lái xe')->default('--');

        // Chỉ giữ lại Quy trình, bỏ Trạng thái và Ngày tạo
        CRUD::column('workflow_status_display')->label('Quy trình');

        // Add modal to the view - inject via JavaScript
        CRUD::addClause('whereRaw', '1=1'); // Dummy clause to ensure setup runs

        // Set custom list view để load CSS/JS riêng cho module
        CRUD::setListView('vehicleregistration::list');
    }

    protected function setupCreateOperation()
    {
        $this->addBasicFields();
    }

    protected function setupUpdateOperation()
    {
        // Keep it simple - same fields as create
        $this->addBasicFields();
    }

    /**
     * Override store to sync with ApprovalRequest
     */
    public function store()
    {
        $response = $this->traitStore();

        // Sync với ApprovalRequest sau khi create
        if ($this->crud->entry && class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
            $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
            $entry = $this->crud->entry;
            $title = $entry->vehicle 
                ? "Đăng ký xe - {$entry->vehicle->name}" 
                : "Đăng ký xe #{$entry->id}";
            $service->syncFromModel($entry, 'vehicle', [
                'title' => $title,
            ]);
        }

        return $response;
    }

    /**
     * Override update to sync with ApprovalRequest
     */
    public function update()
    {
        $response = $this->traitUpdate();

        // Sync với ApprovalRequest sau khi update
        if ($this->crud->entry && class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
            $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
            $entry = $this->crud->entry;
            $title = $entry->vehicle 
                ? "Đăng ký xe - {$entry->vehicle->name}" 
                : "Đăng ký xe #{$entry->id}";
            $service->syncFromModel($entry, 'vehicle', [
                'title' => $title,
            ]);
        }

        return $response;
    }

    protected function setupShowOperation()
    {
        // Use custom view for show operation
        CRUD::setShowView('vehicleregistration::show');
    }

    private function addBasicFields()
    {
        // Departure and Return date on the same row (no time)
        CRUD::field([
            'name' => 'departure_datetime',
            'label' => 'Ngày đi',
            'type' => 'date',
            'allows_null' => false,
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'required' => true
            ]
        ]);

        CRUD::field([
            'name' => 'return_datetime',
            'label' => 'Ngày về',
            'type' => 'date',
            'allows_null' => false,
            'wrapper' => [
                'class' => 'form-group col-md-6'
            ],
            'attributes' => [
                'required' => true
            ]
        ]);

        CRUD::field('route')->label('Tuyến đường')->type('textarea')->attributes(['required' => true]);
        CRUD::field('purpose')->label('Mục đích sử dụng')->type('textarea')->attributes(['required' => true]);
        CRUD::field('passenger_count')->label('Số lượng người')->type('number')->default(1);
        CRUD::field('cargo_description')->label('Mô tả hàng hóa')->type('textarea');

        // Hidden field for user_id
        CRUD::field([
            'name' => 'user_id',
            'type' => 'hidden',
            'value' => backpack_user()->id
        ]);
    }

    private function addAssignmentFields()
    {
        $this->addBasicFields();

        // Vehicle assignment fields (only for Đội trưởng đội xe)
        CRUD::addField([
            'name' => 'vehicle_id',
            'label' => 'Phân xe',
            'type' => 'select',
            'entity' => 'vehicle',
            'model' => \Modules\VehicleRegistration\Models\Vehicle::class,
            'attribute' => 'name',
        ]);

        CRUD::addField([
            'name' => 'driver_id',
            'label' => 'Phân lái xe',
            'type' => 'select',
            'entity' => 'driver',
            'model' => \Modules\OrganizationStructure\Models\Employee::class,
            'attribute' => 'name',
        ]);

        // Alternative: manual driver entry
        CRUD::field('driver_name')->label('Tên lái xe (thủ công)')->type('text');
        CRUD::field('driver_license')->label('Số bằng lái')->type('text');
    }

    /**
     * ❌ REMOVED: addApprovalFields()
     * ✅ Approval fields are now managed in approval_requests table
     * Use ApprovalController for approve/reject actions
     */

    /**
     * TODO: Show vehicle assignment form (for Đội trưởng đội xe)
     * Temporarily disabled for basic CRUD testing
     */
    public function showAssignForm($id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        // Permission is already checked by route middleware

        // Check if can be assigned - check from approval_requests
        $approvalRequest = $registration->approvalRequest;
        if (!$approvalRequest || $approvalRequest->status !== 'submitted') {
            abort(403, 'Đăng ký này không thể phân xe.');
        }

        // Extract dates from datetime fields for compatibility
        $departureDate = $registration->departure_datetime ?
            \Carbon\Carbon::parse($registration->departure_datetime)->toDateString() :
            ($registration->departure_date ? $registration->departure_date->toDateString() : null);
        $returnDate = $registration->return_datetime ?
            \Carbon\Carbon::parse($registration->return_datetime)->toDateString() :
            ($registration->return_date ? $registration->return_date->toDateString() : null);

        if (!$departureDate || !$returnDate) {
            abort(400, 'Đăng ký này chưa có ngày đi/ngày về.');
        }

        // Get all vehicles and drivers, then filter by availability
        $allVehicles = \Modules\VehicleRegistration\Models\Vehicle::available()->get();
        $allDrivers = \Modules\OrganizationStructure\Models\Employee::where('position_id', 19) // Lái xe position
            ->active()
            ->with(['department', 'position'])
            ->get();

        // Get available vehicles and drivers for the dates (excluding current registration)
        $availableVehicles = $this->getAvailableVehicles($departureDate, $returnDate, $registration->id);
        $availableDrivers = $this->getAvailableDrivers($departureDate, $returnDate, $registration->id);

        // Get unavailable IDs for display in view (to disable them)
        $unavailableVehicleIds = $allVehicles->pluck('id')->diff($availableVehicles->pluck('id'))->toArray();
        $unavailableDriverIds = $allDrivers->pluck('id')->diff($availableDrivers->pluck('id'))->toArray();

        return view('vehicleregistration::assign', compact(
            'registration', 
            'availableVehicles', 
            'availableDrivers',
            'allVehicles',
            'allDrivers',
            'unavailableVehicleIds',
            'unavailableDriverIds'
        ));
    }

    /**
     * Process vehicle assignment
     */
    public function processAssignment(Request $request, $id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        // Check permission with debug
        $user = backpack_user();
        \Log::info('Assignment attempt:', [
            'user' => $user->name,
            'user_id' => $user->id,
            'role' => $user->roles->pluck('name')->toArray(),
            'has_assign_permission' => $user->hasPermissionTo('vehicle_registration.assign'),
            'request_data' => $request->all()
        ]);

        // Permission is already checked by route middleware

        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:employees,id',
        ]);

        // Extract dates from registration
        $departureDate = $registration->departure_datetime ?
            \Carbon\Carbon::parse($registration->departure_datetime)->toDateString() :
            ($registration->departure_date ? $registration->departure_date->toDateString() : null);
        $returnDate = $registration->return_datetime ?
            \Carbon\Carbon::parse($registration->return_datetime)->toDateString() :
            ($registration->return_date ? $registration->return_date->toDateString() : null);

        if (!$departureDate || !$returnDate) {
            return redirect()->back()->withErrors(['error' => 'Đăng ký này chưa có ngày đi/ngày về.']);
        }

        // Check if vehicle is available
        $availableVehicles = $this->getAvailableVehicles($departureDate, $returnDate, $registration->id);
        if (!$availableVehicles->contains('id', $request->vehicle_id)) {
            return redirect()->back()->withErrors(['vehicle_id' => 'Xe này đã được phân công cho đơn khác trong khoảng thời gian này.']);
        }

        // Check if driver is available
        $availableDrivers = $this->getAvailableDrivers($departureDate, $returnDate, $registration->id);
        if (!$availableDrivers->contains('id', $request->driver_id)) {
            return redirect()->back()->withErrors(['driver_id' => 'Lái xe này đã được phân công cho đơn khác trong khoảng thời gian này.']);
        }

        // Get driver info
        $driver = \Modules\OrganizationStructure\Models\Employee::find($request->driver_id);

        // Update registration - chỉ update business fields
        $registration->update([
            'vehicle_id' => $request->vehicle_id,
            'driver_id' => $request->driver_id,
            'driver_name' => $driver ? $driver->name : null,
            'updated_by' => backpack_user()->name
        ]);

        // Sync với ApprovalRequest sau khi assign vehicle
        // ApprovalRequestService sẽ tự động update status và current_step
        if (class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
            $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
            $service->syncFromModel($registration, 'vehicle');
            
            // Update approval_requests để chuyển sang step tiếp theo (department_head_approval)
            $approvalRequest = $registration->approvalRequest;
            if ($approvalRequest) {
                $approvalRequest->update([
                    'current_step' => 'department_head_approval',
                    'status' => 'in_review'
                ]);
            }
        }

        return redirect(backpack_url('vehicle-registration'))->with('success', 'Đã phân công xe thành công!');
    }

    /**
     * ❌ REMOVED: processApproveDepartment, processApproveDirector, processReject
     * ✅ These are now handled by ApprovalService via ApprovalController
     * Use /approval/approve/{modelClass}/{id} or /approval/reject/{modelClass}/{id}
     */

    /**
     * Approve with digital signature
     */
    public function approve($id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        if (!PermissionHelper::userCan('vehicle_registration.approve')) {
            abort(403, getUserTitle() . ' không có quyền phê duyệt.');
        }

        // Update approval info
        $registration->update([
            'status' => 'approved',
            'workflow_status' => 'approved',
            'director_approved_by' => backpack_user()->id,
            'director_approved_at' => now(),
        ]);

        // Generate PDF with digital signature
        try {
            $pdfPath = VehicleRegistrationPdfService::generateApprovalPdf($registration);

            \Log::info('PDF Generated for vehicle registration:', [
                'registration_id' => $registration->id,
                'pdf_path' => $pdfPath,
                'approver' => backpack_user()->name
            ]);

            return redirect(backpack_url('vehicle-registration'))->with('success', 'Đã phê duyệt và tạo PDF thành công! Chữ ký số đã được áp dụng.');

        } catch (\Exception $e) {
            \Log::error('PDF Generation Error:', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);

            return redirect(backpack_url('vehicle-registration'))->with('warning', 'Đã phê duyệt thành công nhưng có lỗi khi tạo PDF: ' . $e->getMessage());
        }
    }

    // ❌ REMOVED: reject() method
    // ✅ This is now handled by ApprovalWorkflow module's ApprovalController!

    /**
     * Download PDF with signature
     */
    public function downloadPdf($id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        if (!$registration->isApproved()) {
            abort(403, 'Chỉ có thể Tải về khi đã được phê duyệt.');
        }

        try {
            // Get signed_pdf_path from approval_requests
            $approvalRequest = $registration->approvalRequest;
            $signedPdfPath = $approvalRequest ? $approvalRequest->signed_pdf_path : null;
            
            // Check if PDF already exists
            if ($signedPdfPath && Storage::disk('public')->exists($signedPdfPath)) {
                return Storage::disk('public')->download($signedPdfPath, 'Dang_ky_xe_' . $registration->id . '.pdf');
            }

            // Generate PDF on-the-fly if not exists
            $pdfPath = VehicleRegistrationPdfService::generatePdf($registration, true);

            return Storage::disk('public')->download($pdfPath, 'Dang_ky_xe_' . $registration->id . '.pdf');

        } catch (\Exception $e) {
            \Log::error('PDF Download Error:', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Lỗi khi tạo PDF: ' . $e->getMessage());
        }
    }

    // ❌ REMOVED: approveWithPin() method
    // ✅ This is now handled by ApprovalWorkflow module's ApprovalController!

    /**
     * Check PDF signature validity
     */
    public function checkSignature($id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        // Get signed_pdf_path from approval_requests
        $approvalRequest = $registration->approvalRequest;
        $signedPdfPath = $approvalRequest ? $approvalRequest->signed_pdf_path : null;

        if (!$signedPdfPath) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy PDF đã ký'
            ]);
        }

        try {
            $validation = VehicleRegistrationPdfService::validatePdfSignature($signedPdfPath);

            return response()->json([
                'success' => true,
                'validation' => $validation,
                'message' => $validation['valid'] ? 'PDF có chữ ký hợp lệ' : 'PDF không có chữ ký số hoặc chữ ký không hợp lệ'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra chữ ký: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get available vehicles for date range
     * Vehicles are available if they're not already assigned to another registration
     * that overlaps with the given date range
     */
    private function getAvailableVehicles($startDate, $endDate, $excludeRegistrationId = null)
    {
        return \Modules\VehicleRegistration\Models\Vehicle::available()
            ->forDateRange($startDate, $endDate, $excludeRegistrationId)
            ->get();
    }

    /**
     * Get available drivers for date range
     * Drivers are available if they're not already assigned to another registration
     * that overlaps with the given date range
     */
    private function getAvailableDrivers($startDate, $endDate, $excludeRegistrationId = null)
    {
        return \Modules\OrganizationStructure\Models\Employee::where('position_id', 19) // Lái xe position
            ->active()
            ->forDateRange($startDate, $endDate, $excludeRegistrationId)
            ->with(['department', 'position'])
            ->get();
    }
}

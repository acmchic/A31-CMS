<?php

namespace Modules\VehicleRegistration\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\VehicleRegistration\Models\VehicleRegistration;
use App\Helpers\PermissionHelper;
use Illuminate\Http\Request;
use App\Services\VehicleRegistrationPdfService;
use Illuminate\Support\Facades\Storage;

class VehicleRegistrationCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
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
        CRUD::column('departure_datetime')->label('Ngày đi')->type('date');
        CRUD::column('return_datetime')->label('Ngày về')->type('date');
        CRUD::column('route')->label('Tuyến đường')->limit(50);
        CRUD::column('purpose')->label('Mục đích')->limit(50);
        CRUD::column('passenger_count')->label('Số người');

        // Show vehicle and driver info if assigned
        CRUD::column('vehicle_id')
            ->label('Xe được phân')
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

        CRUD::column('status_display')->label('Trạng thái');
        CRUD::column('workflow_status_display')->label('Quy trình');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime');

        // Add modal to the view - inject via JavaScript
        CRUD::addClause('whereRaw', '1=1'); // Dummy clause to ensure setup runs
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
        CRUD::field('vehicle_id')->label('Phân xe')->type('select2')->entity('vehicle')->attribute('name')
            ->options(function($query) {
                // Get available vehicles for the selected dates
                return $query->where('status', 'available')->get();
            });

        CRUD::field('driver_id')->label('Phân lái xe')->type('select2')->entity('driver')->attribute('name')
            ->options(function($query) {
                // Get available drivers for the selected dates
                return $query->where('status', 'available')->get();
            });

        // Alternative: manual driver entry
        CRUD::field('driver_name')->label('Tên lái xe (thủ công)')->type('text');
        CRUD::field('driver_license')->label('Số bằng lái')->type('text');
    }

    private function addApprovalFields()
    {
        $this->addAssignmentFields();

        // Approval fields
        CRUD::field('status')->label('Trạng thái')->type('select_from_array')
            ->options([
                'pending' => 'Chờ duyệt',
                'dept_approved' => 'Phòng ban đã duyệt',
                'approved' => 'Đã phê duyệt',
                'rejected' => 'Đã từ chối'
            ]);

        CRUD::field('workflow_status')->label('Tình trạng duyệt')->type('select_from_array')
            ->options([
                'submitted' => 'Đã gửi',
                'dept_review' => 'Phòng ban xem xét',
                'director_review' => 'Ban giám đốc xem xét',
                'approved' => 'Đã duyệt',
                'rejected' => 'Đã từ chối'
            ]);

        CRUD::field('rejection_reason')->label('Lý do từ chối')->type('textarea');
    }

    /**
     * TODO: Show vehicle assignment form (for Đội trưởng đội xe)
     * Temporarily disabled for basic CRUD testing
     */
    public function showAssignForm($id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        // Permission is already checked by route middleware

        // Check if can be assigned
        if ($registration->workflow_status !== 'submitted') {
            abort(403, 'Đăng ký này không thể phân xe.');
        }

        // Extract dates from datetime fields for compatibility
        $departureDate = $registration->departure_datetime ?
            \Carbon\Carbon::parse($registration->departure_datetime)->toDateString() :
            $registration->departure_date;
        $returnDate = $registration->return_datetime ?
            \Carbon\Carbon::parse($registration->return_datetime)->toDateString() :
            $registration->return_date;

        // Get available vehicles and drivers for the dates
        $availableVehicles = $this->getAvailableVehicles($departureDate, $returnDate);
        $availableDrivers = $this->getAvailableDrivers($departureDate, $returnDate);

        // Get available vehicles and drivers (employees with driver role)
        $availableVehicles = \Modules\VehicleRegistration\Models\Vehicle::available()
            ->forDateRange($departureDate, $returnDate)
            ->get();

        // Get drivers - employees with position_id = 19 (Lái xe) or fallback to some employees
        $availableDrivers = \Modules\OrganizationStructure\Models\Employee::where('position_id', 19) // Lái xe position
            ->orWhere(function($query) {
                // Fallback: get some employees for demo (first 10)
                $query->limit(10);
            })
            ->with(['department', 'position'])
            ->get();

        return view('vehicleregistration::assign', compact('registration', 'availableVehicles', 'availableDrivers'));
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

        // Get driver info
        $driver = \Modules\OrganizationStructure\Models\Employee::find($request->driver_id);

        // Update registration
        $registration->update([
            'vehicle_id' => $request->vehicle_id,
            'driver_id' => $request->driver_id,
            'driver_name' => $driver ? $driver->name : null,
            'workflow_status' => 'dept_review',
            'updated_by' => backpack_user()->name
        ]);

        return redirect(backpack_url('vehicle-registration'))->with('success', 'Đã phân công xe thành công!');
    }

    /**
     * Approve by department
     */
    public function processApproveDepartment(Request $request, $id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        if (!PermissionHelper::userCan('vehicle_registration.approve')) {
            abort(403, getUserTitle() . ' không có quyền phê duyệt.');
        }

        $registration->update([
            'workflow_status' => 'director_review',
            'department_approved_by' => backpack_user()->id,
            'department_approved_at' => now(),
            'digital_signature_dept' => backpack_user()->signature_path // If available
        ]);

        return redirect()->back()->with('success', 'Đã duyệt cấp phòng ban!');
    }

    /**
     * Approve by director (final approval)
     */
    public function processApproveDirector(Request $request, $id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        if (!PermissionHelper::userCan('vehicle_registration.approve')) {
            abort(403, getUserTitle() . ' không có quyền phê duyệt.');
        }

        $registration->update([
            'status' => 'approved',
            'workflow_status' => 'approved',
            'director_approved_by' => backpack_user()->id,
            'director_approved_at' => now(),
            'digital_signature_director' => backpack_user()->signature_path
        ]);

        // Generate PDF with digital signature here using lsnepomuceno/laravel-a1-pdf-sign
        // TODO: Implement PDF generation with signatures

        return redirect()->back()->with('success', 'Đã phê duyệt hoàn tất! PDF đang được tạo.');
    }

    /**
     * Reject registration
     */
    public function processReject(Request $request, $id)
    {
        $registration = VehicleRegistration::findOrFail($id);

        if (!PermissionHelper::userCan('vehicle_registration.approve')) {
            abort(403, getUserTitle() . ' không có quyền từ chối.');
        }

        $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $registration->update([
            'status' => 'rejected',
            'workflow_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejection_level' => $registration->workflow_status === 'submitted' ? 'department' : 'director'
        ]);

        return redirect()->back()->with('success', 'Đã từ chối đăng ký.');
    }

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
            // Check if PDF already exists
            if ($registration->signed_pdf_path && Storage::disk('public')->exists($registration->signed_pdf_path)) {
                return Storage::disk('public')->download($registration->signed_pdf_path, 'Dang_ky_xe_' . $registration->id . '.pdf');
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

        if (!$registration->signed_pdf_path) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy PDF đã ký'
            ]);
        }

        try {
            $validation = VehicleRegistrationPdfService::validatePdfSignature($registration->signed_pdf_path);

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
     */
    private function getAvailableVehicles($startDate, $endDate)
    {
        // TODO: Query vehicles table for available vehicles
        // For now return empty array
        return [];
    }

    /**
     * Get available drivers for date range
     */
    private function getAvailableDrivers($startDate, $endDate)
    {
        // TODO: Query drivers table for available drivers
        // For now return empty array
        return [];
    }
}

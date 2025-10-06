<?php

namespace Modules\PersonnelReport\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\OrganizationStructure\Models\Employee;
use Modules\OrganizationStructure\Models\Department;
use Modules\OrganizationStructure\Models\Leave;
use App\Helpers\PermissionHelper;

class LeaveRequestCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(EmployeeLeave::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/leave-request');
        CRUD::setEntityNameStrings('đơn xin nghỉ phép', 'đơn xin nghỉ phép');

        CRUD::orderBy('id', 'DESC');

        // ✅ Disable search persistence - không lưu giá trị search cũ
        CRUD::set('list.persistentTable', false);

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

        // ✅ Debug logging
        $employeeDeptId = null;
        if ($user->employee_id) {
            $emp = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
            $employeeDeptId = $emp ? $emp->department_id : null;
        }
        
        \Log::info('LeaveRequest Filter Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_roles' => $user->roles->pluck('name'),
            'scope' => $scope,
            'department_id' => $user->department_id,
            'employee_department_id' => $employeeDeptId,
        ]);

        switch ($scope) {
            case 'all':
            case 'company':
                // No filtering - can see all leave requests
                break;

            case 'department':
                // Can see department's leave requests
                $departmentId = $user->department_id;
                if (!$departmentId && $user->employee_id) {
                    $emp = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                    $departmentId = $emp ? $emp->department_id : null;
                }

                if ($departmentId) {
                    $employeeIds = Employee::where('department_id', $departmentId)->pluck('id');
                    \Log::info('Department Filter Applied', [
                        'department_id' => $departmentId,
                        'employee_ids' => $employeeIds->toArray(),
                    ]);
                    CRUD::addClause('whereIn', 'employee_id', $employeeIds);
                } else {
                    \Log::warning('No department_id found for user', ['user_id' => $user->id]);
                    CRUD::addClause('where', 'id', 0);
                }
                break;

            case 'own':
                // Can see only own leave requests
                if ($user->employee_id) {
                    CRUD::addClause('where', 'employee_id', $user->employee_id);
                } else {
                    CRUD::addClause('where', 'id', 0);
                }
                break;

            default:
                \Log::warning('Unknown scope, hiding all records', ['scope' => $scope, 'user_id' => $user->id]);
                CRUD::addClause('where', 'id', 0);
                break;
        }
    }

    /**
     * Setup buttons based on user permissions - clean approach
     * ✅ Using ApprovalWorkflow module buttons
     */
    private function setupButtonsForRole()
    {
        $user = backpack_user();

        // Remove buttons based on permissions
        if (!PermissionHelper::can($user, 'leave.create')) {
            CRUD::removeButton('create');
        }

        // ✅ Hide Edit, Delete buttons for approved leaves (done via model method)
        CRUD::removeButton('update');
        CRUD::removeButton('delete');
        CRUD::addButtonFromModelFunction('line', 'edit_conditional', 'editButtonConditional', 'beginning');
        CRUD::addButtonFromModelFunction('line', 'delete_conditional', 'deleteButtonConditional', 'beginning');

        // ✅ Add ApprovalWorkflow buttons - these come from ApprovalButtons trait in the model
        if (PermissionHelper::can($user, 'leave.approve')) {
            CRUD::addButtonFromModelFunction('line', 'approve', 'approveButton', 'beginning');
            CRUD::addButtonFromModelFunction('line', 'reject', 'rejectButton', 'beginning');
        }

        // Download PDF button for anyone who can view
        if (PermissionHelper::can($user, 'leave.view')) {
            CRUD::addButtonFromModelFunction('line', 'download_pdf', 'downloadPdfButton', 'beginning');
        }
    }

    protected function setupListOperation()
    {
        // ✅ Add widget to disable browser autocomplete on search input
        \Widget::add()->type('view')->view('personnelreport::widgets.disable-search-autocomplete');

        // Setup buttons based on user role
        $this->setupButtonsForRole();

        CRUD::column('employee_name')
            ->label('Nhân sự')
            ->type('closure')
            ->function(function($entry) {
                return $entry->employee ? $entry->employee->name : 'N/A';
            });

        CRUD::column('department_name')
            ->label('Phòng ban')
            ->type('closure')
            ->function(function($entry) {
                return $entry->employee && $entry->employee->department
                    ? $entry->employee->department->name
                    : 'N/A';
            });

        CRUD::column('leave_type')
            ->label('Loại nghỉ')
            ->type('closure')
            ->function(function($entry) {
                return $entry->leave_type_text;
            });

        CRUD::column('from_date')
            ->label('Từ ngày')
            ->type('date');

        CRUD::column('to_date')
            ->label('Đến ngày')
            ->type('date');

        CRUD::column('location')
            ->label('Địa điểm')
            ->type('text');

        CRUD::column('status')
            ->label('Trạng thái')
            ->type('closure')
            ->function(function($entry) {
                return $entry->status_text;
            });

        CRUD::column('workflow_status')
            ->label('Trạng thái quy trình')
            ->type('closure')
            ->function(function($entry) {
                return $entry->workflow_status_text;
            });

        CRUD::column('note')
            ->label('Ghi chú')
            ->type('text')
            ->limit(50);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|string|in:business,attendance,study,leave,other',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'location' => 'required|string|max:255',
            'note' => 'nullable|string|max:500'
        ], [
            'employee_id.required' => 'Vui lòng chọn Nhân sự',
            'employee_id.exists' => 'Nhân sự không tồn tại',
            'leave_type.required' => 'Vui lòng chọn loại nghỉ',
            'leave_type.in' => 'Loại nghỉ không hợp lệ',
            'from_date.required' => 'Vui lòng chọn ngày bắt đầu',
            'from_date.date' => 'Ngày bắt đầu không hợp lệ',
            'from_date.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi',
            'to_date.required' => 'Vui lòng chọn ngày kết thúc',
            'to_date.date' => 'Ngày kết thúc không hợp lệ',
            'to_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'location.required' => 'Vui lòng nhập địa điểm',
            'location.max' => 'Địa điểm không được quá 255 ký tự',
            'note.max' => 'Ghi chú không được quá 500 ký tự'
        ]);

        // Filter employees based on user's department
        $employeeOptions = [];
        $user = backpack_user();
        if ($user->hasRole('admin')) {
            $employeeOptions = Employee::with('department')->get()->mapWithKeys(function($emp) {
                return [$emp->id => $emp->name . ' (' . ($emp->department ? $emp->department->name : 'N/A') . ')'];
            });
        } else {
            // First try to get department from user's direct department_id
            $departmentId = $user->department_id;

            // Fallback to employee's department if user doesn't have direct department
            if (!$departmentId && $user->employee_id) {
                $emp = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                $departmentId = $emp ? $emp->department_id : null;
            }

            if ($departmentId) {
                $employeeOptions = Employee::where('department_id', $departmentId)
                    ->get()
                    ->mapWithKeys(function($emp) {
                        return [$emp->id => $emp->name];
                    });
            }
        }

        // Define leave types using constants
        $leaveOptions = [
            EmployeeLeave::TYPE_BUSINESS => 'Công tác',
            EmployeeLeave::TYPE_ATTENDANCE => 'Cơ động',
            EmployeeLeave::TYPE_STUDY => 'Đi học',
            EmployeeLeave::TYPE_LEAVE => 'Nghỉ phép',
            EmployeeLeave::TYPE_OTHER => 'Khác'
        ];

        CRUD::field('employee_id')
            ->label('Nhân sự')
            ->type('select_from_array')
            ->options($employeeOptions)
            ->tab('Thông tin cơ bản');

        CRUD::field('leave_type')
            ->label('Loại nghỉ')
            ->type('select_from_array')
            ->options($leaveOptions)
            ->tab('Thông tin cơ bản');

        CRUD::field('from_date')
            ->label('Từ ngày')
            ->type('date')
            ->tab('Thông tin cơ bản');

        CRUD::field('to_date')
            ->label('Đến ngày')
            ->type('date')
            ->tab('Thông tin cơ bản');

        CRUD::field('location')
            ->label('Địa điểm')
            ->type('text')
            ->tab('Thông tin cơ bản');

        CRUD::field('note')
            ->label('Ghi chú')
            ->type('textarea')
            ->tab('Thông tin cơ bản');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();

        // Add status field for approval/rejection
        CRUD::field('status')
            ->label('Trạng thái')
            ->type('select_from_array')
            ->options([
                EmployeeLeave::STATUS_PENDING => 'Chờ duyệt',
                EmployeeLeave::STATUS_APPROVED => 'Đã duyệt',
                EmployeeLeave::STATUS_REJECTED => 'Từ chối',
                EmployeeLeave::STATUS_CANCELLED => 'Đã hủy'
            ])
            ->tab('Thông tin cơ bản');

        CRUD::field('workflow_status')
            ->label('Trạng thái quy trình')
            ->type('select_from_array')
            ->options([
                EmployeeLeave::WORKFLOW_PENDING => 'Chờ xử lý',
                EmployeeLeave::WORKFLOW_IN_REVIEW => 'Đang xem xét',
                EmployeeLeave::WORKFLOW_APPROVED => 'Đã phê duyệt',
                EmployeeLeave::WORKFLOW_REJECTED => 'Đã từ chối'
            ])
            ->tab('Thông tin cơ bản');

        CRUD::field('rejection_reason')
            ->label('Lý do từ chối')
            ->type('textarea')
            ->tab('Thông tin cơ bản');

        CRUD::field('is_authorized')
            ->label('Đã ủy quyền')
            ->type('boolean')
            ->tab('Thông tin cơ bản');

        CRUD::field('is_checked')
            ->label('Đã kiểm tra')
            ->type('boolean')
            ->tab('Thông tin cơ bản');
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();

        CRUD::column('approved_by')
            ->label('Người duyệt')
            ->type('closure')
            ->function(function($entry) {
                return $entry->approver ? $entry->approver->name : 'N/A';
            });

        CRUD::column('approved_at')
            ->label('Ngày duyệt')
            ->type('datetime');

        CRUD::column('reviewer_id')
            ->label('Người xem xét')
            ->type('closure')
            ->function(function($entry) {
                return $entry->reviewer ? $entry->reviewer->name : 'N/A';
            });

        CRUD::column('reviewed_at')
            ->label('Ngày xem xét')
            ->type('datetime');

        CRUD::column('rejection_reason')
            ->label('Lý do từ chối');

        CRUD::column('is_authorized')
            ->label('Đã ủy quyền')
            ->type('boolean');

        CRUD::column('is_checked')
            ->label('Đã kiểm tra')
            ->type('boolean');

        CRUD::column('created_by')
            ->label('Người tạo');

        CRUD::column('updated_by')
            ->label('Người cập nhật');
    }

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $user = backpack_user();
        $request = $this->crud->getRequest();

        // Set additional fields
        $request->merge([
            'status' => EmployeeLeave::STATUS_PENDING,
            'workflow_status' => EmployeeLeave::WORKFLOW_PENDING,
            'created_by' => $user->name ?: $user->username,
            'updated_by' => $user->name ?: $user->username
        ]);

        $this->crud->setRequest($request);
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitStore();
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $user = backpack_user();
        $request = $this->crud->getRequest();

        // If status is being changed to approved, set approved_by and approved_at
        if ($request->input('status') == EmployeeLeave::STATUS_APPROVED) {
            $request->merge([
                'approved_by' => $user->id,
                'approved_at' => now(),
                'workflow_status' => EmployeeLeave::WORKFLOW_APPROVED
            ]);
        } elseif ($request->input('status') == EmployeeLeave::STATUS_REJECTED) {
            $request->merge(['workflow_status' => EmployeeLeave::WORKFLOW_REJECTED]);
        }

        $request->merge(['updated_by' => $user->name ?: $user->username]);

        $this->crud->setRequest($request);
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }

    // ❌ REMOVED: approve(), reject(), generateSignedPdf(), generatePdfContent()
    // ✅ These are now handled by ApprovalWorkflow module's ApprovalController!

    /**
     * Download signed PDF
     * ✅ Keep this method for backward compatibility with existing routes
     */
    public function downloadPdf($id)
    {
        $leaveRequest = EmployeeLeave::findOrFail($id);

        if (!$leaveRequest->signed_pdf_path) {
            abort(404, 'PDF chưa được tạo');
        }

        $filePath = storage_path('app/public/' . $leaveRequest->signed_pdf_path);

        if (!file_exists($filePath)) {
            abort(404, 'File PDF không tồn tại');
        }

        return response()->download($filePath, 'don_xin_nghi_phep_' . $leaveRequest->id . '.pdf');
    }
}

<?php

namespace Modules\PersonnelReport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Modules\OrganizationStructure\Models\Employee;
use Modules\ApprovalWorkflow\Traits\HasApprovalWorkflow;
use Modules\ApprovalWorkflow\Traits\HasDigitalSignature;
use Modules\ApprovalWorkflow\Traits\ApprovalButtons;

class EmployeeLeave extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;
    use HasApprovalWorkflow, HasDigitalSignature, ApprovalButtons;

    protected $table = 'employee_leave';

    // ✅ Configure ApprovalWorkflow
    protected $workflowType = 'two_level';
    protected $pdfView = 'personnelreport::pdf.leave-request';
    protected $pdfDirectory = 'leave_requests';

    protected $fillable = [
        'employee_id',
        'leave_type',
        'from_date',
        'to_date',
        'start_at',
        'end_at',
        'note',
        'location',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'digital_signature',
        'signed_pdf_path',
        'template_pdf_path',
        'signature_certificate',
        'workflow_status',
        'reviewer_id',
        'reviewed_at',
        'is_authorized',
        'is_checked',
        'approved_by_approver',
        'approved_at_approver',
        'approver_comment',
        'approver_signature_path',
        'approved_by_department_head',
        'approved_at_department_head',
        'approved_by_reviewer',
        'approved_at_reviewer',
        'approved_by_director',
        'approved_at_director',
        'director_comment',
        'director_signature_path',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = ['deleted_at', 'from_date', 'to_date', 'start_at', 'end_at', 'approved_at', 'reviewed_at', 'approved_at_department_head', 'approved_at_reviewer'];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at_department_head' => 'datetime',
        'approved_at_reviewer' => 'datetime',
        'is_authorized' => 'boolean',
        'is_checked' => 'boolean',
        'workflow_status' => 'string'
    ];

    // Leave status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    // Leave type constants
    const TYPE_BUSINESS = 'business';
    const TYPE_ATTENDANCE = 'attendance';
    const TYPE_STUDY = 'study';
    const TYPE_LEAVE = 'leave';
    const TYPE_OTHER = 'other';

    // Workflow status constants - 4 step workflow
    const WORKFLOW_PENDING = 'pending';                                    // Step 1: User tạo đơn
    const WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD = 'approved_by_department_head';  // Step 2: Trưởng phòng ký
    const WORKFLOW_APPROVED_BY_REVIEWER = 'approved_by_reviewer';         // Step 3: Thẩm định ký
    const WORKFLOW_APPROVED_BY_DIRECTOR = 'approved_by_director';         // Step 4: BGD ký (final)
    const WORKFLOW_REJECTED = 'rejected';

    // Legacy constants (for backward compatibility)
    const WORKFLOW_IN_REVIEW = 'in_review';
    const WORKFLOW_APPROVED_BY_APPROVER = 'approved_by_approver';

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leave()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Leave::class, 'leave_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewer_id');
    }

    public function approverUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_approver');
    }

    public function directorUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_director');
    }

    public function departmentHeadUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_department_head');
    }

    public function reviewerUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_reviewer');
    }

    // ✅ Map old column names to ApprovalWorkflow convention
    public function getWorkflowLevel1ByAttribute()
    {
        return $this->attributes['approved_by_approver'] ?? null;
    }

    public function getWorkflowLevel1AtAttribute()
    {
        return isset($this->attributes['approved_at_approver']) ? $this->attributes['approved_at_approver'] : null;
    }

    public function getWorkflowLevel2ByAttribute()
    {
        return $this->attributes['approved_by_director'] ?? null;
    }

    public function getWorkflowLevel2AtAttribute()
    {
        return isset($this->attributes['approved_at_director']) ? $this->attributes['approved_at_director'] : null;
    }

    // ✅ Override level1Approver relationship để dùng cột cũ
    public function level1Approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_approver');
    }

    // ✅ Override level2Approver relationship để dùng cột cũ
    public function level2Approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by_director');
    }

    // ✅ Override module permission
    protected function getModulePermission(): string
    {
        return 'leave';
    }

    // ✅ Custom PDF title
    public function getPdfTitle(): string
    {
        return 'Đơn xin nghỉ phép #' . $this->id;
    }

    // ✅ Custom PDF filename for download
    public function getPdfFilename(): string
    {
        return 'don_xin_nghi_phep_' . $this->id . '.pdf';
    }

    // ✅ Custom PDF filename pattern for saving
    public function getCustomPdfFilename(): string
    {
        return 'don_nghi_phep.pdf';
    }

    // ✅ Override PDF owner username - use employee's username instead of approver
    public function getCustomPdfOwnerUsername(): string
    {
        // Get employee's user account username
        if ($this->employee && $this->employee->user) {
            return $this->employee->user->username ?? 'user_' . $this->employee->user->id;
        }

        // If employee doesn't have user account, use employee ID
        if ($this->employee) {
            return 'employee_' . $this->employee->id;
        }

        // Fallback
        return 'unknown';
    }

    // ✅ Custom PDF data (override trait method)
    public function getPdfData(): array
    {
        // Get base data from trait
        $baseData = [
            'model' => $this,
            'approver' => $this->getCurrentLevelApprover(),
            'generated_at' => \Carbon\Carbon::now()->format('d/m/Y H:i:s'),
        ];

        // Add custom leave-specific data
        return array_merge($baseData, [
            'leave' => $this,
            'employee' => $this->employee,
            'department' => $this->employee ? $this->employee->department : null,
        ]);
    }

    public function getWorkflowStatusDisplayAttribute(): string
    {
        return $this->workflow_status_text;
    }

    // ✅ Override getNextWorkflowStep - 4 step workflow
    public function getNextWorkflowStep(): ?string
    {
        $currentStep = $this->getCurrentWorkflowStep();

        $workflowMap = [
            self::WORKFLOW_PENDING => self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD,              // Step 1 → Step 2
            self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD => self::WORKFLOW_APPROVED_BY_REVIEWER, // Step 2 → Step 3
            self::WORKFLOW_APPROVED_BY_REVIEWER => self::WORKFLOW_APPROVED_BY_DIRECTOR,         // Step 3 → Step 4
            self::WORKFLOW_APPROVED_BY_DIRECTOR => null,                                       // Step 4 → Done
            self::WORKFLOW_REJECTED => null,                                                   // Rejected → Done
            // Legacy support
            'approved_by_approver' => self::WORKFLOW_APPROVED_BY_REVIEWER,
            'approved' => null,
        ];

        $nextStep = $workflowMap[$currentStep] ?? null;

        // Ensure return value is always a string or null
        return $nextStep !== null ? (string) $nextStep : null;
    }

    public function canBeApproved(): bool
    {
        if ($this->workflow_status === self::WORKFLOW_APPROVED_BY_DIRECTOR) {
            return false;
        }

        if ($this->workflow_status === self::WORKFLOW_REJECTED) {
            return false;
        }

        return in_array($this->workflow_status, [
            self::WORKFLOW_PENDING,
            self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD,
            self::WORKFLOW_APPROVED_BY_REVIEWER,
            'approved_by_approver'
        ]);
    }

    // ✅ Override canBeRejected - can reject at any step before final
    public function canBeRejected(): bool
    {
        if ($this->signed_pdf_path) {
            return false;
        }

        if ($this->workflow_status === self::WORKFLOW_APPROVED_BY_DIRECTOR) {
            return false;
        }

        if ($this->workflow_status === self::WORKFLOW_REJECTED) {
            return false;
        }

        // Can reject at any step before final approval
        return in_array($this->workflow_status, [
            self::WORKFLOW_PENDING,
            self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD,
            self::WORKFLOW_APPROVED_BY_REVIEWER,
            // Legacy support
            'approved_by_approver'
        ]);
    }

    // ✅ Override getCurrentLevelApprover - 4 step workflow
    public function getCurrentLevelApprover()
    {
        $status = $this->workflow_status;
        
        switch ($status) {
            case self::WORKFLOW_PENDING:
                return $this->getDepartmentHead();

            case self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD:
                return $this->getReviewer();

            case self::WORKFLOW_APPROVED_BY_REVIEWER:
                return $this->getDirector();

            // Legacy support
            case 'approved_by_approver':
                return $this->getReviewer();

            default:
                return null;
        }
    }

    /**
     * Get department head for employee's department
     */
    public function getDepartmentHead()
    {
        if (!$this->employee || !$this->employee->department_id) {
        return null;
        }

        return \App\Models\User::where('department_id', $this->employee->department_id)
            ->where(function($q) {
                $q->where('is_department_head', true)
                  ->orWhereHas('roles', function($r) {
                      $r->whereIn('name', ['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);
                  });
            })
            ->first();
    }

    /**
     * Get reviewer role (thẩm định) - role có quyền thẩm định nhiều phòng ban
     */
    public function getReviewer()
    {
        return \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Thẩm định']);
        })->first();
    }

    /**
     * Get director (BGD) - final approver
     */
    public function getDirector()
    {
        return \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        })->first();
    }

    // Scopes
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->whereHas('employee', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => 'Chờ duyệt',
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            self::STATUS_CANCELLED => 'Đã hủy'
        ];
        return $statuses[$this->status] ?? 'Không xác định';
    }

    public function getWorkflowStatusTextAttribute()
    {
        $statuses = [
            self::WORKFLOW_PENDING => 'Chờ chỉ huy xác nhận',
            self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD => 'Chờ thẩm định',
            self::WORKFLOW_APPROVED_BY_REVIEWER => 'Chờ BGD ký',
            self::WORKFLOW_APPROVED_BY_DIRECTOR => 'Đã phê duyệt hoàn tất',
            self::WORKFLOW_REJECTED => 'Đã từ chối',
            // Legacy
            self::WORKFLOW_IN_REVIEW => 'Đang xem xét',
            self::WORKFLOW_APPROVED_BY_APPROVER => 'Đã phê duyệt',
        ];
        return $statuses[$this->workflow_status] ?? 'Không xác định';
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary'
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    public function getWorkflowStatusColorAttribute()
    {
        $colors = [
            self::WORKFLOW_PENDING => 'info',
            self::WORKFLOW_IN_REVIEW => 'warning',
            self::WORKFLOW_APPROVED => 'success',
            self::WORKFLOW_REJECTED => 'danger'
        ];
        return $colors[$this->workflow_status] ?? 'secondary';
    }

    // Computed attributes for backward compatibility
    public function getStartDateAttribute()
    {
        return $this->from_date;
    }

    public function getEndDateAttribute()
    {
        return $this->to_date;
    }

    public function getLeaveTypeTextAttribute()
    {
        $types = [
            self::TYPE_BUSINESS => 'Công tác',
            self::TYPE_ATTENDANCE => 'Cơ động',
            self::TYPE_STUDY => 'Đi học',
            self::TYPE_LEAVE => 'Nghỉ phép',
            self::TYPE_OTHER => 'Khác'
        ];
        return $types[$this->leave_type] ?? 'Không xác định';
    }

    public function getReasonAttribute()
    {
        return $this->note;
    }

    /**
     * Override approveButton to check workflow step and user role
     * Only show button if user is the correct approver for current step
     */
    public function approveButton()
    {
        if (!$this->canBeApproved()) {
            return '';
        }

        $user = backpack_user();
        if (!$user) {
            return '';
        }

        // Check if user has permission
        $modulePermission = $this->getModulePermission();
        $hasApprovePermission = \App\Helpers\PermissionHelper::can($user, "{$modulePermission}.approve");
        $hasReviewPermission = \App\Helpers\PermissionHelper::can($user, "{$modulePermission}.review");
        
        if (!$hasApprovePermission && !$hasReviewPermission) {
            return '';
        }

        // Check if user is the correct approver for current workflow step
        if (!$this->canUserApproveAtCurrentStep($user)) {
            return '';
        }

        // Check if this is reviewer step - use approve-without-pin (no signature needed)
        $isReviewerStep = $this->workflow_status === self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD && $hasReviewPermission;
        
        if ($isReviewerStep) {
            // Reviewer step: no PIN needed, just approve and forward to BGD
            return $this->generateReviewerApproveButtonHtml();
        }

        // Generate button HTML with PIN modal (for department head and director)
        $modelClass = base64_encode(get_class($this));
        $modalId = 'pinModal_' . $this->id;
        $approvalUrl = route('approval.approve-with-pin', ['modelClass' => $modelClass, 'id' => $this->id]);

        return $this->generateApproveButtonHtml($modalId, $approvalUrl);
    }

    /**
     * Override rejectButton to check workflow step and user role
     * Only show button if user is the correct approver for current step
     */
    public function rejectButton()
    {
        if (!$this->canBeRejected()) {
            return '';
        }

        $user = backpack_user();
        if (!$user) {
            return '';
        }

        // Check if user has permission
        $modulePermission = $this->getModulePermission();
        $hasApprovePermission = \App\Helpers\PermissionHelper::can($user, "{$modulePermission}.approve");
        $hasReviewPermission = \App\Helpers\PermissionHelper::can($user, "{$modulePermission}.review");
        
        if (!$hasApprovePermission && !$hasReviewPermission) {
            return '';
        }

        // Check if user is the correct approver for current workflow step
        if (!$this->canUserApproveAtCurrentStep($user)) {
            return '';
        }

        // Generate button HTML (copy from ApprovalButtons trait)
        $modelClass = base64_encode(get_class($this));
        $rejectUrl = route('approval.reject', ['modelClass' => $modelClass, 'id' => $this->id]);
        $modalId = 'rejectModal_' . $this->id;

        return $this->generateRejectButtonHtml($modalId, $rejectUrl);
    }

    /**
     * Generate approve button HTML (extracted from ApprovalButtons trait)
     */
    protected function generateApproveButtonHtml($modalId, $approvalUrl)
    {
        // Use trait method via trait alias if available, otherwise copy code
        // Since we can't call parent from trait, we'll use the trait's method directly
        // by calling the trait method name with different approach
        $traitMethod = \Modules\ApprovalWorkflow\Traits\ApprovalButtons::class . '::approveButton';

        // Actually, we need to copy the HTML generation code
        // Let's use a simpler approach - call the trait method but it will be overridden
        // So we need to manually generate the HTML

        // For now, let's use a workaround: call the original trait method via reflection
        // But simpler: just copy the essential parts we need

        return '
        <button class="btn btn-sm btn-success" onclick="showPinModal_' . $this->id . '()">
            <i class="la la-check"></i> Phê duyệt
        </button>
        <script>
        function showPinModal_' . $this->id . '() {
            const searchInputs = document.querySelectorAll(\'input[type="search"], .dataTables_filter input\');
            searchInputs.forEach(input => {
                if (input.value === "admin" || input.value.toLowerCase().includes("admin")) {
                    input.value = "";
                }
            });

            var modalHtml = `
            <div class="modal fade" id="' . $modalId . '" tabindex="-1" data-bs-backdrop="static" style="z-index: 99999 !important;">
                <div class="modal-dialog" style="z-index: 100000 !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Xác nhận Phê duyệt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="error_message_' . $this->id . '" class="alert alert-danger" style="display:none;" role="alert">
                                <i class="la la-exclamation-circle"></i> <span id="error_text_' . $this->id . '"></span>
                            </div>
                            <p class="mb-3">Vui lòng nhập mã PIN chữ ký số của bạn để xác thực:</p>
                            <input type="text" name="fake_username_' . $this->id . '" style="position:absolute;top:-9999px;left:-9999px;" autocomplete="username" tabindex="-1">
                            <div class="mb-3">
                                <label class="form-label">Mã PIN <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="pin_input_' . $this->id . '" name="certificate_pin_' . $this->id . '" placeholder="Nhập mã PIN" autocomplete="new-password" autocorrect="off" autocapitalize="none" spellcheck="false" autofocus>
                                <div class="form-text">Mã PIN này đã được thiết lập trong trang Thông tin cá nhân</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú (tùy chọn)</label>
                                <textarea class="form-control" id="comment_input_' . $this->id . '" rows="2" placeholder="Nhập ghi chú nếu có" autocomplete="off"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-success" onclick="submitApproval_' . $this->id . '()">
                                <i class="la la-check"></i> Xác nhận Ký số
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

            var existingModal = document.getElementById(\'' . $modalId . '\');
            if (existingModal) existingModal.remove();

            document.body.insertAdjacentHTML(\'beforeend\', modalHtml);
            var modal = new bootstrap.Modal(document.getElementById(\'' . $modalId . '\'));
            modal.show();

            document.getElementById(\'' . $modalId . '\').addEventListener(\'shown.bs.modal\', function() {
                document.getElementById(\'pin_input_' . $this->id . '\').focus();
            });

            document.getElementById(\'' . $modalId . '\').addEventListener(\'hidden.bs.modal\', function() {
                this.remove();
            });
        }

        function submitApproval_' . $this->id . '() {
            var pin = document.getElementById(\'pin_input_' . $this->id . '\').value;
            var comment = document.getElementById(\'comment_input_' . $this->id . '\').value;
            var errorDiv = document.getElementById(\'error_message_' . $this->id . '\');
            var errorText = document.getElementById(\'error_text_' . $this->id . '\');

            errorDiv.style.display = \'none\';

            if (!pin || pin.trim() === \'\') {
                errorText.textContent = \'Vui lòng nhập mã PIN!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'pin_input_' . $this->id . '\').focus();
                return;
            }

            var formData = new FormData();
            formData.append(\'_token\', document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\'));
            formData.append(\'certificate_pin\', pin);
            formData.append(\'comment\', comment);

            var submitBtn = event.target;
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = \'<i class="la la-spinner la-spin"></i> Đang xử lý...\';

            fetch(\'' . $approvalUrl . '\', {
                method: \'POST\',
                body: formData,
                headers: { \'X-Requested-With\': \'XMLHttpRequest\' }
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.success) {
                    var modalEl = document.getElementById(\'' . $modalId . '\');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    if (typeof swal !== \'undefined\') {
                        swal({
                            title: \'Phê duyệt thành công\',
                            text: data.message || \'Đơn xin nghỉ phép đã được phê duyệt.\',
                            icon: \'success\',
                            timer: 2000,
                            buttons: false,
                            closeOnClickOutside: false,
                            closeOnEsc: false
                        }).then(function() {
                            window.location.href = \'' . backpack_url('leave-request') . '\';
                        });
                    } else {
                        new Noty({
                            type: \'success\',
                            text: \'<strong>Phê duyệt thành công</strong><br>\' + (data.message || \'Đơn xin nghỉ phép đã được phê duyệt.\'),
                            timeout: 2000
                        }).show();
                        setTimeout(function() {
                            window.location.href = \'' . backpack_url('leave-request') . '\';
                        }, 2000);
                    }
                } else {
                    errorText.textContent = data.message || \'Không thể phê duyệt\';
                    errorDiv.style.display = \'block\';
                    document.getElementById(\'pin_input_' . $this->id . '\').focus();
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                errorText.textContent = \'Có lỗi xảy ra khi kết nối máy chủ\';
                errorDiv.style.display = \'block\';
            });
        }
        </script>
        <style>
            .modal-backdrop { z-index: 99998 !important; }
            #' . $modalId . ' { z-index: 99999 !important; }
        </style>
        ';
    }

    /**
     * Generate approve button HTML for reviewer step (no PIN, just approve and forward)
     */
    protected function generateReviewerApproveButtonHtml()
    {
        $modelClass = base64_encode(get_class($this));
        $approvalUrl = route('approval.approve-without-pin', ['modelClass' => $modelClass, 'id' => $this->id]);

        return '
        <button class="btn btn-sm btn-success" onclick="submitReviewerApproval_' . $this->id . '()">
            <i class="la la-paper-plane"></i> Gửi lên BGD
        </button>
        <script>
        function submitReviewerApproval_' . $this->id . '() {
            var submitBtn = event.target;
            
            // Show confirmation dialog using SweetAlert
            if (typeof swal !== \'undefined\') {
                swal({
                    title: \'Xác nhận gửi lên BGD\',
                    html: \'<i class="la la-question-circle" style="font-size: 48px; color: #17a2b8; margin-bottom: 15px;"></i><p style="margin-top: 10px;">Bạn có chắc chắn muốn gửi đơn này lên Ban Giám đốc?</p>\',
                    buttons: {
                        cancel: {
                            text: \'Hủy\',
                            value: false,
                            visible: true,
                            className: \'btn btn-secondary\',
                            closeModal: true
                        },
                        confirm: {
                            text: \'Xác nhận\',
                            value: true,
                            visible: true,
                            className: \'btn btn-success\',
                            closeModal: false
                        }
                    },
                    dangerMode: false,
                    closeOnClickOutside: true
                }).then(function(willApprove) {
                    if (!willApprove) {
                        return;
                    }

                    var formData = new FormData();
                    formData.append(\'_token\', document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\'));

                    var originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = \'<i class="la la-spinner la-spin"></i> Đang xử lý...\';

                    fetch(\'' . $approvalUrl . '\', {
                        method: \'POST\',
                        body: formData,
                        headers: { \'X-Requested-With\': \'XMLHttpRequest\' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;

                        if (data.success) {
                            swal({
                                title: \'Gửi lên BGD thành công\',
                                text: data.message || \'Đơn xin nghỉ phép đã được gửi lên Ban Giám đốc.\',
                                icon: \'success\',
                                timer: 2000,
                                buttons: false,
                                closeOnClickOutside: false,
                                closeOnEsc: false
                            }).then(function() {
                                window.location.href = \'' . backpack_url('leave-request') . '\';
                            });
                        } else {
                            swal({
                                title: \'Lỗi\',
                                text: data.message || \'Không thể gửi lên BGD\',
                                icon: \'error\',
                                button: \'Đóng\'
                            });
                        }
                    })
                    .catch(error => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        swal({
                            title: \'Lỗi kết nối\',
                            text: \'Có lỗi xảy ra khi kết nối máy chủ\',
                            icon: \'error\',
                            button: \'Đóng\'
                        });
                    });
                });
            } else {
                // Fallback to Noty if SweetAlert is not available
                if (!confirm(\'Bạn có chắc chắn muốn gửi đơn này lên Ban Giám đốc?\')) {
                    return;
                }

                var formData = new FormData();
                formData.append(\'_token\', document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\'));

                var originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = \'<i class="la la-spinner la-spin"></i> Đang xử lý...\';

                fetch(\'' . $approvalUrl . '\', {
                    method: \'POST\',
                    body: formData,
                    headers: { \'X-Requested-With\': \'XMLHttpRequest\' }
                })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (data.success) {
                        new Noty({
                            type: \'success\',
                            text: \'<strong>Gửi lên BGD thành công</strong><br>\' + (data.message || \'Đơn xin nghỉ phép đã được gửi lên Ban Giám đốc.\'),
                            timeout: 2000
                        }).show();
                        setTimeout(function() {
                            window.location.href = \'' . backpack_url('leave-request') . '\';
                        }, 2000);
                    } else {
                        showError(data.message || \'Không thể gửi lên BGD\', \'Lỗi\');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    showError(\'Có lỗi xảy ra khi kết nối máy chủ\', \'Lỗi kết nối\');
                });
            }
        }
        </script>
        ';
    }

    protected function generateRejectButtonHtml($modalId, $rejectUrl)
    {
        return '
        <button class="btn btn-sm btn-danger" onclick="showRejectModal_' . $this->id . '()">
            <i class="la la-times"></i> Từ chối
        </button>
        <script>
        function showRejectModal_' . $this->id . '() {
            const searchInputs = document.querySelectorAll(\'input[type="search"], .dataTables_filter input\');
            searchInputs.forEach(input => {
                if (input.value === "admin" || input.value.toLowerCase().includes("admin")) {
                    input.value = "";
                }
            });

            var modalHtml = `
            <div class="modal fade" id="' . $modalId . '" tabindex="-1" data-bs-backdrop="static" style="z-index: 99999 !important;">
                <div class="modal-dialog" style="z-index: 100000 !important;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Từ chối phê duyệt</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="reject_error_message_' . $this->id . '" class="alert alert-danger" style="display:none;" role="alert">
                                <i class="la la-exclamation-circle"></i> <span id="reject_error_text_' . $this->id . '"></span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reject_reason_' . $this->id . '" rows="3" placeholder="Nhập lý do từ chối (ít nhất 5 ký tự)" autocomplete="off" required autofocus></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="button" class="btn btn-danger" onclick="submitRejection_' . $this->id . '()">
                                <i class="la la-times"></i> Xác nhận Từ chối
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

            var existingModal = document.getElementById(\'' . $modalId . '\');
            if (existingModal) existingModal.remove();

            document.body.insertAdjacentHTML(\'beforeend\', modalHtml);
            var modal = new bootstrap.Modal(document.getElementById(\'' . $modalId . '\'));
            modal.show();

            document.getElementById(\'' . $modalId . '\').addEventListener(\'shown.bs.modal\', function() {
                document.getElementById(\'reject_reason_' . $this->id . '\').focus();
            });

            document.getElementById(\'' . $modalId . '\').addEventListener(\'hidden.bs.modal\', function() {
                this.remove();
            });
        }

        function submitRejection_' . $this->id . '() {
            var reason = document.getElementById(\'reject_reason_' . $this->id . '\').value;
            var errorDiv = document.getElementById(\'reject_error_message_' . $this->id . '\');
            var errorText = document.getElementById(\'reject_error_text_' . $this->id . '\');

            errorDiv.style.display = \'none\';

            if (!reason || reason.trim() === \'\') {
                errorText.textContent = \'Vui lòng nhập lý do từ chối!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'reject_reason_' . $this->id . '\').focus();
                return;
            }

            if (reason.trim().length < 5) {
                errorText.textContent = \'Lý do từ chối phải có ít nhất 5 ký tự!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'reject_reason_' . $this->id . '\').focus();
                return;
            }

            var formData = new FormData();
            formData.append(\'_token\', document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\'));
            formData.append(\'reason\', reason);

            var submitBtn = event.target;
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = \'<i class="la la-spinner la-spin"></i> Đang xử lý...\';

            fetch(\'' . $rejectUrl . '\', {
                method: \'POST\',
                body: formData,
                headers: { \'X-Requested-With\': \'XMLHttpRequest\' }
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.success) {
                    var modalEl = document.getElementById(\'' . $modalId . '\');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    if (typeof swal !== \'undefined\') {
                        swal({
                            title: \'Từ chối thành công\',
                            text: data.message || \'Đơn xin nghỉ phép đã bị từ chối.\',
                            icon: \'success\',
                            timer: 2000,
                            buttons: false,
                            closeOnClickOutside: false,
                            closeOnEsc: false
                        }).then(function() {
                            window.location.href = \'' . backpack_url('leave-request') . '\';
                        });
                    } else {
                        new Noty({
                            type: \'success\',
                            text: \'<strong>Từ chối thành công</strong><br>\' + (data.message || \'Đơn xin nghỉ phép đã bị từ chối.\'),
                            timeout: 2000
                        }).show();
                        setTimeout(function() {
                            window.location.href = \'' . backpack_url('leave-request') . '\';
                        }, 2000);
                    }
                } else {
                    errorText.textContent = data.message || \'Không thể từ chối\';
                    errorDiv.style.display = \'block\';
                    document.getElementById(\'reject_reason_' . $this->id . '\').focus();
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                errorText.textContent = \'Có lỗi xảy ra khi kết nối máy chủ\';
                errorDiv.style.display = \'block\';
            });
        }
        </script>
        <style>
            .modal-backdrop { z-index: 99998 !important; }
            #' . $modalId . ' { z-index: 99999 !important; }
        </style>
        ';
    }

    /**
     * Check if user can approve at current workflow step
     */
    protected function canUserApproveAtCurrentStep($user): bool
    {
        // Admin can always approve
        if ($user->hasRole('Admin')) {
            return true;
        }

        $status = $this->workflow_status;

        // Step 1: Only department head of the employee's department can approve
        if ($status === self::WORKFLOW_PENDING) {
            if (!$this->employee || !$this->employee->department_id) {
                return false;
            }

            $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);
            if (!$isDepartmentHead) {
                return false;
            }

            $userDepartmentId = $user->department_id;
            if ($user->employee_id) {
                $emp = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                if ($emp && $emp->department_id) {
                    $userDepartmentId = $emp->department_id;
                }
            }

            return $userDepartmentId == $this->employee->department_id;
        }

        // Step 2: Only reviewer can approve (check by role or permission)
        if ($status === self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD) {
            // Check by role
            if ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Thẩm định'])) {
                return true;
            }
            // Check by permission
            if (\App\Helpers\PermissionHelper::can($user, 'leave.review')) {
                return true;
            }
            return false;
        }

        // Step 3: Only director (BGD) can approve
        if ($status === self::WORKFLOW_APPROVED_BY_REVIEWER) {
            return $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        }

        // Legacy support
        if ($status === 'approved_by_approver') {
            return $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Thẩm định']);
        }

        return false;
    }

    public function showButton()
    {
        $user = backpack_user();

        if (!\App\Helpers\PermissionHelper::can($user, 'leave.view')) {
            return '';
        }

        return '<a href="' . backpack_url('leave-request/' . $this->id . '/show') . '" bp-button="show" class="btn btn-sm btn-link" title="Xem">
            <i class="la la-eye"></i> Xem
        </a>';
    }

    public function editButtonConditional()
    {
        $user = backpack_user();

        if (!\App\Helpers\PermissionHelper::can($user, 'leave.edit')) {
            return '';
        }

        if (!$user->employee_id || $user->employee_id != $this->employee_id) {
            return '';
        }

        if ($this->workflow_status === self::WORKFLOW_PENDING) {
            return '<a class="btn btn-sm btn-link" href="' . backpack_url('leave-request/' . $this->id . '/edit') . '" title="Sửa">
                <i class="la la-edit"></i> Sửa
            </a>';
        }

        return '';
    }

    public function deleteButtonConditional()
    {
        $user = backpack_user();

        if (!\App\Helpers\PermissionHelper::can($user, 'leave.delete')) {
            return '';
        }

        if (!$user->employee_id || $user->employee_id != $this->employee_id) {
            return '';
        }

        if ($this->workflow_status === self::WORKFLOW_PENDING) {
            $route = backpack_url('leave-request/' . $this->id);
            return '<a class="btn btn-sm btn-link" href="javascript:void(0)" onclick="deleteLeaveRequest_' . $this->id . '(this)" title="Xóa" data-route="' . $route . '">
                <i class="la la-trash"></i> Xóa
            </a>
            <script>
            function deleteLeaveRequest_' . $this->id . '(button) {
                var route = button.getAttribute(\'data-route\');
                confirmDelete(
                    \'' . getUserTitle() . ' có chắc chắn muốn xóa đơn xin nghỉ phép này?\',
                    null,
                    function() {
                        $.ajax({
                            url: route,
                            type: \'DELETE\',
                            headers: {
                                \'X-CSRF-TOKEN\': $(\'meta[name="csrf-token"]\').attr(\'content\')
                            },
                            success: function(result) {
                                if (result == 1) {
                                    if (typeof crud != \'undefined\' && typeof crud.table != \'undefined\') {
                                        if(crud.table.rows().count() === 1) {
                                            crud.table.page("previous");
                                        }
                                        crud.table.draw(false);
                                    }
                                    showSuccess(\'Đã xóa thành công\', \'Thành công\', 2000);
                                    $(\'.modal\').modal(\'hide\');
                                } else {
                                    if (result instanceof Object) {
                                        Object.entries(result).forEach(function(entry) {
                                            var type = entry[0];
                                            entry[1].forEach(function(message) {
                                                new Noty({
                                                    type: type,
                                                    text: message
                                                }).show();
                                            });
                                        });
                                    } else {
                                        showError(\'Không thể xóa\', \'Lỗi\');
                                    }
                                }
                            },
                            error: function(xhr) {
                                var message = \'Có lỗi xảy ra khi xóa\';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                showError(message, \'Lỗi\');
                            }
                        });
                    }
                );
            }
            </script>';
        }

        return '';
    }

    /**
     * Override downloadPdfButton - only show when approved by director
     * Show icon only, no text, white icon color
     */
    public function downloadPdfButton()
    {
        // Only show when status is approved_by_director
        if ($this->workflow_status !== self::WORKFLOW_APPROVED_BY_DIRECTOR) {
            return '';
        }

        if (!$this->hasSignedPdf()) {
            return '';
        }

        $pdfUrl = route('approval.download-pdf', ['modelClass' => base64_encode(get_class($this)), 'id' => $this->id]);

        return '<a class="btn btn-sm btn-info" href="' . $pdfUrl . '" target="_blank" title="Tải về PDF đã ký" style="color: white !important;">
            <i class="la la-download" style="color: white !important;"></i>
        </a>';
    }
}

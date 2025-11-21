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
        'selected_approvers',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = ['deleted_at', 'from_date', 'to_date', 'start_at', 'end_at', 'approved_at', 'reviewed_at', 'approved_at_department_head', 'approved_at_reviewer', 'approved_at_director'];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at_department_head' => 'datetime',
        'approved_at_reviewer' => 'datetime',
        'approved_at_director' => 'datetime',
        'is_authorized' => 'boolean',
        'is_checked' => 'boolean',
        'workflow_status' => 'string',
        'selected_approvers' => 'array'
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

    /**
     * Get all directors (BGD) - for reviewer to select
     */
    public static function getDirectors()
    {
        return \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        })->get();
    }

    /**
     * Get selected approvers (directors selected by reviewer)
     */
    public function getSelectedApprovers()
    {
        if (!$this->selected_approvers) {
            return collect([]);
        }
        
        $approverIds = is_array($this->selected_approvers) 
            ? $this->selected_approvers 
            : json_decode($this->selected_approvers, true);
            
        if (!is_array($approverIds)) {
            return collect([]);
        }
        
        return \App\Models\User::whereIn('id', $approverIds)->get();
    }

    /**
     * Check if user is in selected approvers list
     */
    public function isUserSelectedApprover($userId)
    {
        if (!$this->selected_approvers) {
            return false;
        }
        
        $approverIds = is_array($this->selected_approvers) 
            ? $this->selected_approvers 
            : json_decode($this->selected_approvers, true);
            
        if (!is_array($approverIds)) {
            return false;
        }
        
        // Convert all IDs to integers for comparison (handle both string and int in JSON)
        $approverIds = array_map('intval', $approverIds);
        $userId = (int)$userId;
        
        return in_array($userId, $approverIds);
    }

    /**
     * Override approve button to handle reviewer step differently
     */
    public function approveButton()
    {
        if (!$this->canBeApproved()) {
            return '';
        }

        $user = backpack_user();
        $modulePermission = $this->getModulePermission();

        if (!\App\Helpers\PermissionHelper::can($user, "{$modulePermission}.approve")) {
            return '';
        }

        // Check if this is reviewer step
        $isReviewerStep = $this->workflow_status === self::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD;
        $hasReviewPermission = \App\Helpers\PermissionHelper::can($user, 'leave.review');

        if ($isReviewerStep && $hasReviewPermission) {
            // For reviewer step: show "Người phê duyệt" button
            return $this->assignApproversButton();
        }

        // For other steps: show normal approve button with PIN
        // Copy code from ApprovalButtons trait since we can't use parent:: with traits
        $modelClass = base64_encode(get_class($this));
        $modalId = 'pinModal_' . $this->id;
        $approvalUrl = route('approval.approve-with-pin', ['modelClass' => $modelClass, 'id' => $this->id]);

        return '
        <button class="btn btn-sm btn-success" onclick="showPinModal_' . $this->id . '()">
            <i class="la la-check"></i> Phê duyệt
        </button>
        <script>
        function showPinModal_' . $this->id . '() {
            // Clear any search input before showing modal (prevent Chrome autofill)
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
                            <!-- Error message container -->
                            <div id="error_message_' . $this->id . '" class="alert alert-danger" style="display:none;" role="alert">
                                <i class="la la-exclamation-circle"></i> <span id="error_text_' . $this->id . '"></span>
                            </div>

                            <p class="mb-3">Vui lòng nhập mã PIN chữ ký số của bạn để xác thực:</p>

                            <!-- Hidden fake username field to prevent Chrome from filling search box -->
                            <input type="text" name="fake_username_' . $this->id . '" style="position:absolute;top:-9999px;left:-9999px;" autocomplete="username" tabindex="-1">

                            <div class="mb-3">
                                <label class="form-label">Mã PIN <span class="text-danger">*</span></label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="pin_input_' . $this->id . '"
                                    name="certificate_pin_' . $this->id . '"
                                    placeholder="Nhập mã PIN"
                                    autocomplete="new-password"
                                    autocorrect="off"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    autofocus>
                                <div class="form-text">Mã PIN này đã được thiết lập trong trang Thông tin cá nhân</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú (tùy chọn)</label>
                                <textarea
                                    class="form-control"
                                    id="comment_input_' . $this->id . '"
                                    rows="2"
                                    placeholder="Nhập ghi chú nếu có"
                                    autocomplete="off"></textarea>
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
            if (existingModal) {
                existingModal.remove();
            }

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

            // Hide error message
            errorDiv.style.display = \'none\';

            // Validate PIN
            if (!pin || pin.trim() === \'\') {
                errorText.textContent = \'Vui lòng nhập mã PIN!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'pin_input_' . $this->id . '\').focus();
                return;
            }

            if (pin.length < 1) {
                errorText.textContent = \'Mã PIN phải có ít nhất 1 ký tự!\';
                errorDiv.style.display = \'block\';
                document.getElementById(\'pin_input_' . $this->id . '\').focus();
                return;
            }

            var formData = new FormData();
            formData.append(\'_token\', document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\'));
            formData.append(\'certificate_pin\', pin);
            formData.append(\'comment\', comment);

            // Disable submit button during request
            var submitBtn = event.target;
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = \'<i class="la la-spinner la-spin"></i> Đang xử lý...\';

            fetch(\'' . $approvalUrl . '\', {
                method: \'POST\',
                body: formData,
                headers: {
                    \'X-Requested-With\': \'XMLHttpRequest\'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.success) {
                    // Close modal and reload on success
                    var modalEl = document.getElementById(\'' . $modalId . '\');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();

                    alert(\'✅ \' + data.message);
                    window.location.reload();
                } else {
                    // Show error in modal, keep modal open
                    errorText.textContent = data.message || \'Không thể phê duyệt\';
                    errorDiv.style.display = \'block\';
                    document.getElementById(\'pin_input_' . $this->id . '\').focus();
                }
            })
            .catch(error => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                // Show error in modal, keep modal open
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
     * Generate "Assign Approvers" button for reviewer step
     */
    protected function assignApproversButton()
    {
        $modalId = 'assignApproversModal_' . $this->id;
        $getDirectorsUrl = route('approval-center.directors');
        $assignUrl = route('approval-center.assign-approvers');

        return '
        <button class="btn btn-sm btn-primary" onclick="showAssignApproversModal_' . $this->id . '()">
            <i class="la la-user-plus"></i> Người phê duyệt
        </button>
        <script>
        function showAssignApproversModal_' . $this->id . '() {
            // Fetch directors
            fetch(\'' . $getDirectorsUrl . '\', {
                method: \'GET\',
                headers: { 
                    \'X-Requested-With\': \'XMLHttpRequest\',
                    \'Accept\': \'application/json\'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.directors) {
                    showDirectorsModal_' . $this->id . '(data.directors);
                } else {
                    alert(\'Không thể tải danh sách Ban Giám đốc\');
                }
            })
            .catch(error => {
                console.error(\'Error:\', error);
                alert(\'Có lỗi xảy ra khi tải danh sách Ban Giám đốc\');
            });
        }

        function showDirectorsModal_' . $this->id . '(directors) {
            var directorsList = "";
            directors.forEach(function(director) {
                var checkboxId = "director_" + director.id + "_' . $this->id . '";
                var checkboxClass = "director-checkbox-' . $this->id . '";
                var deptText = director.department || "N/A";
                
                directorsList += "<div class=\"form-check mb-2\">";
                directorsList += "<input class=\"form-check-input " + checkboxClass + "\" type=\"checkbox\" value=\"" + director.id + "\" id=\"" + checkboxId + "\">";
                directorsList += "<label class=\"form-check-label\" for=\"" + checkboxId + "\">";
                directorsList += director.name + " <small class=\"text-muted\">(" + deptText + ")</small>";
                directorsList += "</label></div>";
            });

            var modalHtml = "";
            modalHtml += "<div class=\"modal fade\" id=\"' . $modalId . '\" tabindex=\"-1\" data-bs-backdrop=\"static\" style=\"z-index: 99999 !important;\">";
            modalHtml += "<div class=\"modal-dialog\" style=\"z-index: 100000 !important;\">";
            modalHtml += "<div class=\"modal-content\">";
            modalHtml += "<div class=\"modal-header bg-primary text-white\">";
            modalHtml += "<h5 class=\"modal-title\"><i class=\"la la-user-plus\"></i> Chọn người phê duyệt</h5>";
            modalHtml += "<button type=\"button\" class=\"btn-close btn-close-white\" data-bs-dismiss=\"modal\"></button>";
            modalHtml += "</div>";
            modalHtml += "<div class=\"modal-body\">";
            modalHtml += "<div id=\"assign_error_' . $this->id . '\" class=\"alert alert-danger\" style=\"display:none;\" role=\"alert\">";
            modalHtml += "<i class=\"la la-exclamation-circle\"></i> <span id=\"assign_error_text_' . $this->id . '\"></span>";
            modalHtml += "</div>";
            modalHtml += "<p class=\"mb-3\">Chọn một hoặc nhiều người trong Ban Giám đốc để gửi đơn phê duyệt:</p>";
            modalHtml += "<div style=\"max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;\">";
            modalHtml += directorsList;
            modalHtml += "</div>";
            modalHtml += "<div class=\"mt-3\">";
            modalHtml += "<small class=\"text-muted\"><i class=\"la la-info-circle\"></i> Chỉ những người được chọn mới thấy đơn này trong danh sách phê duyệt</small>";
            modalHtml += "</div>";
            modalHtml += "</div>";
            modalHtml += "<div class=\"modal-footer\">";
            modalHtml += "<button type=\"button\" class=\"btn btn-secondary\" data-bs-dismiss=\"modal\">Hủy</button>";
            modalHtml += "<button type=\"button\" class=\"btn btn-primary\" onclick=\"submitAssignApprovers_' . $this->id . '()\"><i class=\"la la-check\"></i> Xác nhận</button>";
            modalHtml += "</div>";
            modalHtml += "</div>";
            modalHtml += "</div>";
            modalHtml += "</div>";

            var existingModal = document.getElementById(\'' . $modalId . '\');
            if (existingModal) existingModal.remove();

            document.body.insertAdjacentHTML(\'beforeend\', modalHtml);
            var modal = new bootstrap.Modal(document.getElementById(\'' . $modalId . '\'));
            modal.show();

            document.getElementById(\'' . $modalId . '\').addEventListener(\'hidden.bs.modal\', function() {
                this.remove();
            });
        }

        function submitAssignApprovers_' . $this->id . '() {
            var selectedIds = [];
            document.querySelectorAll(\'.director-checkbox-' . $this->id . ':checked\').forEach(function(checkbox) {
                selectedIds.push(parseInt(checkbox.value));
            });

            var errorDiv = document.getElementById(\'assign_error_' . $this->id . '\');
            var errorText = document.getElementById(\'assign_error_text_' . $this->id . '\');
            errorDiv.style.display = \'none\';

            if (selectedIds.length === 0) {
                errorText.textContent = \'Vui lòng chọn ít nhất một người phê duyệt\';
                errorDiv.style.display = \'block\';
                return;
            }

            var formData = new FormData();
            formData.append(\'_token\', document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\'));
            formData.append(\'id\', ' . $this->id . ');
            formData.append(\'model_type\', \'leave\');
            formData.append(\'approver_ids\', JSON.stringify(selectedIds));

            var submitBtn = event.target;
            var originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = \'<i class="la la-spinner la-spin"></i> Đang xử lý...\';

            fetch(\'' . $assignUrl . '\', {
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

                    alert(\'✅ \' + data.message);
                    window.location.reload();
                } else {
                    errorText.textContent = data.message || \'Không thể gán người phê duyệt\';
                    errorDiv.style.display = \'block\';
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
            'business' => 'Công tác - Cơ động',
            'study' => 'Học',
            'leave' => 'Phép - Tranh thủ',
            'hospital' => 'Đi viện',
            'pending' => 'Chờ hưu',
            'sick' => 'Ốm tại trại',
            'maternity' => 'Thai sản',
            'checkup' => 'Khám bệnh',
            'other' => 'Khác'
        ];
        return $types[$this->leave_type] ?? 'Không xác định';
    }

    public function getReasonAttribute()
    {
        return $this->note;
    }

    /**
     * Generate approve button HTML for reviewer step (no PIN, just approve and forward)
     */
    protected function generateReviewerApproveButtonHtml()
    {
        $modelClass = base64_encode(get_class($this));
        $approvalUrl = route('approval.approve-without-pin', ['modelClass' => $modelClass, 'id' => $this->id]);

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

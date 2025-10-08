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
        'approved_by_director',
        'approved_at_director',
        'director_comment',
        'director_signature_path',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = ['deleted_at', 'from_date', 'to_date', 'start_at', 'end_at', 'approved_at', 'reviewed_at'];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'approved_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_authorized' => 'boolean',
        'is_checked' => 'boolean'
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

    // Workflow status constants
    const WORKFLOW_PENDING = 'pending';
    const WORKFLOW_IN_REVIEW = 'in_review';
    const WORKFLOW_APPROVED_BY_APPROVER = 'approved_by_approver';
    const WORKFLOW_APPROVED_BY_DIRECTOR = 'approved_by_director';
    const WORKFLOW_REJECTED = 'rejected';

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

    // ✅ Override workflow status mapping to match old constants
    public function getWorkflowStatusDisplayAttribute(): string
    {
        // Map old workflow status to display text
        $statuses = [
            'pending' => 'Chờ phê duyệt',
            'approved_by_approver' => 'Đã phê duyệt',  // old constant
            'level1_approved' => 'Đã phê duyệt',       // new ApprovalWorkflow
            'approved_by_director' => 'Đã phê duyệt hoàn tất', // old constant
            'level2_approved' => 'Đã phê duyệt hoàn tất',     // new (not used)
            'approved' => 'Đã phê duyệt hoàn tất',            // new ApprovalWorkflow
            'rejected' => 'Đã từ chối'
        ];

        return $statuses[$this->workflow_status] ?? $this->workflow_status;
    }

    // ✅ Override getNextWorkflowStep to support old workflow status values
    public function getNextWorkflowStep(): ?string
    {
        $currentStep = $this->getCurrentWorkflowStep();

        // Map old workflow status to new flow
        $workflowMap = [
            'pending' => 'approved_by_approver',              // Cấp 1 approve
            'approved_by_approver' => 'approved_by_director',  // Cấp 2 approve (final)
            'approved_by_director' => null,                    // Done - cannot approve anymore
            'approved' => null,                                // Done
            'rejected' => null,                                // Done
        ];

        return $workflowMap[$currentStep] ?? null;
    }

    // ✅ Override canBeApproved to prevent approving if already has PDF
    public function canBeApproved(): bool
    {
        // Cannot approve if:
        // 1. Already has signed PDF (final approval done)
        // 2. Already rejected
        // 3. Already at final status
        if ($this->signed_pdf_path) {
            return false;
        }

        if ($this->workflow_status === 'approved_by_director') {
            return false;
        }

        // Can approve if: pending or approved_by_approver (for level 2)
        return in_array($this->workflow_status, ['pending', 'approved_by_approver']);
    }

    // ✅ Override canBeRejected to prevent rejecting if already has PDF
    public function canBeRejected(): bool
    {
        // Cannot reject if:
        // 1. Already has signed PDF (final approval done)
        // 2. Already at final status
        if ($this->signed_pdf_path) {
            return false;
        }

        if ($this->workflow_status === 'approved_by_director') {
            return false;
        }

        // Can reject if: pending or approved_by_approver (before final approval)
        return in_array($this->workflow_status, ['pending', 'approved_by_approver']);
    }

    // ✅ Override getCurrentLevelApprover to support old workflow status values
    public function getCurrentLevelApprover()
    {
        $status = $this->workflow_status;
        
        // Map old workflow status to new flow
        if ($status === 'pending') {
            return $this->level1Approver;
        } elseif (in_array($status, ['approved_by_approver', 'level1_approved'])) {
            return $this->level2Approver;
        } elseif (in_array($status, ['approved_by_director', 'level2_approved'])) {
            return $this->level2Approver; // Final approver
        }
        
        return null;
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
            self::WORKFLOW_PENDING => 'Chờ phê duyệt',
            self::WORKFLOW_IN_REVIEW => 'Đang xem xét',
            self::WORKFLOW_APPROVED_BY_APPROVER => 'Đã phê duyệt',
            self::WORKFLOW_APPROVED_BY_DIRECTOR => 'Đã phê duyệt hoàn tất',
            self::WORKFLOW_REJECTED => 'Đã từ chối'
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

    // ❌ REMOVED: approveButton(), rejectButton(), downloadPdfButton()
    // ✅ These methods are now provided by ApprovalButtons trait!

    /**
     * ✅ Conditional Edit button - Only show for pending status
     */
    public function editButtonConditional()
    {
        $user = backpack_user();

        // Only show edit button if:
        // 1. User has permission
        // 2. Status is pending (not approved or rejected)
        if (!\App\Helpers\PermissionHelper::can($user, 'leave.edit')) {
            return '';
        }

        if ($this->workflow_status === 'pending') {
            return '<a class="btn btn-sm btn-link" href="' . backpack_url('leave-request/' . $this->id . '/edit') . '" title="Sửa">
                <i class="la la-edit"></i> Sửa
            </a>';
        }

        return '';
    }

    /**
     * ✅ Conditional Delete button - Only show for pending/rejected status
     */
    public function deleteButtonConditional()
    {
        $user = backpack_user();

        // Only show delete button if:
        // 1. User has permission
        // 2. Status is pending or rejected (not approved)
        if (!\App\Helpers\PermissionHelper::can($user, 'leave.delete')) {
            return '';
        }

        if (in_array($this->workflow_status, ['pending', 'rejected'])) {
            return '<a class="btn btn-sm btn-link" href="' . backpack_url('leave-request/' . $this->id . '/delete') . '"
                onclick="return confirm(\'' . getUserTitle() . ' có chắc chắn muốn xóa?\');" title="Xóa">
                <i class="la la-trash"></i> Xóa
            </a>';
        }

        return '';
    }
}

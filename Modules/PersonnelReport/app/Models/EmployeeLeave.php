<?php

namespace Modules\PersonnelReport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Modules\OrganizationStructure\Models\Employee;

class EmployeeLeave extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;

    protected $table = 'employee_leave';

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
    const WORKFLOW_APPROVED = 'approved';
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
            self::WORKFLOW_PENDING => 'Chờ xử lý',
            self::WORKFLOW_IN_REVIEW => 'Đang xem xét',
            self::WORKFLOW_APPROVED => 'Đã phê duyệt',
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

    public function getLocationAttribute()
    {
        return 'N/A'; // This field doesn't exist in employee_leave table
    }
}

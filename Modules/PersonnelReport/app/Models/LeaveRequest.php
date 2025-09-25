<?php

namespace Modules\PersonnelReport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Modules\OrganizationStructure\Models\Employee;

class LeaveRequest extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'location',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['deleted_at', 'start_date', 'end_date', 'approved_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime'
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

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

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
}

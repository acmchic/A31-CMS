<?php

namespace Modules\PersonnelReport\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Modules\OrganizationStructure\Models\Employee;
use Modules\OrganizationStructure\Models\Department;

class DailyPersonnelReport extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        'department_id',
        'report_date',
        'total_employees',
        'present_count',
        'absent_count',
        'on_leave_count',
        'sick_count',
        'annual_leave_count',
        'personal_leave_count',
        'military_leave_count',
        'other_leave_count',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['report_date'];
    
    protected $casts = [
        'report_date' => 'date',
        'total_employees' => 'integer',
        'present_count' => 'integer',
        'absent_count' => 'integer',
        'on_leave_count' => 'integer',
        'sick_count' => 'integer',
        'annual_leave_count' => 'integer',
        'personal_leave_count' => 'integer',
        'military_leave_count' => 'integer',
        'other_leave_count' => 'integer'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('report_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    public function getAttendanceRateAttribute()
    {
        if ($this->total_employees == 0) return 0;
        return round(($this->present_count / $this->total_employees) * 100, 2);
    }

    public function getAbsenceRateAttribute()
    {
        if ($this->total_employees == 0) return 0;
        return round(($this->absent_count / $this->total_employees) * 100, 2);
    }

    public function getLeaveRateAttribute()
    {
        if ($this->total_employees == 0) return 0;
        return round(($this->on_leave_count / $this->total_employees) * 100, 2);
    }

    // Static method to generate report for a department on a specific date
    public static function generateReport($departmentId, $date)
    {
        $department = Department::find($departmentId);
        if (!$department) return null;

        // Get total employees in department
        $totalEmployees = $department->employees()->active()->count();

        // Get approved leave requests for the date
        $leaveRequests = EmployeeLeave::whereHas('employee', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        })
        ->where('status', EmployeeLeave::STATUS_APPROVED)
        ->where('from_date', '<=', $date)
        ->where('to_date', '>=', $date)
        ->get();

        // Count by leave type
        $businessCount = $leaveRequests->where('leave_type', EmployeeLeave::TYPE_BUSINESS)->count();
        $attendanceCount = $leaveRequests->where('leave_type', EmployeeLeave::TYPE_ATTENDANCE)->count();
        $studyCount = $leaveRequests->where('leave_type', EmployeeLeave::TYPE_STUDY)->count();
        $leaveCount = $leaveRequests->where('leave_type', EmployeeLeave::TYPE_LEAVE)->count();
        $otherLeaveCount = $leaveRequests->where('leave_type', EmployeeLeave::TYPE_OTHER)->count();

        $onLeaveCount = $leaveRequests->count();
        $presentCount = $totalEmployees - $onLeaveCount;
        $absentCount = 0; // This would need to be tracked separately

        return self::create([
            'department_id' => $departmentId,
            'report_date' => $date,
            'total_employees' => $totalEmployees,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'on_leave_count' => $onLeaveCount,
            'sick_count' => $businessCount,
            'annual_leave_count' => $attendanceCount,
            'personal_leave_count' => $studyCount,
            'military_leave_count' => $leaveCount,
            'other_leave_count' => $otherLeaveCount,
            'created_by' => backpack_user()->name ?? 'system',
            'updated_by' => backpack_user()->name ?? 'system'
        ]);
    }
}

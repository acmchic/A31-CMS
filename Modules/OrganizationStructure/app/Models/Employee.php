<?php

namespace Modules\OrganizationStructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Models\User;

class Employee extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;

    protected $fillable = [
        'name',
        'date_of_birth',
        'enlist_date', 
        'rank_code',
        'position_id',
        'department_id',
        'start_date',
        'quit_date',
        'CCCD',
        'phone',
        'gender',
        'address',
        'max_leave_allowed',
        'annual_leave_balance',
        'annual_leave_total',
        'annual_leave_used',
        'delay_counter',
        'hourly_counter',
        'is_active',
        'created_by',
        'updated_by'
    ];

    protected $attributes = [
        'created_by' => 'system',
        'updated_by' => 'system',
        'is_active' => true,
        'max_leave_allowed' => 0,
        'annual_leave_balance' => 0,
        'annual_leave_total' => 0,
        'annual_leave_used' => 0,
        'delay_counter' => 0,
        'hourly_counter' => 0,
    ];

    protected $dates = ['deleted_at', 'date_of_birth', 'start_date', 'quit_date'];
    
    protected $casts = [
        'gender' => 'boolean',
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
        // enlist_date giờ là string (mm/yyyy), không cast date
        'start_date' => 'date',
        'quit_date' => 'date'
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'employee_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // Accessors
    public function getGenderTextAttribute()
    {
        return $this->gender ? 'Nam' : 'Nữ';
    }

    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Đang làm việc' : 'Đã nghỉ việc';
    }
}

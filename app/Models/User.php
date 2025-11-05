<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, CrudTrait, HasRoles;

    /**
     * Boot the model.
     * Tự động sync dữ liệu từ Employee khi User được tạo hoặc employee_id thay đổi
     */
    protected static function boot()
    {
        parent::boot();

        // Sync dữ liệu từ Employee khi User được created và có employee_id
        static::created(function ($user) {
            if ($user->employee_id && $user->employee) {
                $user->syncFromEmployee();
            }
        });

        // Sync dữ liệu từ Employee khi employee_id được update
        static::updated(function ($user) {
            if ($user->isDirty('employee_id') && $user->employee) {
                $user->syncFromEmployee();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'department_id',
        'profile_photo_path',
        'signature_path',
        'certificate_pin',
        'certificate_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

     public function username()
    {
        return 'username';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Spatie Laravel Permission provides:
    // hasRole(), assignRole(), removeRole(), hasPermissionTo(), etc.

    // Relationship with Employee
    public function employee()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Employee::class, 'employee_id');
    }
    
    // Get correct employee info by matching name
    public function getCorrectEmployee()
    {
        // First try by employee_id relationship
        if ($this->employee_id) {
            $employee = \Modules\OrganizationStructure\Models\Employee::find($this->employee_id);
            if ($employee) {
                return $employee;
            }
        }
        
        // Otherwise, try to find by exact name matching
        $employeeByName = \Modules\OrganizationStructure\Models\Employee::where('name', $this->name)->first();
        
        if ($employeeByName) {
            return $employeeByName;
        }
        
        // Fallback: partial name match
        return \Modules\OrganizationStructure\Models\Employee::where('name', 'LIKE', '%' . $this->name . '%')->first();
    }

    // Relationship with Department
    public function department()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Department::class, 'department_id');
    }

    // Helper method to get user's department
    // Employee là Single Source of Truth, ưu tiên lấy từ Employee
    public function getDepartment()
    {
        // Ưu tiên lấy từ Employee (Single Source of Truth)
        if ($this->employee && $this->employee->department) {
            return $this->employee->department;
        }
        
        // Fallback to direct department relationship
        if ($this->department) {
            return $this->department;
        }
        
        return null;
    }

    /**
     * Get name - ưu tiên lấy từ Employee (Single Source of Truth) nếu có
     * Nếu không có Employee, lấy từ User
     */
    public function getDisplayNameAttribute()
    {
        // Ưu tiên lấy từ Employee (Single Source of Truth)
        if ($this->employee && $this->employee->name) {
            return $this->employee->name;
        }
        
        // Fallback về name trong User (đã được sync)
        return $this->attributes['name'] ?? null;
    }

    /**
     * Sync dữ liệu từ Employee sang User.
     * Được gọi khi User được tạo hoặc employee_id thay đổi.
     * 
     * @return void
     */
    public function syncFromEmployee()
    {
        if (!$this->employee) {
            return;
        }

        // Sync name và department_id từ Employee
        $syncData = [
            'name' => $this->employee->name,
            'department_id' => $this->employee->department_id,
        ];

        // Chỉ update nếu có thay đổi
        $hasChanges = false;
        if ($this->getOriginal('name') !== $this->employee->name) {
            $hasChanges = true;
        }
        if ($this->getOriginal('department_id') != $this->employee->department_id) {
            $hasChanges = true;
        }

        if ($hasChanges) {
            // Sử dụng updateQuietly để tránh trigger events loop
            $this->updateQuietly($syncData);
        }
    }

    /**
     * Sync dữ liệu từ User sang Employee.
     * Được gọi khi User được cập nhật từ profile page.
     * 
     * @return void
     */
    public function syncToEmployee()
    {
        if (!$this->employee) {
            return;
        }

        // Sync name và department_id từ User sang Employee
        $syncData = [
            'name' => $this->name,
            'department_id' => $this->department_id,
        ];

        // Chỉ update nếu có thay đổi
        $hasChanges = false;
        if ($this->employee->name !== $this->name) {
            $hasChanges = true;
        }
        if ($this->employee->department_id != $this->department_id) {
            $hasChanges = true;
        }

        if ($hasChanges) {
            // Sử dụng updateQuietly để tránh trigger events loop
            $this->employee->updateQuietly($syncData);
        }
    }

    // Check if user can access specific department
    public function canAccessDepartment($departmentId)
    {
        if ($this->hasRole('admin')) {
            return true;
        }
        
        // Check direct department assignment first
        if ($this->department_id == $departmentId) {
            return true;
        }
        
        // Fallback to employee's department
        $employee = $this->employee;
        return $employee && $employee->department_id == $departmentId;
    }

    /**
     * Get the profile photo URL
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return \Storage::url($this->profile_photo_path);
        }
        return null;
    }

    /**
     * Get the signature URL
     */
    public function getSignatureUrlAttribute()
    {
        if ($this->signature_path) {
            return \Storage::url($this->signature_path);
        }
        return null;
    }

    /**
     * Get approver title for PDF display
     */
    public function getApproverTitle()
    {
        // Check roles first - get the first role name
        $roles = $this->roles;
        if ($roles->count() > 0) {
            $roleName = $roles->first()->name;
            
            // Map role names to display titles
            $roleMap = [
                'Admin' => 'GIÁM ĐỐC',
                'Ban Giám đốc' => 'BAN GIÁM ĐỐC',
                'Ban Giam Doc' => 'BAN GIÁM ĐỐC', 
                'Ban Giám Đốc' => 'BAN GIÁM ĐỐC',
                'Trưởng phòng' => 'TRƯỞNG PHÒNG',
                'Truong Phong' => 'TRƯỞNG PHÒNG',
                'Trưởng Phòng' => 'TRƯỞNG PHÒNG',
                'Nhân sự' => 'NHÂN SỰ',
                'Nhan Vien' => 'NHÂN SỰ',
                'Nhân Viên' => 'NHÂN SỰ',
            ];
            
            if (isset($roleMap[$roleName])) {
                return $roleMap[$roleName];
            }
            
            // If role not in map, use role name directly
            return strtoupper($roleName);
        }
        
        // Check by department name as fallback
        $department = $this->getDepartment();
        if ($department) {
            $deptName = strtoupper($department->name);
            
            // Map specific department names to titles
            $titleMap = [
                'PHÒNG KẾ HOẠCH' => 'TRƯỞNG PHÒNG KẾ HOẠCH',
                'PHÒNG TỔ CHỨC' => 'TRƯỞNG PHÒNG TỔ CHỨC',
                'PHÒNG TÀI CHÍNH' => 'TRƯỞNG PHÒNG TÀI CHÍNH',
                'PHÒNG KỸ THUẬT' => 'TRƯỞNG PHÒNG KỸ THUẬT',
                'PHÒNG HÀNH CHÍNH' => 'TRƯỞNG PHÒNG HÀNH CHÍNH',
                'PHÒNG NHÂN SỰ' => 'TRƯỞNG PHÒNG NHÂN SỰ',
            ];
            
            if (isset($titleMap[$deptName])) {
                return $titleMap[$deptName];
            }
            
            // Default pattern: TRƯỞNG + department name
            return 'TRƯỞNG ' . $deptName;
        }
        
        // Final fallback
        return 'THỦ TRƯỞNG ĐƠN VỊ';
    }
}

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
        // First try by user_id
        $employee = \Modules\OrganizationStructure\Models\Employee::where('user_id', $this->id)->first();
        
        // If found and name matches, return it
        if ($employee && $this->name === $employee->name) {
            return $employee;
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
    public function getDepartment()
    {
        // First try to get from direct relationship
        if ($this->department) {
            return $this->department;
        }
        
        // Fallback to employee's department
        return $this->employee ? $this->employee->department : null;
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
}

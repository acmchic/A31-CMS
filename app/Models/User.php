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
        return $this->hasOne(\Modules\OrganizationStructure\Models\Employee::class, 'user_id');
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
}

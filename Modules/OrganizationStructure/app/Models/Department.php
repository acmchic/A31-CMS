<?php

namespace Modules\OrganizationStructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Department extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;

    protected $fillable = [
        'name',
        'created_by',
        'updated_by'
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Accessors
    public function getEmployeeCountAttribute()
    {
        // Use employees_count if available (from withCount), otherwise count manually
        return $this->employees_count ?? $this->employees()->count();
    }
}

<?php

namespace Modules\OrganizationStructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Leave extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;

    protected $fillable = [
        'name',
        'is_instantly',
        'is_accumulative',
        'discount_rate',
        'days_limit',
        'minutes_limit',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_instantly' => 'boolean',
        'is_accumulative' => 'boolean',
        'discount_rate' => 'decimal:2',
        'days_limit' => 'integer',
        'minutes_limit' => 'integer'
    ];

    // Relationships
    public function employeeLeaves()
    {
        return $this->hasMany(\Modules\PersonnelReport\Models\EmployeeLeave::class, 'leave_id');
    }

    // Accessors
    public function getIsInstantlyTextAttribute()
    {
        return $this->is_instantly ? 'Có' : 'Không';
    }

    public function getIsAccumulativeTextAttribute()
    {
        return $this->is_accumulative ? 'Có' : 'Không';
    }
}

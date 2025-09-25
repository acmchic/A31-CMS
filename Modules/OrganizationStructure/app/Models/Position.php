<?php

namespace Modules\OrganizationStructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Position extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        'name',
        'description',
        'vacancies_count',
        'created_by',
        'updated_by'
    ];

    protected $attributes = [
        'vacancies_count' => 0,
        'created_by' => 'system',
        'updated_by' => 'system',
    ];

    protected $dates = ['created_at', 'updated_at'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
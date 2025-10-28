<?php

namespace Modules\RecordManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class AnDuongRecord extends Model
{
    use HasFactory, CrudTrait;

    protected $table = 'an_duong_records';

    protected $fillable = [
        'stt',
        'ho_va_ten',
        'cap_bac',
        'chuc_vu',
        'tieu_chuan_duoc_huong',
        'ghi_chu',
        'year',
        'department_id',
        'employee_id',
    ];

    protected $casts = [
        'tieu_chuan_duoc_huong' => 'string',
    ];

    /**
     * Relationship với Department
     */
    public function department()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Department::class);
    }

    /**
     * Relationship với Employee
     */
    public function employee()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Employee::class);
    }

    /**
     * Accessor để format tiêu chuẩn được hưởng
     */
    public function getTieuChuanDuocHuongFormattedAttribute()
    {
        if (empty($this->tieu_chuan_duoc_huong)) {
            return '';
        }
        
        // Nếu là số, format thành tiền tệ
        if (is_numeric($this->tieu_chuan_duoc_huong)) {
            return number_format($this->tieu_chuan_duoc_huong, 0, ',', '.') . ' VNĐ';
        }
        
        return $this->tieu_chuan_duoc_huong;
    }

    /**
     * Scope để filter theo department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}

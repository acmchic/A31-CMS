<?php

namespace Modules\RecordManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class SoDieuDongRecord extends Model
{
    use HasFactory, CrudTrait;

    protected $table = 'records_so_dieu_dong';

    protected $fillable = [
        'employee_id',
        'department_id',
        'nhap_ngu',
        'chuc_vu_cnqc',
        'so_quyet_dinh',
        'ngay_quyet_dinh',
        'nguoi_ky',
        'chuc_vu_nguoi_ky',
        'ly_do_dieu_dong',
        'tu_don_vi',
        'den_don_vi',
        'chuc_vu_cu',
        'chuc_vu_moi',
        'ngay_hieu_luc',
        'ghi_chu',
    ];

    protected $casts = [
        'ngay_quyet_dinh' => 'date',
        'ngay_hieu_luc' => 'date',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Employee::class, 'employee_id');
    }

    public function department()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Department::class, 'department_id');
    }

    // Auto-fill department_id from employee
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if ($model->employee_id && !$model->department_id) {
                $employee = \Modules\OrganizationStructure\Models\Employee::find($model->employee_id);
                if ($employee) {
                    $model->department_id = $employee->department_id;
                    $model->nhap_ngu = $employee->enlist_date;
                    $model->chuc_vu_cnqc = $employee->position ? $employee->position->name : null;
                }
            }
        });
    }
}


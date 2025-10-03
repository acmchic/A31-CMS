<?php

namespace Modules\RecordManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class SalaryUpRecord extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        // Thông tin cơ bản
        'employee_id',
        'department_id',
        'year',
        
        // Thông tin cá nhân
        'ho_ten',
        'nhap_ngu',
        'chuc_vu',
        
        // Lương hiện hưởng
        'luong_hien_loai_nhom',
        'luong_hien_bac',
        'luong_hien_he_so',
        'luong_hien_phan_tram_tn_vk',
        'luong_hien_he_so_bl',
        'luong_hien_quan_ham',
        'luong_hien_thang_nhan',
        
        // Xếp lương mới
        'luong_moi_loai_nhom',
        'luong_moi_bac',
        'luong_moi_he_so',
        'luong_moi_phan_tram_tn_vk',
        'luong_moi_he_so_bl',
        'luong_moi_thang_qd_huong',
        'luong_moi_thang_nam_nhan',
        
        // Thông tin khác
        'don_vi',
        'ghi_chu',
        
        // Metadata
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        // nhap_ngu giờ là string (mm/yyyy), không cast date
        'luong_hien_bac' => 'integer',
        'luong_hien_phan_tram_tn_vk' => 'decimal:2',
        'luong_moi_bac' => 'integer',
        'luong_moi_phan_tram_tn_vk' => 'decimal:2',
    ];

    /**
     * Boot method - Auto-fill thông tin từ Employee
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($record) {
            if ($record->employee_id) {
                $employee = \Modules\OrganizationStructure\Models\Employee::with('position')->find($record->employee_id);
                if ($employee) {
                    // Auto-fill họ tên
                    if (!$record->ho_ten) {
                        $record->ho_ten = $employee->name;
                    }
                    
                    // Auto-fill nhập ngũ (string format mm/yyyy hoặc --)
                    if (!$record->nhap_ngu) {
                        $record->nhap_ngu = $employee->enlist_date ?? '--';
                    }
                    
                    // Auto-fill chức vụ
                    if (!$record->chuc_vu) {
                        $record->chuc_vu = $employee->position ? $employee->position->name : '--';
                    }
                    
                    // Auto-fill quân hàm QNCN
                    if (!$record->luong_hien_quan_ham) {
                        $record->luong_hien_quan_ham = $employee->rank_code ?? '--';
                    }
                }
            }
        });
    }

    // Relationships
    public function employee()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Department::class);
    }

    // Scopes
    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}


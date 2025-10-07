<?php

namespace Modules\RecordManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class QuanNhanRecord extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;

    protected $table = 'records_quan_nhan';

    protected $fillable = [
        // Foreign keys
        'employee_id',
        'department_id',
        
        // Thông tin cá nhân bổ sung
        'ho_ten_thuong_dung',
        'so_hieu_quan_nhan',
        'so_the_QN',
        
        // Thông tin quân đội (các field mới - CHỈ lưu những gì CHƯA CÓ trong employees)
        'cap_bac',
        'ngay_nhan_cap',
        'ngay_cap_cc',
        'cnqs',
        'bac_ky_thuat',
        'tai_ngu',
        'ngay_chuyen_qncn',
        'ngay_chuyen_cnv',
        'luong_nhom_ngach_bac',
        
        // Thông tin chính trị
        'ngay_vao_doan',
        'ngay_vao_dang',
        'ngay_chinh_thuc',
        
        // Thành phần
        'tp_gia_dinh',
        'tp_ban_than',
        'dan_toc',
        'ton_giao',
        
        // Trình độ
        'van_hoa',
        'ngoai_ngu',
        'suc_khoe',
        'hang_thuong_tru',
        'khu_vuc',
        
        // Khen thưởng - Kỷ luật
        'khen_thuong',
        'ky_luat',
        
        // Đào tạo
        'ten_truong',
        'cap_hoc',
        'nganh_hoc',
        'thoi_gian_hoc',
        
        // Nguồn
        'nguon_quan',
        
        // Liên hệ khẩn cấp
        'bao_tin',
        
        // Thông tin gia đình
        'ho_ten_cha',
        'ho_ten_me',
        'ho_ten_vo_chong',
        'may_con',
        
        // Ghi chú
        'ghi_chu',
        
        // Metadata
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'ngay_nhan_cap' => 'date',
        'ngay_cap_cc' => 'date',
        'ngay_chuyen_qncn' => 'date',
        'ngay_chuyen_cnv' => 'date',
        'ngay_vao_doan' => 'date',
        'ngay_vao_dang' => 'date',
        'ngay_chinh_thuc' => 'date',
    ];

    /**
     * Boot method - Auto-fill department từ Employee
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($record) {
            if ($record->employee_id && !$record->department_id) {
                $employee = \Modules\OrganizationStructure\Models\Employee::find($record->employee_id);
                if ($employee) {
                    $record->department_id = $employee->department_id;
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
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }
}


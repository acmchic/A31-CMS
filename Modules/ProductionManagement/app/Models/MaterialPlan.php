<?php

namespace Modules\ProductionManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Modules\ApprovalWorkflow\Traits\HasApprovalWorkflow;
use Modules\ApprovalWorkflow\Traits\ApprovalButtons;
use App\Models\User;

class MaterialPlan extends Model
{
    use CrudTrait, SoftDeletes, HasApprovalWorkflow, ApprovalButtons;

    protected $table = 'material_plans';

    protected $fillable = [
        'ten_khi_tai',
        'ky_hieu_khi_tai',
        'don_vi_co_khi_tai',
        'so_hieu',
        'muc_sua_chua',
        'don_vi_sua_chua',
        'ngay_vao_sua_chua',
        'du_kien_thoi_gian_sua_chua',
        'trang_thai',
        'nguoi_lap_id',
        'selected_approvers',
        'workflow_status',
        'workflow_notes',
        'approved_by_director',
        'approved_at',
    ];

    protected $casts = [
        'selected_approvers' => 'array',
        'don_vi_sua_chua' => 'array', // Cast JSON to array for multiple departments
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function items()
    {
        return $this->hasMany(MaterialPlanItem::class)->orderBy('so_thu_tu');
    }

    public function nguoiLap()
    {
        return $this->belongsTo(User::class, 'nguoi_lap_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_director');
    }

    /**
     * Get status display
     */
    public function getTrangThaiDisplayAttribute()
    {
        $statuses = [
            'nhap' => 'Nhập',
            'cho_phe_duyet' => 'Chờ phê duyệt',
            'dang_phe_duyet' => 'Đang phê duyệt',
            'da_phe_duyet' => 'Đã phê duyệt',
            'tu_choi' => 'Từ chối',
        ];

        return $statuses[$this->trang_thai] ?? $this->trang_thai;
    }

    /**
     * Get module permission for approval workflow
     */
    protected function getModulePermission(): string
    {
        return 'material_plan.approve';
    }

    /**
     * Get title for approval workflow
     */
    public function getApprovalTitleAttribute()
    {
        return "Phương án vật tư: {$this->ten_khi_tai} ({$this->ky_hieu_khi_tai})";
    }

    /**
     * Get type label for approval center
     */
    public function getTypeLabelAttribute()
    {
        return 'Phương án vật tư';
    }

    /**
     * Check if has signed PDF (required by ApprovalButtons trait)
     * MaterialPlan doesn't use digital signature, so always return false
     */
    public function hasSignedPdf(): bool
    {
        return false;
    }
}

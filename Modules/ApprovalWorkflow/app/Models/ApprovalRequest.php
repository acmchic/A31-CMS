<?php

namespace Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ApprovalRequest Model
 * 
 * Bảng tập trung quản lý tất cả các yêu cầu phê duyệt từ các module
 */
class ApprovalRequest extends Model
{
    use SoftDeletes;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'module_type',
        'model_type',
        'model_id',
        'flow_id',
        'created_by',
        'title',
        'description',
        'status',
        'approval_steps',
        'current_step',
        'current_step_index',
        'selected_approvers',
        'approval_history',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'rejection_step',
        'signed_pdf_path',
        'template_pdf_path',
        'metadata',
    ];

    protected $casts = [
        'approval_steps' => 'array',
        'selected_approvers' => 'array',
        'approval_history' => 'array',
        'metadata' => 'array',
        'current_step_index' => 'integer',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the flow that owns this request
     */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'flow_id');
    }

    /**
     * Get the user who created this request
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who rejected this request
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'rejected_by');
    }

    /**
     * Get the related model (polymorphic)
     */
    public function model(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Scope: Filter by module type
     */
    public function scopeModuleType($query, string $moduleType)
    {
        return $query->where('module_type', $moduleType);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by current step
     */
    public function scopeCurrentStep($query, string $step)
    {
        return $query->where('current_step', $step);
    }

    /**
     * Kiểm tra xem user có thể phê duyệt ApprovalRequest ở bước hiện tại không
     * 
     * @param \App\Models\User $user
     * @return bool
     */
    public function canBeApprovedBy(\App\Models\User $user): bool
    {
        // Chỉ có thể approve nếu status là 'submitted' hoặc 'in_review'
        if (!in_array($this->status, ['submitted', 'in_review'])) {
            return false;
        }

        // Xác định module permission
        $modulePermission = $this->getModulePermission();
        
        // Kiểm tra quyền approve cơ bản
        $hasApprovePermission = \App\Helpers\PermissionHelper::can($user, "{$modulePermission}.approve");
        
        // Với leave module: kiểm tra thêm quyền review nếu ở bước review
        $hasReviewPermission = false;
        if ($this->module_type === 'leave' && $this->current_step === 'review') {
            $hasReviewPermission = \App\Helpers\PermissionHelper::can($user, "{$modulePermission}.review");
        }

        if (!$hasApprovePermission && !$hasReviewPermission) {
            return false;
        }

        // Kiểm tra selected_approvers nếu có
        // Nếu bước hiện tại có selected_approvers, user phải nằm trong danh sách đó
        if ($this->selected_approvers) {
            $selectedApprovers = is_array($this->selected_approvers) 
                ? $this->selected_approvers 
                : json_decode($this->selected_approvers, true);

            if (is_array($selectedApprovers)) {
                // Kiểm tra xem current_step có selected_approvers không
                $stepApprovers = $selectedApprovers[$this->current_step] ?? null;
                
                if ($stepApprovers) {
                    // Nếu là object/array với key 'users'
                    if (is_array($stepApprovers) && isset($stepApprovers['users'])) {
                        $users = $stepApprovers['users'];
                    } else {
                        $users = $stepApprovers;
                    }

                    // Kiểm tra user có trong danh sách không
                    if (is_array($users)) {
                        // ✅ Sửa: users có thể là array objects {id, name, email} hoặc array IDs
                        $userIds = [];
                        foreach ($users as $userItem) {
                            if (is_array($userItem) && isset($userItem['id'])) {
                                // Là object với key 'id'
                                $userIds[] = (int)$userItem['id'];
                            } elseif (is_numeric($userItem)) {
                                // Là ID trực tiếp
                                $userIds[] = (int)$userItem;
                            }
                        }
                        
                        if (!in_array((int)$user->id, $userIds)) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get module permission prefix
     */
    protected function getModulePermission(): string
    {
        $modulePermissionMap = [
            'leave' => 'leave',
            'vehicle' => 'vehicle_registration',
            'material' => 'material_plan',
        ];

        return $modulePermissionMap[$this->module_type] ?? $this->module_type;
    }

    /**
     * Get status label (Vietnamese)
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'draft' => 'Nháp',
            'submitted' => 'Đã gửi',
            'in_review' => 'Đang xem xét',
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Đã từ chối',
            'returned' => 'Trả lại',
            'cancelled' => 'Đã hủy',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}

<?php

namespace Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

/**
 * ApprovalRequest Model
 * 
 * Bảng tập trung quản lý tất cả các yêu cầu phê duyệt
 */
class ApprovalRequest extends Model
{
    use SoftDeletes;

    protected $table = 'approval_requests';

    protected $fillable = [
        'module_type',
        'model_type',
        'model_id',
        'created_by',
        'title',
        'description',
        'status',
        'approval_steps',
        'current_step',
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
        'rejected_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Status constants - Trạng thái chung cho toàn hệ thống
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_IN_REVIEW = 'in_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
    
    /**
     * Get approver for a specific step
     */
    public function getApproverForStep($stepName)
    {
        $history = $this->approval_history ?? [];
        if (isset($history[$stepName]['approved_by'])) {
            return User::find($history[$stepName]['approved_by']);
        }
        return null;
    }

    /**
     * Get the actual model instance
     */
    public function getModel()
    {
        if (!class_exists($this->model_type)) {
            return null;
        }
        
        return $this->model_type::find($this->model_id);
    }

    /**
     * Get status display label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_SUBMITTED => 'Đã gửi',
            self::STATUS_IN_REVIEW => 'Đang xem xét',
            self::STATUS_APPROVED => 'Đã phê duyệt',
            self::STATUS_REJECTED => 'Đã từ chối',
            self::STATUS_RETURNED => 'Trả lại',
            self::STATUS_CANCELLED => 'Đã hủy',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get current step label
     */
    public function getCurrentStepLabelAttribute()
    {
        if (!$this->current_step) {
            return null;
        }
        
        $stepLabels = [
            'department_head_approval' => 'Trưởng phòng phê duyệt',
            'review' => 'Thẩm định',
            'vehicle_picked' => 'Đã phân công xe',
            'director_approval' => 'BGD phê duyệt',
        ];
        
        return $stepLabels[$this->current_step] ?? $this->current_step;
    }

    /**
     * Check if can be approved by user at current step
     */
    public function canBeApprovedBy($user, $stepName = null)
    {
        // Check if already approved or rejected
        if (in_array($this->status, [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_CANCELLED])) {
            return false;
        }

        // Must be in review status
        if ($this->status !== self::STATUS_IN_REVIEW) {
            return false;
        }

        // Determine current step
        $step = $stepName ?? $this->current_step;
        if (!$step) {
            return false;
        }

        // Check if user is in selected approvers for this step
        $selectedApprovers = $this->selected_approvers ?? [];
        if (isset($selectedApprovers[$step]) && !empty($selectedApprovers[$step])) {
            $userId = (int)$user->id;
            $stepApprovers = $selectedApprovers[$step];
            return in_array($userId, $stepApprovers) || in_array((string)$userId, $stepApprovers);
        }

        // If no selected approvers for this step, check permission
        return true;
    }

    /**
     * Get next step after approval at current step
     */
    public function getNextStepAfterApproval()
    {
        if (!$this->current_step || !$this->approval_steps) {
            return null;
        }

        $steps = $this->approval_steps;
        $currentIndex = array_search($this->current_step, $steps);
        
        if ($currentIndex === false || $currentIndex === count($steps) - 1) {
            // Last step, move to approved
            return null;
        }

        return $steps[$currentIndex + 1] ?? null;
    }

    /**
     * Check if step is completed
     */
    public function isStepCompleted($stepName)
    {
        $history = $this->approval_history ?? [];
        return isset($history[$stepName]) && !empty($history[$stepName]['approved_by']);
    }
}


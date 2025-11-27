<?php

namespace Modules\ApprovalWorkflow\Services;

use Modules\ApprovalWorkflow\Models\ApprovalRequest;
use Modules\ApprovalWorkflow\Models\ApprovalHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * ApprovalWorkflowHandler
 * 
 * Service xử lý approve/reject dựa hoàn toàn trên approval_requests table
 * Không phụ thuộc vào workflow_status cũ của model
 */
class ApprovalWorkflowHandler
{
    protected $approvalRequestService;

    public function __construct(ApprovalRequestService $approvalRequestService)
    {
        $this->approvalRequestService = $approvalRequestService;
    }

    /**
     * Approve tại step hiện tại
     * 
     * @param ApprovalRequest $approvalRequest
     * @param User $approver
     * @param array $options ['comment', 'signature_path', 'metadata']
     * @return bool
     */
    public function approve(ApprovalRequest $approvalRequest, User $approver, array $options = []): bool
    {
        // Validate
        if (!$this->canApprove($approvalRequest, $approver)) {
            throw new \Exception('Không thể phê duyệt: ' . $this->getCannotApproveReason($approvalRequest, $approver));
        }

        // Check if need to select approvers first (for intermediate steps)
        if ($this->needsApproverSelection($approvalRequest)) {
            throw new \Exception('Vui lòng chọn người phê duyệt trước khi phê duyệt');
        }

        DB::beginTransaction();
        try {
            // Update approval_history
            $this->recordApproval($approvalRequest, $approver, $options);

            // Move to next step or complete
            $this->moveToNextStep($approvalRequest, $approver);

            // Sync to model if needed
            $this->syncToModel($approvalRequest);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject tại step hiện tại
     * 
     * @param ApprovalRequest $approvalRequest
     * @param User $rejector
     * @param string $reason
     * @param array $options
     * @return bool
     */
    public function reject(ApprovalRequest $approvalRequest, User $rejector, string $reason = '', array $options = []): bool
    {
        // Validate
        if (!$this->canReject($approvalRequest, $rejector)) {
            throw new \Exception('Không thể từ chối: ' . $this->getCannotRejectReason($approvalRequest, $rejector));
        }

        DB::beginTransaction();
        try {
            // Update approval_history
            $this->recordRejection($approvalRequest, $rejector, $reason, $options);

            // Update status to rejected
            $approvalRequest->status = ApprovalRequest::STATUS_REJECTED;
            $approvalRequest->rejected_by = $rejector->id;
            $approvalRequest->rejected_at = now();
            $approvalRequest->rejection_reason = $reason;
            $approvalRequest->rejection_step = $approvalRequest->current_step;
            $approvalRequest->current_step = null;
            $approvalRequest->save();

            // Sync to model
            $this->syncToModel($approvalRequest);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if can approve
     */
    protected function canApprove(ApprovalRequest $approvalRequest, User $user): bool
    {
        // Check status
        if (!in_array($approvalRequest->status, [
            ApprovalRequest::STATUS_SUBMITTED,
            ApprovalRequest::STATUS_IN_REVIEW
        ])) {
            return false;
        }

        // Check if user can approve at current step
        return $approvalRequest->canBeApprovedBy($user);
    }

    /**
     * Check if can reject
     */
    protected function canReject(ApprovalRequest $approvalRequest, User $user): bool
    {
        // Check status
        if (!in_array($approvalRequest->status, [
            ApprovalRequest::STATUS_SUBMITTED,
            ApprovalRequest::STATUS_IN_REVIEW
        ])) {
            return false;
        }

        // Check if user can approve at current step (same permission for reject)
        return $approvalRequest->canBeApprovedBy($user);
    }

    /**
     * Check if needs approver selection before approving
     * 
     * For some steps (like review, department_head_approval), we need to select
     * approvers for the NEXT step before we can approve the current step
     */
    protected function needsApproverSelection(ApprovalRequest $approvalRequest): bool
    {
        if (!$approvalRequest->current_step) {
            return false;
        }

        // Get next step
        $nextStep = $approvalRequest->getNextStepAfterApproval();
        if (!$nextStep) {
            return false; // Last step, no need to select approvers
        }

        // Check if next step has selected approvers
        $selectedApprovers = $approvalRequest->selected_approvers ?? [];
        if (!is_array($selectedApprovers)) {
            $selectedApprovers = json_decode($selectedApprovers, true) ?? [];
        }
        
        $nextStepApprovers = $selectedApprovers[$nextStep] ?? [];

        return empty($nextStepApprovers);
    }

    /**
     * Record approval in history
     */
    protected function recordApproval(ApprovalRequest $approvalRequest, User $approver, array $options = []): void
    {
        $history = $approvalRequest->approval_history ?? [];
        if (!is_array($history)) {
            $history = [];
        }

        $step = $approvalRequest->current_step;
        if ($step) {
            $history[$step] = [
                'approved_by' => $approver->id,
                'approved_at' => now()->toIso8601String(),
                'comment' => $options['comment'] ?? null,
                'signature_path' => $options['signature_path'] ?? null,
            ];
        }

        $approvalRequest->approval_history = $history;
        $approvalRequest->save();

        // Create ApprovalHistory record
        $level = $this->getLevelFromStep($approvalRequest, $step);
        $model = $approvalRequest->getModel();
        if ($model) {
            ApprovalHistory::create([
                'approvable_type' => get_class($model),
                'approvable_id' => $model->id,
                'user_id' => $approver->id,
                'action' => 'approved',
                'level' => $level,
                'workflow_status_before' => $approvalRequest->status,
                'workflow_status_after' => $this->getNextStatus($approvalRequest),
                'comment' => $options['comment'] ?? null,
            ]);
        }
    }

    /**
     * Record rejection in history
     */
    protected function recordRejection(ApprovalRequest $approvalRequest, User $rejector, string $reason, array $options = []): void
    {
        $history = $approvalRequest->approval_history ?? [];
        if (!is_array($history)) {
            $history = [];
        }

        $step = $approvalRequest->current_step;
        if ($step) {
            $history[$step] = [
                'rejected_by' => $rejector->id,
                'rejected_at' => now()->toIso8601String(),
                'rejection_reason' => $reason,
            ];
        }

        $approvalRequest->approval_history = $history;

        // Create ApprovalHistory record
        $level = $this->getLevelFromStep($approvalRequest, $step);
        $model = $approvalRequest->getModel();
        if ($model) {
            ApprovalHistory::create([
                'approvable_type' => get_class($model),
                'approvable_id' => $model->id,
                'user_id' => $rejector->id,
                'action' => 'rejected',
                'level' => $level,
                'workflow_status_before' => $approvalRequest->status,
                'workflow_status_after' => ApprovalRequest::STATUS_REJECTED,
                'comment' => $reason,
            ]);
        }
    }

    /**
     * Move to next step or complete
     */
    protected function moveToNextStep(ApprovalRequest $approvalRequest, User $approver): void
    {
        $nextStep = $approvalRequest->getNextStepAfterApproval();

        if ($nextStep) {
            // Move to next step
            $approvalRequest->current_step = $nextStep;
            $approvalRequest->status = ApprovalRequest::STATUS_IN_REVIEW;
        } else {
            // Last step completed, approve
            $approvalRequest->current_step = null;
            $approvalRequest->status = ApprovalRequest::STATUS_APPROVED;
        }

        $approvalRequest->save();
    }

    /**
     * Sync approval_requests data back to model (if needed)
     */
    protected function syncToModel(ApprovalRequest $approvalRequest): void
    {
        // Sync lại để model có thể lấy status từ approvalRequest
        $this->approvalRequestService->syncFromModel(
            $approvalRequest->getModel(),
            $approvalRequest->module_type
        );
    }

    /**
     * Get approval level from step
     */
    protected function getLevelFromStep(ApprovalRequest $approvalRequest, ?string $step): int
    {
        if (!$step || !$approvalRequest->approval_steps) {
            return 1;
        }

        $steps = $approvalRequest->approval_steps;
        $index = array_search($step, $steps);
        
        return $index !== false ? $index + 1 : 1;
    }

    /**
     * Get next status after approval
     */
    protected function getNextStatus(ApprovalRequest $approvalRequest): string
    {
        $nextStep = $approvalRequest->getNextStepAfterApproval();
        
        if ($nextStep) {
            return ApprovalRequest::STATUS_IN_REVIEW;
        }
        
        return ApprovalRequest::STATUS_APPROVED;
    }

    /**
     * Get reason why cannot approve
     */
    protected function getCannotApproveReason(ApprovalRequest $approvalRequest, User $user): string
    {
        if (!in_array($approvalRequest->status, [ApprovalRequest::STATUS_SUBMITTED, ApprovalRequest::STATUS_IN_REVIEW])) {
            return 'Trạng thái không hợp lệ: ' . $approvalRequest->status;
        }

        if (!$approvalRequest->canBeApprovedBy($user)) {
            return 'Bạn không có quyền phê duyệt ở bước này';
        }

        return 'Không xác định';
    }

    /**
     * Get reason why cannot reject
     */
    protected function getCannotRejectReason(ApprovalRequest $approvalRequest, User $user): string
    {
        if (!in_array($approvalRequest->status, [ApprovalRequest::STATUS_SUBMITTED, ApprovalRequest::STATUS_IN_REVIEW])) {
            return 'Trạng thái không hợp lệ: ' . $approvalRequest->status;
        }

        if (!$approvalRequest->canBeApprovedBy($user)) {
            return 'Bạn không có quyền từ chối ở bước này';
        }

        return 'Không xác định';
    }
}


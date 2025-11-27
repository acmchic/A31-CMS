<?php

namespace Modules\ApprovalWorkflow\Services;

use Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Services\WorkflowEngine;

/**
 * ApprovalWorkflowHandler
 * 
 * Wrapper service sử dụng WorkflowEngine để xử lý workflow
 * 
 * @deprecated Consider using WorkflowEngine directly
 */
class ApprovalWorkflowHandler
{
    protected $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    public function processApproval(
        ApprovalRequest $request,
        string $action,
        ?string $comment = null,
        ?array $selectedApprovers = null,
        $approver = null
    ): ApprovalRequest {
        return $this->workflowEngine->processApprovalStep($request, $action, $comment, $selectedApprovers, $approver);
    }

    public function approve(ApprovalRequest $request, $approver = null, ?array $options = null): ApprovalRequest
    {
        $comment = null;
        $selectedApprovers = null;
        
        if ($options && is_array($options)) {
            $comment = $options['comment'] ?? null;
            $selectedApprovers = $options['selected_approvers'] ?? null;
        }
        
        $userToPass = null;
        if ($approver) {
            if (is_object($approver)) {
                if (isset($approver->id) || method_exists($approver, 'id')) {
                    $userToPass = $approver;
                }
            }
        }
        
        if (!$userToPass) {
            $userToPass = backpack_user();
        }
        
        if (!$userToPass) {
            $userToPass = auth()->user();
        }
        
        \Log::info('ApprovalWorkflowHandler::approve', [
            'approver_type' => $approver ? get_class($approver) : 'null',
            'approver_id' => $approver && is_object($approver) ? ($approver->id ?? 'no_id') : 'N/A',
            'is_object' => is_object($approver) ? 'yes' : 'no',
            'userToPass_set' => $userToPass ? 'yes' : 'no',
            'userToPass_id' => $userToPass ? $userToPass->id : 'null',
            'backpack_user_id' => backpack_user() ? backpack_user()->id : 'null',
            'auth_id' => auth()->id(),
        ]);
        
        return $this->processApproval($request, 'approved', $comment, $selectedApprovers, $userToPass);
    }

    public function reject(ApprovalRequest $request, $approver = null, ?string $comment = null, ?array $options = null): ApprovalRequest
    {
        if ($approver instanceof \App\Models\User) {
            return $this->processApproval($request, 'rejected', $comment, null, $approver);
        }
        
        if (is_string($approver)) {
            $comment = $approver;
            $approver = null;
        }
        
        return $this->processApproval($request, 'rejected', $comment);
    }

    /**
     * Return request
     */
    public function return(ApprovalRequest $request, ?string $comment = null): ApprovalRequest
    {
        return $this->processApproval($request, 'returned', $comment);
    }

    /**
     * Cancel request
     */
    public function cancel(ApprovalRequest $request, ?string $comment = null): ApprovalRequest
    {
        return $this->processApproval($request, 'cancelled', $comment);
    }
}

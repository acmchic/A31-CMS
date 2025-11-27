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

    /**
     * Xử lý phê duyệt
     * 
     * @param ApprovalRequest $request
     * @param string $action
     * @param string|null $comment
     * @param array|null $selectedApprovers
     * @return ApprovalRequest
     */
    public function processApproval(
        ApprovalRequest $request,
        string $action,
        ?string $comment = null,
        ?array $selectedApprovers = null
    ): ApprovalRequest {
        return $this->workflowEngine->processApprovalStep($request, $action, $comment, $selectedApprovers);
    }

    /**
     * Alias for processApproval
     */
    public function approve(ApprovalRequest $request, ?string $comment = null, ?array $selectedApprovers = null): ApprovalRequest
    {
        return $this->processApproval($request, 'approved', $comment, $selectedApprovers);
    }

    /**
     * Reject request
     */
    public function reject(ApprovalRequest $request, ?string $comment = null): ApprovalRequest
    {
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

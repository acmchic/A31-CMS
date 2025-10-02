<?php

namespace Modules\ApprovalWorkflow\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\ApprovalWorkflow\Models\ApprovalHistory;
use App\Models\User;

/**
 * ApprovalService
 * 
 * Central service for handling approval workflows
 */
class ApprovalService
{
    /**
     * Approve model at current workflow level
     */
    public function approve(Model $model, User $approver, array $options = []): bool
    {
        if (!$model->canBeApproved()) {
            throw new \Exception('Model cannot be approved at current status: ' . $model->workflow_status);
        }

        $currentStatus = $model->workflow_status;
        $nextStatus = $model->getNextWorkflowStep();
        
        if (!$nextStatus) {
            throw new \Exception('No next workflow step defined for status: ' . $currentStatus);
        }

        // Determine which level we're approving
        $level = $this->getCurrentApprovalLevel($currentStatus);
        
        // Update model with approval info
        $updateData = [
            'workflow_status' => $nextStatus,
            "workflow_level{$level}_by" => $approver->id,
            "workflow_level{$level}_at" => now(),
        ];

        // Add signature path if provided
        if (isset($options['signature_path'])) {
            $updateData["workflow_level{$level}_signature"] = $options['signature_path'];
        }

        $model->update($updateData);

        // Create approval history record
        $this->createHistory($model, $approver, 'approved', $level, $options);

        return true;
    }

    /**
     * Reject model
     */
    public function reject(Model $model, User $approver, string $reason, array $options = []): bool
    {
        if (!$model->canBeRejected()) {
            throw new \Exception('Model cannot be rejected at current status: ' . $model->workflow_status);
        }

        $currentStatus = $model->workflow_status;
        $level = $this->getCurrentApprovalLevel($currentStatus);

        // Update model
        $model->update([
            'workflow_status' => 'rejected',
            'rejection_reason' => $reason,
            "workflow_level{$level}_by" => $approver->id,
            "workflow_level{$level}_at" => now(),
        ]);

        // Create approval history record
        $this->createHistory($model, $approver, 'rejected', $level, array_merge($options, [
            'reason' => $reason
        ]));

        return true;
    }

    /**
     * Approve with digital signature
     */
    public function approveWithSignature(
        Model $model, 
        User $approver, 
        string $certificatePin,
        array $options = []
    ): array {
        // Validate PIN
        $certificatePath = \App\Services\UserCertificateService::getUserCertificatePath($approver);
        
        if (!$certificatePath) {
            throw new \Exception('Không tìm thấy chứng thư số');
        }

        // Validate user PIN first
        if (!$approver->certificate_pin) {
            throw new \Exception(getUserTitle($approver) . ' chưa thiết lập PIN chữ ký số. Vui lòng vào trang Thông tin cá nhân để thiết lập PIN.');
        }
        
        if ($certificatePin !== $approver->certificate_pin) {
            throw new \Exception('Mã PIN không hợp lệ');
        }
        
        // Get certificate password from config (for .pfx file, NOT user PIN)
        $certificatePassword = config('approvalworkflow::approval.digital_signature.certificate_password', 'A31Factory2025');
        
        // Validate certificate file can be opened
        $validation = \App\Services\UserCertificateService::validateCertificate($certificatePath, $certificatePassword);
        
        if (!$validation['valid']) {
            throw new \Exception($validation['error']);
        }

        // Approve first
        $this->approve($model, $approver, $options);

        // Generate signed PDF
        $pdfService = app(PdfGeneratorService::class);
        $pdfPath = $pdfService->generateSignedPdf($model, $approver, $certificatePath, $certificatePassword);

        // Update model with signed PDF path
        $model->update([
            'signed_pdf_path' => $pdfPath
        ]);

        return [
            'success' => true,
            'pdf_path' => $pdfPath,
            'workflow_status' => $model->workflow_status
        ];
    }

    /**
     * Get current approval level from workflow status
     */
    protected function getCurrentApprovalLevel(string $status): int
    {
        if ($status === 'pending') {
            return 1;
        } elseif ($status === 'level1_approved') {
            return 2;
        } elseif ($status === 'level2_approved') {
            return 3;
        }
        
        return 1;
    }

    /**
     * Create approval history record
     */
    protected function createHistory(
        Model $model, 
        User $approver, 
        string $action,
        int $level,
        array $options = []
    ): ApprovalHistory {
        return ApprovalHistory::create([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->id,
            'user_id' => $approver->id,
            'action' => $action,
            'level' => $level,
            'workflow_status_before' => $options['status_before'] ?? null,
            'workflow_status_after' => $model->workflow_status,
            'comment' => $options['comment'] ?? null,
            'reason' => $options['reason'] ?? null,
            'metadata' => json_encode($options['metadata'] ?? []),
        ]);
    }

    /**
     * Get approval history for model
     */
    public function getHistory(Model $model)
    {
        return ApprovalHistory::where('approvable_type', get_class($model))
            ->where('approvable_id', $model->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}


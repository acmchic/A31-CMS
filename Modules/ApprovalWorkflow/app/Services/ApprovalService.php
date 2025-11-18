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

        // Special handling for EmployeeLeave with custom fields
        if ($model instanceof \Modules\PersonnelReport\Models\EmployeeLeave) {
            return $this->approveEmployeeLeave($model, $approver, $currentStatus, $nextStatus, $options);
        }

        // Default handling for other models
        $level = $this->getCurrentApprovalLevel($currentStatus);
        
        $updateData = [
            'workflow_status' => $nextStatus,
            "workflow_level{$level}_by" => $approver->id,
            "workflow_level{$level}_at" => now(),
        ];

        if (isset($options['signature_path'])) {
            $updateData["workflow_level{$level}_signature"] = $options['signature_path'];
        }

        $model->update($updateData);
        $this->createHistory($model, $approver, 'approved', $level, $options);

        return true;
    }

    /**
     * Approve EmployeeLeave with custom field mapping
     */
    protected function approveEmployeeLeave($model, $approver, $currentStatus, $nextStatus, $options)
    {
        // Ensure nextStatus is a string literal (not constant reference)
        if (is_string($nextStatus)) {
            $nextStatusValue = $nextStatus;
        } else {
            // If it's a constant, get its actual string value
            $nextStatusValue = (string) $nextStatus;
        }
        
        // Map workflow status to correct fields and ensure string values
        $updateData = [];
        
        switch ($currentStatus) {
            case \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_PENDING:
            case 'pending':
                $updateData['workflow_status'] = 'approved_by_department_head'; // Use string literal
                $updateData['approved_by_department_head'] = $approver->id;
                $updateData['approved_at_department_head'] = now();
                break;

            case \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD:
            case 'approved_by_department_head':
                $updateData['workflow_status'] = 'approved_by_reviewer'; // Use string literal
                $updateData['approved_by_reviewer'] = $approver->id;
                $updateData['approved_at_reviewer'] = now();
                break;

            case \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER:
            case 'approved_by_reviewer':
                $updateData['workflow_status'] = 'approved_by_director'; // Use string literal
                $updateData['approved_by_director'] = $approver->id;
                $updateData['approved_at_director'] = now();
                break;

            // Legacy support
            case 'approved_by_approver':
                $updateData['workflow_status'] = 'approved_by_reviewer'; // Use string literal
                $updateData['approved_by_reviewer'] = $approver->id;
                $updateData['approved_at_reviewer'] = now();
                break;
        }

        // If workflow_status not set, use nextStatus (fallback)
        if (!isset($updateData['workflow_status'])) {
            $updateData['workflow_status'] = $nextStatusValue;
        }

        if (isset($options['signature_path'])) {
            if ($currentStatus === \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_PENDING || $currentStatus === 'pending') {
                $updateData['approver_signature_path'] = $options['signature_path'];
            } elseif ($currentStatus === \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER || $currentStatus === 'approved_by_reviewer') {
                $updateData['director_signature_path'] = $options['signature_path'];
            }
        }

        // Use update() with explicit string values
        $model->update($updateData);
        
        $level = $this->getCurrentApprovalLevelForEmployeeLeave($currentStatus);
        $this->createHistory($model, $approver, 'approved', $level, $options);

        return true;
    }

    /**
     * Get approval level for EmployeeLeave
     */
    protected function getCurrentApprovalLevelForEmployeeLeave(string $status): int
    {
        $map = [
            \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_PENDING => 1,
            \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD => 2,
            \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER => 3,
            \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR => 4,
        ];

        return $map[$status] ?? 1;
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

        // Special handling for EmployeeLeave
        if ($model instanceof \Modules\PersonnelReport\Models\EmployeeLeave) {
            $updateData = [
                'workflow_status' => \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_REJECTED,
                'rejection_reason' => $reason,
            ];

            // Set rejection by field based on current step
            switch ($currentStatus) {
                case \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_PENDING:
                    $updateData['approved_by_department_head'] = $approver->id;
                    $updateData['approved_at_department_head'] = now();
                    break;
                case \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD:
                    $updateData['approved_by_reviewer'] = $approver->id;
                    $updateData['approved_at_reviewer'] = now();
                    break;
                case \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER:
                    $updateData['approved_by_director'] = $approver->id;
                    $updateData['approved_at_director'] = now();
                    break;
            }

            $model->update($updateData);
            $level = $this->getCurrentApprovalLevelForEmployeeLeave($currentStatus);
            $this->createHistory($model, $approver, 'rejected', $level, array_merge($options, ['reason' => $reason]));
            return true;
        }

        // Default handling
        $level = $this->getCurrentApprovalLevel($currentStatus);
        $model->update([
            'workflow_status' => 'rejected',
            'rejection_reason' => $reason,
            "workflow_level{$level}_by" => $approver->id,
            "workflow_level{$level}_at" => now(),
        ]);

        $this->createHistory($model, $approver, 'rejected', $level, array_merge($options, ['reason' => $reason]));
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
        } elseif ($status === 'level1_approved' || $status === 'approved_by_approver') {
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


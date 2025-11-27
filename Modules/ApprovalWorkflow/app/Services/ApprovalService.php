<?php

namespace Modules\ApprovalWorkflow\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\ApprovalWorkflow\Models\ApprovalHistory;
use Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Models\User;

/**
 * ApprovalService
 * 
 * Central service for handling approval workflows
 * Refactored to use ApprovalRequest as single source of truth
 */
class ApprovalService
{
    protected $workflowHandler;
    protected $approvalRequestService;

    public function __construct(
        ApprovalWorkflowHandler $workflowHandler,
        ApprovalRequestService $approvalRequestService
    ) {
        $this->workflowHandler = $workflowHandler;
        $this->approvalRequestService = $approvalRequestService;
    }

    /**
     * Approve model at current workflow level
     * 
     * @deprecated Use approveRequest() instead - this method is kept for backward compatibility
     */
    public function approve(Model $model, User $approver, array $options = []): bool
    {
        // Xác định module type TRƯỚC KHI query
        $moduleType = $this->getModuleTypeForModel($model);
        if (!$moduleType) {
            throw new \Exception('Không xác định được module type cho model: ' . get_class($model));
        }

        // Get or create ApprovalRequest - filter theo module_type
        $approvalRequest = ApprovalRequest::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->where('module_type', $moduleType)
            ->first();

        if (!$approvalRequest) {
            // Sync from model first
            $this->approvalRequestService->syncFromModel($model, $moduleType);
            
            // Reload approvalRequest - filter theo module_type
            $approvalRequest = ApprovalRequest::where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->where('module_type', $moduleType)
                ->first();
        }

        if (!$approvalRequest) {
            throw new \Exception('Không tìm thấy ApprovalRequest cho model này');
        }

        // Use ApprovalWorkflowHandler to approve
        return $this->workflowHandler->approve($approvalRequest, $approver, $options);
    }

    /**
     * Approve using ApprovalRequest directly (recommended)
     */
    public function approveRequest(ApprovalRequest $approvalRequest, User $approver, array $options = []): bool
    {
        return $this->workflowHandler->approve($approvalRequest, $approver, $options);
    }

    /**
     * @deprecated Use ApprovalWorkflowHandler instead
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

        // Sync với ApprovalRequest sau khi approve
        if (class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
            $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
            $service->syncFromModel($model, 'leave');
        }

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
     * @deprecated Use ApprovalWorkflowHandler instead
     */
    protected function approveVehicleRegistration($model, $approver, $currentStatus, $nextStatus, $options)
    {
        $approvalRequest = $model->approvalRequest;
        if (!$approvalRequest) {
            throw new \Exception('Không tìm thấy yêu cầu phê duyệt');
        }

        $currentStep = $approvalRequest->current_step;
        $status = $approvalRequest->status;
        
        // Step 2: department_head_approval - Trưởng phòng kế hoạch duyệt
        // Note: status can be 'submitted' or 'in_review' at this step
        if ($currentStep === 'department_head_approval' && in_array($status, ['submitted', 'in_review'])) {
            // Check if selected_approvers (directors) are already set
            $selectedApprovers = $approvalRequest->selected_approvers ?? [];
            if (!is_array($selectedApprovers)) {
                $selectedApprovers = json_decode($selectedApprovers, true) ?? [];
            }
            
            $directorApprovers = $selectedApprovers['director_approval'] ?? [];
            if (empty($directorApprovers)) {
                throw new \Exception('Vui lòng chọn người phê duyệt (Ban Giám đốc) trước khi phê duyệt');
            }
            
            // Update approval_history
            $approvalHistory = $approvalRequest->approval_history ?? [];
            if (!is_array($approvalHistory)) {
                $approvalHistory = json_decode($approvalHistory, true) ?? [];
            }
            
            $approvalHistory['department_head_approval'] = [
                'approved_by' => $approver->id,
                'approved_at' => now()->toIso8601String(),
                'comment' => $options['comment'] ?? null,
            ];
            $approvalRequest->approval_history = $approvalHistory;
            
            // Move to next step: director_approval
            $approvalRequest->current_step = 'director_approval';
            $approvalRequest->status = 'in_review';
            $approvalRequest->save();
            
            // Create approval history record
            $this->createHistory($model, $approver, 'approved', 2, $options);
            
            // Sync với ApprovalRequestService
            if (class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
                $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
                $service->syncFromModel($model, 'vehicle');
            }
            
            return true;
        }
        
        // Step 3: director_approval - Ban Giám đốc duyệt
        if ($currentStep === 'director_approval' && $status === 'in_review') {
            // Update approval_history
            $approvalHistory = $approvalRequest->approval_history ?? [];
            if (!is_array($approvalHistory)) {
                $approvalHistory = json_decode($approvalHistory, true) ?? [];
            }
            
            $approvalHistory['director_approval'] = [
                'approved_by' => $approver->id,
                'approved_at' => now()->toIso8601String(),
                'comment' => $options['comment'] ?? null,
            ];
            $approvalRequest->approval_history = $approvalHistory;
            
            // Move to approved
            $approvalRequest->current_step = null;
            $approvalRequest->status = 'approved';
            $approvalRequest->save();
            
            // Create approval history record
            $this->createHistory($model, $approver, 'approved', 3, $options);
            
            // Sync với ApprovalRequestService
            if (class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
                $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
                $service->syncFromModel($model, 'vehicle');
            }
            
            return true;
        }
        
        // Legacy support: fallback to old logic if approvalRequest doesn't have current_step
        $updateData = [];
        switch ($currentStatus) {
            case 'dept_review':
                $updateData['workflow_status'] = 'director_review';
                break;
            case 'director_review':
            case 'approved':
                if (!$model->director_approved_by) {
                    $updateData['workflow_status'] = 'approved';
                }
                break;
            case 'submitted':
                $updateData['workflow_status'] = 'dept_review';
                break;
        }

        if (!empty($updateData)) {
            $model->update($updateData);
            $level = $this->getCurrentApprovalLevelForVehicleRegistration($currentStatus);
            $this->createHistory($model, $approver, 'approved', $level, $options);
            
            // Sync với ApprovalRequestService
            if (class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
                $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
                $service->syncFromModel($model, 'vehicle');
            }
        }

        return true;
    }

    /**
     * Get approval level for VehicleRegistration
     * Level mapping:
     * - submitted (vehicle_picked) = level 1
     * - dept_review (department_head_approval) = level 2
     * - director_review (director_approval) = level 3
     */
    protected function getCurrentApprovalLevelForVehicleRegistration(string $status): int
    {
        $map = [
            'submitted' => 1,        // vehicle_picked
            'dept_review' => 2,      // department_head_approval
            'director_review' => 3,  // director_approval
        ];

        return $map[$status] ?? 1;
    }

    /**
     * Reject model
     * 
     * @deprecated Use rejectRequest() instead - this method is kept for backward compatibility
     */
    public function reject(Model $model, User $approver, string $reason, array $options = []): bool
    {
        // Xác định module type TRƯỚC KHI query
        $moduleType = $this->getModuleTypeForModel($model);
        if (!$moduleType) {
            throw new \Exception('Không xác định được module type cho model: ' . get_class($model));
        }

        // Get or create ApprovalRequest - filter theo module_type
        $approvalRequest = ApprovalRequest::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->where('module_type', $moduleType)
            ->first();

        if (!$approvalRequest) {
            // Sync from model first
            $this->approvalRequestService->syncFromModel($model, $moduleType);
            
            // Reload approvalRequest - filter theo module_type
            $approvalRequest = ApprovalRequest::where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->where('module_type', $moduleType)
                ->first();
        }

        if (!$approvalRequest) {
            throw new \Exception('Không tìm thấy ApprovalRequest cho model này');
        }

        // Use ApprovalWorkflowHandler to reject
        return $this->workflowHandler->reject($approvalRequest, $approver, $reason, $options);
    }

    /**
     * Reject using ApprovalRequest directly (recommended)
     */
    public function rejectRequest(ApprovalRequest $approvalRequest, User $approver, string $reason, array $options = []): bool
    {
        // ✅ Sửa: workflowHandler->reject() return ApprovalRequest, nhưng method này phải return bool
        // Signature của workflowHandler->reject() là: reject(ApprovalRequest $request, ?string $comment = null)
        $this->workflowHandler->reject($approvalRequest, $reason);
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

        // Xác định module type TRƯỚC KHI query
        $moduleType = $this->getModuleTypeForModel($model);
        if (!$moduleType) {
            throw new \Exception('Không xác định được module type cho model: ' . get_class($model));
        }

        // Get or create ApprovalRequest - QUAN TRỌNG: filter theo cả model_type, model_id VÀ module_type
        $approvalRequest = ApprovalRequest::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->where('module_type', $moduleType) // ⚠️ QUAN TRỌNG: filter theo module_type để tránh nhầm
            ->first();

        if (!$approvalRequest) {
            // Sync from model first
            try {
                $this->approvalRequestService->syncFromModel($model, $moduleType);
            } catch (\Exception $e) {
                \Log::error('Error syncing ApprovalRequest in approveWithSignature:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'module_type' => $moduleType
                ]);
                throw new \Exception('Không thể tạo yêu cầu phê duyệt: ' . $e->getMessage());
            }
            
            // Reload approvalRequest - QUAN TRỌNG: filter theo module_type
            $approvalRequest = ApprovalRequest::where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->where('module_type', $moduleType) // ⚠️ QUAN TRỌNG: filter theo module_type
                ->first();
        }

        if (!$approvalRequest) {
            \Log::error('ApprovalRequest not found after sync in approveWithSignature:', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'module_type' => $moduleType
            ]);
            throw new \Exception('Không tìm thấy yêu cầu phê duyệt. Vui lòng thử lại hoặc liên hệ quản trị viên.');
        }

        // Approve first (without signature)
        $this->workflowHandler->approve($approvalRequest, $approver, $options);

        // Generate signed PDF
        $pdfService = app(PdfGeneratorService::class);
        $pdfPath = $pdfService->generateSignedPdf($model, $approver, $certificatePath, $certificatePassword);

        // Update ApprovalRequest with signed PDF path
        $approvalRequest->signed_pdf_path = $pdfPath;
        $approvalRequest->save();

        // ✅ Sửa: Không update signed_pdf_path vào model nữa vì đã chuyển sang approval_requests
        // Chỉ update nếu model có cột này (backward compatibility cho các model cũ)
        // if (in_array('signed_pdf_path', $model->getFillable())) {
        //     $model->update(['signed_pdf_path' => $pdfPath]);
        // }

        // Sync lại để đảm bảo đồng bộ
        $this->approvalRequestService->syncFromModel($model, $approvalRequest->module_type);

        return [
            'success' => true,
            'pdf_path' => $pdfPath,
            'status' => $approvalRequest->status,
            'current_step' => $approvalRequest->current_step,
        ];
    }

    /**
     * Get module type for model
     */
    protected function getModuleTypeForModel(Model $model): ?string
    {
        if ($model instanceof \Modules\PersonnelReport\Models\EmployeeLeave) {
            return 'leave';
        }
        
        if ($model instanceof \Modules\VehicleRegistration\Models\VehicleRegistration) {
            return 'vehicle';
        }
        
        if ($model instanceof \Modules\ProductionManagement\Models\MaterialPlan) {
            return 'material_plan';
        }
        
        return null;
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


<?php

namespace Modules\ApprovalWorkflow\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\ApprovalWorkflow\Models\ApprovalRequest;
use Modules\ApprovalWorkflow\Models\ApprovalFlow;

/**
 * ApprovalRequestService
 * 
 * Service để sync/create ApprovalRequest từ model
 */
class ApprovalRequestService
{
    /**
     * Sync hoặc tạo ApprovalRequest từ model
     * 
     * @param Model $model
     * @param string $moduleType 'leave', 'vehicle', 'material', ...
     * @param array $options
     * @return ApprovalRequest
     */
    public function syncFromModel(Model $model, string $moduleType, array $options = []): ApprovalRequest
    {
        // Tìm ApprovalRequest hiện có - QUAN TRỌNG: filter theo module_type
        $approvalRequest = ApprovalRequest::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->where('module_type', $moduleType) // ⚠️ QUAN TRỌNG: filter theo module_type
            ->first();

        // Lấy flow cho module
        $flow = ApprovalFlow::getByModuleType($moduleType);
        if (!$flow) {
            throw new \Exception("Không tìm thấy workflow flow cho module: {$moduleType}");
        }

        // Lấy approval steps từ flow
        $steps = $flow->steps()->orderBy('order')->get();
        $stepNames = $steps->pluck('step')->toArray();

        // Xác định current_step và status ban đầu
        // Khi tạo mới: status = 'submitted', current_step = bước đầu tiên
        // Khi đã có: giữ nguyên status và current_step
        if ($approvalRequest) {
            // Đã có ApprovalRequest: giữ nguyên
            $currentStep = $approvalRequest->current_step;
            $currentStepIndex = $approvalRequest->current_step_index;
            $status = $approvalRequest->status;
        } else {
            // Tạo mới: bắt đầu từ bước đầu tiên
            $currentStep = $stepNames[0] ?? null;
            $currentStepIndex = 0;
            $status = 'submitted'; // Mặc định là submitted khi tạo mới
        }

        // Tạo title và description
        $title = $this->generateTitle($model, $moduleType);
        $description = $this->generateDescription($model, $moduleType);

        // Tạo hoặc cập nhật ApprovalRequest
        if ($approvalRequest) {
            // ✅ Update: Không update created_by (giữ nguyên giá trị cũ)
            $data = [
                'module_type' => $moduleType,
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'flow_id' => $flow->id,
                // 'created_by' => không update, giữ nguyên
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'approval_steps' => $stepNames,
                'current_step' => $currentStep,
                'current_step_index' => $currentStepIndex,
                'selected_approvers' => $approvalRequest->selected_approvers ?? null,
                'approval_history' => $approvalRequest->approval_history ?? null,
                'metadata' => $this->extractMetadata($model, $moduleType),
            ];
            $approvalRequest->update($data);
            return $approvalRequest->fresh();
        } else {
            // ✅ Create: Set created_by khi tạo mới, đảm bảo là integer
            $createdBy = $model->created_by ?? backpack_user()?->id ?? 1;
            // Đảm bảo created_by là integer, không phải string
            if (is_string($createdBy) && $createdBy !== 'system' && is_numeric($createdBy)) {
                $createdBy = (int)$createdBy;
            } elseif ($createdBy === 'system' || !is_numeric($createdBy)) {
                $createdBy = backpack_user()?->id ?? 1;
            } else {
                $createdBy = (int)$createdBy;
            }
            
            $data = [
                'module_type' => $moduleType,
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'flow_id' => $flow->id,
                'created_by' => $createdBy,
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'approval_steps' => $stepNames,
                'current_step' => $currentStep,
                'current_step_index' => $currentStepIndex,
                'selected_approvers' => null,
                'approval_history' => null,
                'metadata' => $this->extractMetadata($model, $moduleType),
            ];
            return ApprovalRequest::create($data);
        }
    }

    /**
     * Generate title for ApprovalRequest
     */
    protected function generateTitle(Model $model, string $moduleType): string
    {
        switch ($moduleType) {
            case 'leave':
                if ($model instanceof \Modules\PersonnelReport\Models\EmployeeLeave) {
                    $employeeName = $model->employee?->name ?? 'N/A';
                    $leaveType = $model->leave_type_text ?? 'Nghỉ phép';
                    return "Đơn xin {$leaveType} - {$employeeName}";
                }
                break;

            case 'vehicle':
                if ($model instanceof \Modules\VehicleRegistration\Models\VehicleRegistration) {
                    $employeeName = $model->employee?->name ?? 'N/A';
                    return "Đăng ký xe - {$employeeName}";
                }
                break;
        }

        return "Yêu cầu phê duyệt #{$model->id}";
    }

    /**
     * Generate description for ApprovalRequest
     */
    protected function generateDescription(Model $model, string $moduleType): ?string
    {
        switch ($moduleType) {
            case 'leave':
                if ($model instanceof \Modules\PersonnelReport\Models\EmployeeLeave) {
                    $fromDate = $model->from_date?->format('d/m/Y') ?? 'N/A';
                    $toDate = $model->to_date?->format('d/m/Y') ?? 'N/A';
                    return "Từ ngày: {$fromDate} đến {$toDate}";
                }
                break;

            case 'vehicle':
                if ($model instanceof \Modules\VehicleRegistration\Models\VehicleRegistration) {
                    $departureDate = $model->departure_date?->format('d/m/Y') ?? 'N/A';
                    $returnDate = $model->return_date?->format('d/m/Y') ?? 'N/A';
                    return "Ngày đi: {$departureDate}, Ngày về: {$returnDate}";
                }
                break;
        }

        return null;
    }

    /**
     * Extract metadata from model
     */
    protected function extractMetadata(Model $model, string $moduleType): array
    {
        $metadata = [];

        switch ($moduleType) {
            case 'leave':
                if ($model instanceof \Modules\PersonnelReport\Models\EmployeeLeave) {
                    $metadata = [
                        'employee_id' => $model->employee_id,
                        'leave_type' => $model->leave_type,
                        'from_date' => $model->from_date?->toDateString(),
                        'to_date' => $model->to_date?->toDateString(),
                        'location' => $model->location,
                        'note' => $model->note,
                    ];
                }
                break;

            case 'vehicle':
                if ($model instanceof \Modules\VehicleRegistration\Models\VehicleRegistration) {
                    $metadata = [
                        'employee_id' => $model->employee_id,
                        'departure_date' => $model->departure_date?->toDateString(),
                        'return_date' => $model->return_date?->toDateString(),
                        'purpose' => $model->purpose,
                    ];
                }
                break;
        }

        return $metadata;
    }
}

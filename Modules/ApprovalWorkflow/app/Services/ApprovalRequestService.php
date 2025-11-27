<?php

namespace Modules\ApprovalWorkflow\Services;

use Modules\ApprovalWorkflow\Models\ApprovalRequest;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * Service để quản lý ApprovalRequest - đồng bộ với các module
 */
class ApprovalRequestService
{
    /**
     * Tạo hoặc cập nhật ApprovalRequest từ model
     */
    public function syncFromModel(Model $model, string $moduleType, array $options = [])
    {
        $approvalRequest = ApprovalRequest::where('model_type', get_class($model))
            ->where('model_id', $model->id)
            ->first();

        if (!$approvalRequest) {
            $approvalRequest = new ApprovalRequest();
            $approvalRequest->model_type = get_class($model);
            $approvalRequest->model_id = $model->id;
            $approvalRequest->module_type = $moduleType;
        }

        // Map workflow_status cũ sang status mới và approval_steps
        $mapping = $this->mapWorkflowStatus($model, $moduleType);
        
        $approvalRequest->status = $mapping['status'];
        $approvalRequest->approval_steps = $mapping['approval_steps'];
        $approvalRequest->current_step = $mapping['current_step'];
        
        // Set title và description
        $approvalRequest->title = $options['title'] ?? $this->getDefaultTitle($model, $moduleType);
        $approvalRequest->description = $options['description'] ?? null;
        
        // Set created_by - chỉ set khi tạo mới, không update khi đã có
        if (!$approvalRequest->exists || !$approvalRequest->created_by) {
            // Tìm user ID từ model
            $userId = null;
            
            // Kiểm tra các field có thể chứa user ID
            if (isset($model->nguoi_lap_id) && is_numeric($model->nguoi_lap_id)) {
                $userId = (int)$model->nguoi_lap_id;
            } elseif (isset($model->user_id) && is_numeric($model->user_id)) {
                $userId = (int)$model->user_id;
            } elseif (isset($model->created_by) && is_numeric($model->created_by)) {
                $userId = (int)$model->created_by;
            } elseif (isset($model->employee_id) && is_numeric($model->employee_id)) {
                // Nếu có employee_id, tìm user từ employee
                if (method_exists($model, 'employee') && $model->employee) {
                    $employee = $model->employee;
                    if (isset($employee->user_id) && is_numeric($employee->user_id)) {
                        $userId = (int)$employee->user_id;
                    }
                }
            }
            
            // Fallback: dùng current user
            if (!$userId) {
                $userId = backpack_user()->id ?? 1;
            }
            
            $approvalRequest->created_by = $userId;
        }

        // Set selected_approvers
        if (isset($model->selected_approvers)) {
            $selectedApprovers = is_array($model->selected_approvers) 
                ? $model->selected_approvers 
                : json_decode($model->selected_approvers, true);
            
            // Map selected_approvers theo approval_steps
            $approvalRequest->selected_approvers = $this->mapSelectedApprovers(
                $selectedApprovers, 
                $moduleType, 
                $approvalRequest->approval_steps
            );
        }

        // Set metadata
        $approvalRequest->metadata = $options['metadata'] ?? $this->getDefaultMetadata($model, $moduleType);

        // Sync approval_history từ ApprovalHistory
        $approvalRequest->approval_history = $this->syncApprovalHistory($model);
        
        // Sync signed_pdf_path từ model
        if (isset($model->signed_pdf_path)) {
            $approvalRequest->signed_pdf_path = $model->signed_pdf_path;
        }

        // Sync rejection data nếu model bị rejected
        if ($model->workflow_status === 'rejected' || $approvalRequest->status === 'rejected') {
            // Sync rejected_by
            if (isset($model->rejected_by) && is_numeric($model->rejected_by)) {
                $approvalRequest->rejected_by = (int)$model->rejected_by;
            } elseif (isset($model->department_approved_by) && is_numeric($model->department_approved_by) && isset($model->rejection_level) && $model->rejection_level === 'department') {
                // Nếu rejected ở department level, dùng department_approved_by
                $approvalRequest->rejected_by = (int)$model->department_approved_by;
            } elseif (isset($model->director_approved_by) && is_numeric($model->director_approved_by) && isset($model->rejection_level) && $model->rejection_level === 'director') {
                // Nếu rejected ở director level, dùng director_approved_by
                $approvalRequest->rejected_by = (int)$model->director_approved_by;
            } else {
                // Tìm từ approval_history
                $history = $approvalRequest->approval_history;
                if (is_array($history)) {
                    foreach ($history as $stepName => $stepData) {
                        if (is_array($stepData) && isset($stepData['action']) && $stepData['action'] === 'rejected' && isset($stepData['approved_by'])) {
                            $approvalRequest->rejected_by = (int)$stepData['approved_by'];
                            break;
                        }
                    }
                }
            }

            // Sync rejected_at
            if (isset($model->rejected_at)) {
                $approvalRequest->rejected_at = $model->rejected_at;
            } elseif (isset($model->department_approved_at) && isset($model->rejection_level) && $model->rejection_level === 'department') {
                $approvalRequest->rejected_at = $model->department_approved_at;
            } elseif (isset($model->director_approved_at) && isset($model->rejection_level) && $model->rejection_level === 'director') {
                $approvalRequest->rejected_at = $model->director_approved_at;
            } else {
                // Tìm từ approval_history
                $history = $approvalRequest->approval_history;
                if (is_array($history)) {
                    foreach ($history as $stepName => $stepData) {
                        if (is_array($stepData) && isset($stepData['action']) && $stepData['action'] === 'rejected' && isset($stepData['approved_at'])) {
                            $approvalRequest->rejected_at = \Carbon\Carbon::parse($stepData['approved_at']);
                            break;
                        }
                    }
                }
            }

            // Sync rejection_reason
            if (isset($model->rejection_reason) && !empty($model->rejection_reason)) {
                $approvalRequest->rejection_reason = $model->rejection_reason;
            } else {
                // Tìm từ approval_history
                $history = $approvalRequest->approval_history;
                if (is_array($history)) {
                    foreach ($history as $stepName => $stepData) {
                        if (is_array($stepData) && isset($stepData['action']) && $stepData['action'] === 'rejected') {
                            // Ưu tiên lấy từ comment, nếu không có thì lấy từ reason
                            if (isset($stepData['comment']) && !empty($stepData['comment'])) {
                                $approvalRequest->rejection_reason = $stepData['comment'];
                                break;
                            } elseif (isset($stepData['reason']) && !empty($stepData['reason'])) {
                                $approvalRequest->rejection_reason = $stepData['reason'];
                                break;
                            }
                        }
                    }
                }
            }

            // Sync rejection_step - xác định step nào bị từ chối
            if (isset($model->rejection_level)) {
                // Map rejection_level sang rejection_step
                if ($moduleType === 'vehicle') {
                    if ($model->rejection_level === 'department') {
                        $approvalRequest->rejection_step = 'department_head_approval';
                    } elseif ($model->rejection_level === 'director') {
                        $approvalRequest->rejection_step = 'director_approval';
                    }
                } elseif ($moduleType === 'leave') {
                    // Logic cho leave nếu cần
                    if (isset($model->rejection_level)) {
                        // Map tương tự
                    }
                }
            } elseif ($approvalRequest->current_step) {
                // Nếu có current_step, dùng nó làm rejection_step
                $approvalRequest->rejection_step = $approvalRequest->current_step;
            } else {
                // Tìm từ approval_history
                $history = $approvalRequest->approval_history;
                if (is_array($history)) {
                    foreach ($history as $stepName => $stepData) {
                        if (isset($stepData['action']) && $stepData['action'] === 'rejected') {
                            $approvalRequest->rejection_step = $stepName;
                            break;
                        }
                    }
                }
            }
        } else {
            // Nếu không bị rejected, clear các field rejection
            $approvalRequest->rejected_by = null;
            $approvalRequest->rejected_at = null;
            $approvalRequest->rejection_reason = null;
            $approvalRequest->rejection_step = null;
        }

        $approvalRequest->save();

        return $approvalRequest;
    }

    /**
     * Sync approval_history từ ApprovalHistory table
     */
    protected function syncApprovalHistory(Model $model): array
    {
        $history = [];
        
        if (class_exists(\Modules\ApprovalWorkflow\Models\ApprovalHistory::class)) {
            $histories = \Modules\ApprovalWorkflow\Models\ApprovalHistory::where('approvable_type', get_class($model))
                ->where('approvable_id', $model->id)
                ->orderBy('created_at', 'asc')
                ->get();
            
            foreach ($histories as $h) {
                $step = $this->getStepFromLevel($h->level, $model);
                if ($step) {
                    $history[$step] = [
                        'approved_by' => $h->user_id,
                        'approved_at' => $h->created_at ? $h->created_at->toDateTimeString() : null,
                        'comment' => $h->comment,
                        'signature_path' => $this->getSignaturePathFromModel($model, $h->level),
                        'action' => $h->action,
                        'workflow_status_before' => $h->workflow_status_before,
                        'workflow_status_after' => $h->workflow_status_after,
                    ];
                }
            }
        }
        
        return $history;
    }

    /**
     * Get step name from approval level
     */
    protected function getStepFromLevel(int $level, Model $model): ?string
    {
        // Map level to step based on model type
        if ($model instanceof \Modules\PersonnelReport\Models\EmployeeLeave) {
            $map = [
                1 => 'department_head_approval',
                2 => 'review',
                3 => 'director_approval',
            ];
            return $map[$level] ?? null;
        }
        
        if ($model instanceof \Modules\VehicleRegistration\Models\VehicleRegistration) {
            $map = [
                1 => 'vehicle_picked',
                2 => 'department_head_approval',
                3 => 'director_approval',
            ];
            return $map[$level] ?? null;
        }
        
        if ($model instanceof \Modules\ProductionManagement\Models\MaterialPlan) {
            $map = [
                1 => 'review',
                2 => 'director_approval',
            ];
            return $map[$level] ?? null;
        }
        
        return null;
    }

    /**
     * Get signature path from model based on level
     */
    protected function getSignaturePathFromModel(Model $model, int $level): ?string
    {
        if ($model instanceof \Modules\PersonnelReport\Models\EmployeeLeave) {
            if ($level === 1) {
                return $model->approver_signature_path ?? null;
            } elseif ($level === 3) {
                return $model->director_signature_path ?? null;
            }
        }
        
        if ($model instanceof \Modules\VehicleRegistration\Models\VehicleRegistration) {
            // Level 1: vehicle_picked (không có signature)
            // Level 2: department_head_approval (Trưởng phòng kế hoạch)
            // Level 3: director_approval (BGD)
            if ($level === 2) {
                return $model->digital_signature_dept ?? null;
            } elseif ($level === 3) {
                return $model->digital_signature_director ?? null;
            }
        }
        
        return null;
    }

    /**
     * Map workflow_status cũ sang status mới và approval_steps
     */
    protected function mapWorkflowStatus(Model $model, string $moduleType): array
    {
        $oldStatus = $model->workflow_status ?? 'draft';
        
        switch ($moduleType) {
            case 'leave':
                return $this->mapLeaveStatus($oldStatus);
            
            case 'vehicle':
                return $this->mapVehicleStatus($oldStatus);
            
            case 'material_plan':
                return $this->mapMaterialPlanStatus($oldStatus);
            
            default:
                return [
                    'status' => ApprovalRequest::STATUS_DRAFT,
                    'approval_steps' => [],
                    'current_step' => null,
                ];
        }
    }

    /**
     * Map status cho Leave (EmployeeLeave)
     * Approval steps: department_head_approval -> review -> director_approval
     */
    protected function mapLeaveStatus(string $oldStatus): array
    {
        $steps = ['department_head_approval', 'review', 'director_approval'];
        
        $mapping = [
            'pending' => [
                'status' => ApprovalRequest::STATUS_SUBMITTED,
                'current_step' => 'department_head_approval',
            ],
            'approved_by_department_head' => [
                'status' => ApprovalRequest::STATUS_IN_REVIEW,
                'current_step' => 'review',
            ],
            'approved_by_reviewer' => [
                'status' => ApprovalRequest::STATUS_IN_REVIEW,
                'current_step' => 'director_approval',
            ],
            'approved_by_director' => [
                'status' => ApprovalRequest::STATUS_APPROVED,
                'current_step' => null,
            ],
            'rejected' => [
                'status' => ApprovalRequest::STATUS_REJECTED,
                'current_step' => null,
            ],
        ];

        $result = $mapping[$oldStatus] ?? [
            'status' => ApprovalRequest::STATUS_DRAFT,
            'current_step' => null,
        ];

        return [
            'status' => $result['status'],
            'approval_steps' => $steps,
            'current_step' => $result['current_step'],
        ];
    }

    /**
     * Map status cho Vehicle
     * Approval steps: vehicle_picked -> department_head_approval -> director_approval
     * Workflow: submitted (chọn xe) -> dept_review (Trưởng phòng kế hoạch) -> director_review (BGD) -> approved
     */
    protected function mapVehicleStatus(string $oldStatus): array
    {
        $steps = ['vehicle_picked', 'department_head_approval', 'director_approval'];
        
        $mapping = [
            'draft' => [
                'status' => ApprovalRequest::STATUS_SUBMITTED,
                'current_step' => 'vehicle_picked',
            ],
            'submitted' => [
                'status' => ApprovalRequest::STATUS_SUBMITTED,
                'current_step' => 'vehicle_picked',
            ],
            'dept_review' => [
                'status' => ApprovalRequest::STATUS_IN_REVIEW,
                'current_step' => 'department_head_approval',
            ],
            'director_review' => [
                'status' => ApprovalRequest::STATUS_IN_REVIEW,
                'current_step' => 'director_approval',
            ],
            'approved' => [
                'status' => ApprovalRequest::STATUS_APPROVED,
                'current_step' => null,
            ],
            'rejected' => [
                'status' => ApprovalRequest::STATUS_REJECTED,
                'current_step' => null,
            ],
        ];

        $result = $mapping[$oldStatus] ?? [
            // Default: khi tạo mới, tự động set thành submitted để có thể phân xe
            'status' => ApprovalRequest::STATUS_SUBMITTED,
            'current_step' => 'vehicle_picked',
        ];

        return [
            'status' => $result['status'],
            'approval_steps' => $steps,
            'current_step' => $result['current_step'],
        ];
    }

    /**
     * Map status cho MaterialPlan
     * Approval steps: review -> director_approval
     */
    protected function mapMaterialPlanStatus(string $oldStatus): array
    {
        $steps = ['review', 'director_approval'];
        
        $mapping = [
            'pending' => [
                'status' => ApprovalRequest::STATUS_SUBMITTED,
                'current_step' => 'review',
            ],
            'approved_by_department_head' => [
                'status' => ApprovalRequest::STATUS_IN_REVIEW,
                'current_step' => 'director_approval',
            ],
            'approved_by_reviewer' => [
                'status' => ApprovalRequest::STATUS_IN_REVIEW,
                'current_step' => 'director_approval',
            ],
            'approved' => [
                'status' => ApprovalRequest::STATUS_APPROVED,
                'current_step' => null,
            ],
            'rejected' => [
                'status' => ApprovalRequest::STATUS_REJECTED,
                'current_step' => null,
            ],
        ];

        $result = $mapping[$oldStatus] ?? [
            'status' => ApprovalRequest::STATUS_DRAFT,
            'current_step' => null,
        ];

        return [
            'status' => $result['status'],
            'approval_steps' => $steps,
            'current_step' => $result['current_step'],
        ];
    }

    /**
     * Map selected_approvers theo approval_steps
     */
    protected function mapSelectedApprovers($selectedApprovers, string $moduleType, array $steps): array
    {
        if (empty($selectedApprovers) || empty($steps)) {
            return [];
        }

        $mapped = [];

        switch ($moduleType) {
            case 'leave':
                // Leave không dùng selected_approvers, dùng role-based
                break;
            
            case 'vehicle':
                // Vehicle: selected_approvers cho director_approval
                if (!empty($selectedApprovers)) {
                    $mapped['director_approval'] = $selectedApprovers;
                }
                break;
            
            case 'material_plan':
                // MaterialPlan: selected_approvers cho director_approval
                if (!empty($selectedApprovers)) {
                    $mapped['director_approval'] = $selectedApprovers;
                }
                break;
        }

        return $mapped;
    }

    /**
     * Get default title
     */
    protected function getDefaultTitle(Model $model, string $moduleType): string
    {
        switch ($moduleType) {
            case 'leave':
                if (method_exists($model, 'employee') && $model->employee) {
                    return "Đơn nghỉ phép - {$model->employee->name}";
                }
                return "Đơn nghỉ phép #{$model->id}";
            
            case 'vehicle':
                if (method_exists($model, 'vehicle') && $model->vehicle) {
                    return "Đăng ký xe - {$model->vehicle->name}";
                }
                return "Đăng ký xe #{$model->id}";
            
            case 'material_plan':
                return "Phương án vật tư: {$model->ten_khi_tai}" . 
                       ($model->ky_hieu_khi_tai ? " ({$model->ky_hieu_khi_tai})" : '');
            
            default:
                return "Yêu cầu phê duyệt #{$model->id}";
        }
    }

    /**
     * Get default metadata
     */
    protected function getDefaultMetadata(Model $model, string $moduleType): array
    {
        // Trả về các trường quan trọng của model để hiển thị trong approval center
        return [];
    }
}


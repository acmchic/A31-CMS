<?php

namespace App\Services;

use Modules\ApprovalWorkflow\Models\ApprovalRequest;
use Illuminate\Support\Facades\Log;

/**
 * WorkflowEngine
 * 
 * Core workflow engine xử lý phê duyệt cho từng module
 * 
 * ⚠️ QUAN TRỌNG: Mỗi module phải có BLOCK CODE RIÊNG BIỆT
 * Tuyệt đối không dùng logic chung, không auto-next-step
 */
class WorkflowEngine
{
    /**
     * Xử lý bước phê duyệt
     * 
     * @param ApprovalRequest $request
     * @param string $action 'approved' | 'rejected' | 'returned' | 'cancelled'
     * @param string|null $comment
     * @param array|null $selectedApprovers [user_id1, user_id2, ...] - Dùng cho modal chọn người duyệt
     * @return ApprovalRequest
     * @throws \Exception
     */
    public function processApprovalStep(
        ApprovalRequest $request,
        string $action,
        ?string $comment = null,
        ?array $selectedApprovers = null
    ): ApprovalRequest {
        // Validate action
        if (!in_array($action, ['approved', 'rejected', 'returned', 'cancelled'])) {
            throw new \Exception("Invalid action: {$action}");
        }

        // Xử lý theo từng module - TÁCH BIỆT HOÀN TOÀN
        switch ($request->module_type) {
            case 'leave':
                return $this->handleLeaveWorkflow($request, $action, $comment, $selectedApprovers);

            case 'vehicle':
                return $this->handleVehicleWorkflow($request, $action, $comment, $selectedApprovers);

            case 'material':
                return $this->handleMaterialWorkflow($request, $action, $comment, $selectedApprovers);

            default:
                throw new \Exception("Unsupported module type: {$request->module_type}");
        }
    }

    /**
     * Xử lý workflow cho module LEAVE
     * 
     * Flow: department_head_approval → review → director_approval → approved
     * 
     * ⚠️ KHÔNG có modal
     * ⚠️ KHÔNG được nhảy thẳng sang director
     * ⚠️ KHÔNG được dùng logic generic
     */
    protected function handleLeaveWorkflow(
        ApprovalRequest $request,
        string $action,
        ?string $comment,
        ?array $selectedApprovers
    ): ApprovalRequest {
        $currentStep = $request->current_step;
        $statusBefore = $request->status;

        // Xử lý từ chối/trả lại/hủy
        if (in_array($action, ['rejected', 'returned', 'cancelled'])) {
            return $this->handleRejection($request, $action, $comment);
        }

        // Xử lý duyệt theo từng bước - LOGIC CỨNG CHO LEAVE
        if ($currentStep === 'department_head_approval') {
            // TP duyệt → sang bước review
            $request->current_step = 'review';
            $request->current_step_index = 1;
            $request->status = 'in_review';
            $this->saveHistory($request, $currentStep, $action, $comment, $statusBefore, $request->status);
            $request->save();
            return $request;
        }

        if ($currentStep === 'review') {
            // Review duyệt → sang bước director
            $request->current_step = 'director_approval';
            $request->current_step_index = 2;
            $request->status = 'in_review';
            $this->saveHistory($request, $currentStep, $action, $comment, $statusBefore, $request->status);
            $request->save();
            return $request;
        }

        if ($currentStep === 'director_approval') {
            // Director duyệt → hoàn tất
            $request->status = 'approved';
            $this->saveHistory($request, $currentStep, $action, $comment, $statusBefore, $request->status);
            $request->save();
            return $request;
        }

        throw new \Exception("Invalid current_step for leave workflow: {$currentStep}");
    }

    /**
     * Xử lý workflow cho module VEHICLE
     * 
     * Flow: vehicle_picked → department_head_approval → [MODAL CHỌN BGĐ] → director_approval → approved
     * 
     * ⚠️ TUYỆT ĐỐI phải mở modal chọn BGĐ sau khi TP duyệt
     * ⚠️ TUYỆT ĐỐI phải đặt current_step = director_approval sau khi chọn BGĐ
     * ⚠️ KHÔNG có bước review
     * ⚠️ KHÔNG quay lại bước cũ
     */
    protected function handleVehicleWorkflow(
        ApprovalRequest $request,
        string $action,
        ?string $comment,
        ?array $selectedApprovers
    ): ApprovalRequest {
        $currentStep = $request->current_step;
        $statusBefore = $request->status;

        // Xử lý từ chối/trả lại/hủy
        if (in_array($action, ['rejected', 'returned', 'cancelled'])) {
            return $this->handleRejection($request, $action, $comment);
        }

        // Xử lý duyệt theo từng bước - LOGIC CỨNG CHO VEHICLE
        if ($currentStep === 'vehicle_picked') {
            // vehicle_picked → sang bước trưởng phòng KH
            $request->current_step = 'department_head_approval';
            $request->current_step_index = 1;
            $request->status = 'in_review';
            $this->saveHistory($request, $currentStep, $action, $comment, $statusBefore, $request->status);
            $request->save();
            return $request;
        }

        if ($currentStep === 'department_head_approval') {
            // TP duyệt → PHẢI mở modal chọn người BGĐ
            if (empty($selectedApprovers)) {
                throw new \Exception('director selection required');
            }

            // Cập nhật BGĐ được chọn
            $selectedApproversData = $request->selected_approvers ?? [];
            $selectedApproversData['director_approval'] = [
                'selected_by' => auth()->id(),
                'selected_at' => now()->toIso8601String(),
                'users' => $selectedApprovers
            ];
            $request->selected_approvers = $selectedApproversData;

            // Chuyển sang bước giám đốc
            $request->current_step = 'director_approval';
            $request->current_step_index = 2;
            $request->status = 'in_review';
            $this->saveHistory($request, $currentStep, $action, $comment, $statusBefore, $request->status);
            $request->save();
            return $request;
        }

        if ($currentStep === 'director_approval') {
            // Director duyệt → hoàn tất
            $request->status = 'approved';
            $this->saveHistory($request, $currentStep, $action, $comment, $statusBefore, $request->status);
            $request->save();
            return $request;
        }

        throw new \Exception("Invalid current_step for vehicle workflow: {$currentStep}");
    }

    /**
     * Xử lý workflow cho module MATERIAL
     * 
     * TODO: Implement logic cho material workflow
     */
    protected function handleMaterialWorkflow(
        ApprovalRequest $request,
        string $action,
        ?string $comment,
        ?array $selectedApprovers
    ): ApprovalRequest {
        // TODO: Implement material workflow logic
        throw new \Exception("Material workflow not yet implemented");
    }

    /**
     * Xử lý từ chối/trả lại/hủy
     */
    protected function handleRejection(
        ApprovalRequest $request,
        string $action,
        ?string $comment
    ): ApprovalRequest {
        $currentStep = $request->current_step;
        $statusBefore = $request->status;

        $rejectionReason = $comment;
        if (is_string($rejectionReason)) {
            $decoded = json_decode($rejectionReason, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rejectionReason = 'Dữ liệu không hợp lệ';
            } else {
                $rejectionReason = trim($rejectionReason);
            }
        } else {
            $rejectionReason = is_string($comment) ? trim($comment) : '';
        }

        $request->status = $action;
        $request->rejected_by = auth()->id();
        $request->rejected_at = now();
        $request->rejection_reason = $rejectionReason;
        $request->rejection_step = $currentStep;

        $this->saveHistory($request, $currentStep, $action, $comment, $statusBefore, $request->status);
        $request->save();

        return $request;
    }

    /**
     * Lưu lịch sử phê duyệt vào approval_history
     */
    protected function saveHistory(
        ApprovalRequest $request,
        string $step,
        string $action,
        ?string $comment,
        string $statusBefore,
        string $statusAfter
    ): void {
        $history = $request->approval_history ?? [];

        $history[$step] = [
            'action' => $action,
            'comment' => $comment,
            'approved_at' => now()->toIso8601String(),
            'approved_by' => auth()->id(),
            'workflow_status_before' => $statusBefore,
            'workflow_status_after' => $statusAfter,
            'step_index' => $request->current_step_index,
        ];

        $request->approval_history = $history;
    }
}


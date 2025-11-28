<?php

namespace Modules\ApprovalWorkflow\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ApprovalWorkflow\Services\ApprovalCenterService;
use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\VehicleRegistration\Models\VehicleRegistration;
use App\Helpers\PermissionHelper;

class ApprovalCenterController extends Controller
{
    protected $service;

    public function __construct(ApprovalCenterService $service)
    {
        $this->service = $service;
    }

    /**
     * Display approval center with list of all pending requests
     */
    public function index(Request $request)
    {
        $user = backpack_user();
        
        // Check if user has permission to access approval center
        $hasApprovalCenterAccess = PermissionHelper::can($user, 'approval_center.view');
        $isAdmin = $user->hasRole('Admin');
        
        if (!$hasApprovalCenterAccess && !$isAdmin) {
            abort(403, 'Bạn không có quyền truy cập Trung tâm phê duyệt');
        }
        
        $modelType = $request->get('model_type');
        $type = $request->get('type', $modelType ?: 'all');
        $status = $request->get('status', 'all');
        $timeRange = $request->get('time_range', 'all');
        
        // Get all requests that user can approve
        $requests = $this->service->getApprovalRequests($user, [
            'type' => $type,
            'status' => $status,
            'time_range' => $timeRange,
        ]);

        // Get selected request ID
        $selectedId = $request->get('id');
        $selectedRequest = null;
        
        if ($selectedId) {
            $selectedRequest = $this->service->getRequestDetails($selectedId, $request->get('model_type'));
        } elseif ($requests->isNotEmpty()) {
            // Auto-select first request if none selected
            $firstRequest = $requests->first();
            $selectedRequest = $this->service->getRequestDetails(
                $firstRequest['id'], 
                $firstRequest['model_type']
            );
        }

        // Get user's department name
        $departmentName = 'HỆ THỐNG';
        if ($user->employee && $user->employee->department) {
            $departmentName = strtoupper($user->employee->department->name);
        } elseif ($user->department) {
            $departmentName = strtoupper($user->department->name);
        } elseif ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc'])) {
            $departmentName = 'BAN GIÁM ĐỐC';
        }

        // Get pending counts by type for badge display
        $pendingCounts = $this->service->getPendingCountsByType($user);

        // Check if user has PIN configured
        $hasPin = !empty($user->certificate_pin);

        return view('approvalworkflow::approval-center.index', [
            'requests' => $requests,
            'selectedRequest' => $selectedRequest,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'time_range' => $timeRange,
            ],
            'departmentName' => $departmentName,
            'pendingCounts' => $pendingCounts,
            'hasPin' => $hasPin,
        ]);
    }

    /**
     * Get request details (AJAX)
     */
    public function getDetails(Request $request)
    {
        $user = backpack_user();
        
        // Check if user has permission to access approval center
        $hasApprovalCenterAccess = PermissionHelper::can($user, 'approval_center.view');
        $isAdmin = $user->hasRole('Admin');
        
        if (!$hasApprovalCenterAccess && !$isAdmin) {
            return response()->json(['error' => 'Bạn không có quyền truy cập'], 403);
        }
        
        $id = $request->get('id');
        $modelType = $request->get('model_type');
        
        $details = $this->service->getRequestDetails($id, $modelType);
        
        if (!$details) {
            return response()->json(['error' => 'Request not found'], 404);
        }

        return response()->json($details);
    }

    /**
     * Get approval history (AJAX)
     */
    public function getHistory(Request $request)
    {
        $id = $request->get('id');
        $modelType = $request->get('model_type');
        
        $history = $this->service->getApprovalHistory($id, $modelType);
        
        return response()->json($history);
    }

    /**
     * Approve request
     */
    public function approve(Request $request)
    {
        $id = $request->get('id');
        $modelType = $request->get('model_type');
        $comment = $request->get('comment', '');
        $certificatePin = $request->get('certificate_pin'); // Can be null for reviewer step
        
        try {
            $result = $this->service->approveRequest(
                $id, 
                $modelType, 
                backpack_user(), 
                $comment,
                $certificatePin // Pass null for reviewer step (no PIN needed)
            );
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reject request
     */
    public function reject(Request $request)
    {
        $id = $request->get('id');
        $modelType = $request->get('model_type');
        $reason = $request->get('reason', '');
        
        try {
            $result = $this->service->rejectRequest(
                $id, 
                $modelType, 
                backpack_user(), 
                $reason
            );
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get list of directors for reviewer to select
     */
    public function getDirectors(Request $request)
    {
        $directors = EmployeeLeave::getDirectors();
        
        return response()->json([
            'success' => true,
            'data' => $directors->map(function($director) {
                return [
                    'id' => $director->id,
                    'name' => $director->name,
                    'username' => $director->username,
                    'avatar' => $director->profile_photo_path ? asset('storage/' . $director->profile_photo_path) : null,
                ];
            })
        ]);
    }

    /**
     * Assign approvers (save selected directors)
     */
    public function assignApprovers(Request $request)
    {
        $id = $request->get('id');
        $modelType = $request->get('model_type');
        $approverIds = $request->get('approver_ids', []);
        
        if ($modelType !== 'leave' && $modelType !== 'vehicle') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ áp dụng cho đơn nghỉ phép và đăng ký xe',
            ], 400);
        }
        
        try {
            $user = backpack_user();
            
            if ($modelType === 'leave') {
                $leave = EmployeeLeave::findOrFail($id);
                
                $isReviewerStep = $leave->workflow_status === EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD;
                
                // Check permission based on employee rank
                $rankCode = $leave->employee ? $leave->employee->rank_code : null;
                $isOfficer = $this->service->isOfficerRank($rankCode);
                
                $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
                $hasOfficerReviewPermission = PermissionHelper::can($user, 'leave.review.officer');
                
                // Check if user has appropriate permission for this rank type
                // Admin can always approve, or if user has both permissions
                $hasCorrectPermission = false;
                if ($user->hasRole('Admin') || ($hasReviewPermission && $hasOfficerReviewPermission)) {
                    $hasCorrectPermission = true;
                } elseif ($isOfficer) {
                    // Officer rank - need leave.review.officer permission
                    $hasCorrectPermission = $hasOfficerReviewPermission;
                } else {
                    // Employee rank - need leave.review permission
                    $hasCorrectPermission = $hasReviewPermission;
                }
                
                if (!$isReviewerStep || !$hasCorrectPermission) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn không có quyền thực hiện thao tác này',
                    ], 403);
                }
                
                if (empty($approverIds) || !is_array($approverIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vui lòng chọn ít nhất một người phê duyệt',
                    ], 400);
                }
                
                $directors = EmployeeLeave::getDirectors();
                $directorIds = $directors->pluck('id')->toArray();
                $invalidIds = array_diff($approverIds, $directorIds);
                
                if (!empty($invalidIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Có người được chọn không phải là Ban Giám đốc',
                    ], 400);
                }
                
                $approverIds = array_map('intval', $approverIds);
                
                // ✅ Sửa: Lưu selected_approvers vào approval_requests, không phải employee_leave
                $approvalRequest = $leave->approvalRequest;
                if (!$approvalRequest) {
                    // Tạo approvalRequest nếu chưa có
                    $approvalRequestService = app(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class);
                    $approvalRequest = $approvalRequestService->syncFromModel($leave, 'leave');
                }
                
                // Lấy selected_approvers hiện có
                $selectedApprovers = $approvalRequest->selected_approvers ?? [];
                if (!is_array($selectedApprovers)) {
                    $selectedApprovers = json_decode($selectedApprovers, true) ?? [];
                }
                
                // Set selected_approvers cho bước director_approval
                $selectedApprovers['director_approval'] = [
                    'selected_by' => $user->id,
                    'selected_at' => now()->toIso8601String(),
                    'users' => \App\Models\User::whereIn('id', $approverIds)->get()->map(function($u) {
                        return [
                            'id' => $u->id,
                            'name' => $u->name,
                            'email' => $u->email,
                        ];
                    })->toArray()
                ];
                
                $approvalRequest->selected_approvers = $selectedApprovers;
                $approvalRequest->save(); // Lưu selected_approvers trước
                
                // Dùng WorkflowEngine để xử lý workflow (không truyền selectedApprovers vì đã lưu rồi)
                $workflowEngine = app(\App\Services\WorkflowEngine::class);
                $workflowEngine->processApprovalStep(
                    $approvalRequest,
                    'approved',
                    'Đã chọn người phê duyệt: ' . implode(', ', \App\Models\User::whereIn('id', $approverIds)->pluck('name')->toArray()),
                    null // Leave workflow không cần selectedApprovers trong processApprovalStep
                );
                
                return response()->json([
                    'success' => true,
                    'message' => 'Đã gán người phê duyệt và chuyển đơn lên Ban Giám đốc thành công',
                    'data' => [
                        'selected_approvers' => $approverIds,
                    ],
                ]);
            } elseif ($modelType === 'vehicle') {
                $vehicle = VehicleRegistration::findOrFail($id);
                
                // Check via approval_requests
                $approvalRequest = $vehicle->approvalRequest;
                if (!$approvalRequest) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy yêu cầu phê duyệt',
                    ], 404);
                }
                
                // Check if at review step (Thẩm định)
                $isReviewStep = $approvalRequest->status === 'in_review' && 
                               $approvalRequest->current_step === 'review';
                
                // Check permission for review step
                $hasReviewPermission = PermissionHelper::can($user, 'vehicle_registration.review');
                
                if (!$isReviewStep || !$hasReviewPermission) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn không có quyền thực hiện thao tác này',
                    ], 403);
                }
                
                if (empty($approverIds) || !is_array($approverIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vui lòng chọn ít nhất một người phê duyệt',
                    ], 400);
                }
                
                // Get directors
                $directors = \App\Models\User::whereHas('roles', function($q) {
                    $q->whereIn('name', ['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
                })->get();
                $directorIds = $directors->pluck('id')->toArray();
                $invalidIds = array_diff($approverIds, $directorIds);
                
                if (!empty($invalidIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Có người được chọn không phải là Ban Giám đốc',
                    ], 400);
                }
                
                $approverIds = array_map('intval', $approverIds);
                
                // Update approval_requests
                $selectedApprovers = $approvalRequest->selected_approvers ?? [];
                if (!is_array($selectedApprovers)) {
                    $selectedApprovers = json_decode($selectedApprovers, true) ?? [];
                }
                
                // Map selected_approvers by step
                $selectedApprovers['director_approval'] = $approverIds;
                $approvalRequest->selected_approvers = $selectedApprovers;
                
                // Use WorkflowEngine to process review step (similar to leave)
                $workflowEngine = app(\App\Services\WorkflowEngine::class);
                $workflowEngine->processApprovalStep(
                    $approvalRequest,
                    'approved',
                    'Đã chọn người phê duyệt: ' . implode(', ', \App\Models\User::whereIn('id', $approverIds)->pluck('name')->toArray()),
                    $approverIds, // Pass selected approvers
                    $user
                );
                
                $approvalRequest->refresh();
                
                // History is now stored in approval_requests.approval_history JSON field
                // No need to create separate ApprovalHistory record
                
                // Sync với ApprovalRequestService
                if (class_exists(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class)) {
                    $service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
                    $service->syncFromModel($vehicle, 'vehicle');
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Đã gán người phê duyệt và chuyển đơn lên Ban Giám đốc thành công',
                    'data' => [
                        'selected_approvers' => $approverIds,
                    ],
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Bulk assign approvers for reviewer step (bulk thẩm định)
     */
    public function bulkAssignApprovers(Request $request)
    {
        $requests = $request->get('requests', []);
        $approverIds = $request->get('approver_ids', []);
        
        if (empty($requests) || !is_array($requests)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có đơn nào được chọn',
            ], 400);
        }
        
        if (empty($approverIds) || !is_array($approverIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất một người phê duyệt',
            ], 400);
        }
        
        $user = backpack_user();
        $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
        $hasOfficerReviewPermission = PermissionHelper::can($user, 'leave.review.officer');
        
        // Check if user has at least one review permission
        if (!$hasReviewPermission && !$hasOfficerReviewPermission && !$user->hasRole('Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này',
            ], 403);
        }
        
        // Verify all IDs are directors
        $directors = \Modules\PersonnelReport\Models\EmployeeLeave::getDirectors();
        $directorIds = $directors->pluck('id')->toArray();
        $approverIds = array_map('intval', $approverIds);
        $invalidIds = array_diff($approverIds, $directorIds);
        
        if (!empty($invalidIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Có người được chọn không phải là Ban Giám đốc',
            ], 400);
        }
        
        $successCount = 0;
        $failCount = 0;
        $errors = [];
        
        foreach ($requests as $req) {
            try {
                $id = $req['id'] ?? null;
                $modelType = $req['model_type'] ?? null;
                
                if (!$id || $modelType !== 'leave') {
                    $failCount++;
                    $errors[] = 'Đơn #' . ($id ?? 'N/A') . ': Thông tin không hợp lệ';
                    continue;
                }
                
                $leave = \Modules\PersonnelReport\Models\EmployeeLeave::findOrFail($id);
                
                // Check if this is reviewer step
                $isReviewerStep = $leave->workflow_status === \Modules\PersonnelReport\Models\EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD;
                
                if (!$isReviewerStep) {
                    $failCount++;
                    $errors[] = 'Đơn #' . $id . ': Không ở bước thẩm định';
                    continue;
                }
                
                // Check permission based on employee rank for this specific leave request
                $rankCode = $leave->employee ? $leave->employee->rank_code : null;
                $isOfficer = $this->service->isOfficerRank($rankCode);
                
                // Check if user has appropriate permission for this rank type
                $hasCorrectPermission = false;
                if ($user->hasRole('Admin') || ($hasReviewPermission && $hasOfficerReviewPermission)) {
                    $hasCorrectPermission = true;
                } elseif ($isOfficer) {
                    // Officer rank - need leave.review.officer permission
                    $hasCorrectPermission = $hasOfficerReviewPermission;
                } else {
                    // Employee rank - need leave.review permission
                    $hasCorrectPermission = $hasReviewPermission;
                }
                
                if (!$hasCorrectPermission) {
                    $failCount++;
                    $errors[] = 'Đơn #' . $id . ': Bạn không có quyền thẩm định loại cấp bậc này';
                    continue;
                }
                
                if (!$isReviewerStep) {
                    $failCount++;
                    $errors[] = 'Đơn #' . $id . ': Không ở bước thẩm định';
                    continue;
                }
                
                // ✅ Sửa: Lưu selected_approvers vào approval_requests, không phải employee_leave
                $approvalRequest = $leave->approvalRequest;
                if (!$approvalRequest) {
                    // Tạo approvalRequest nếu chưa có
                    $approvalRequestService = app(\Modules\ApprovalWorkflow\Services\ApprovalRequestService::class);
                    $approvalRequest = $approvalRequestService->syncFromModel($leave, 'leave');
                }
                
                // Lấy selected_approvers hiện có
                $selectedApprovers = $approvalRequest->selected_approvers ?? [];
                if (!is_array($selectedApprovers)) {
                    $selectedApprovers = json_decode($selectedApprovers, true) ?? [];
                }
                
                // Set selected_approvers cho bước director_approval
                $selectedApprovers['director_approval'] = [
                    'selected_by' => $user->id,
                    'selected_at' => now()->toIso8601String(),
                    'users' => \App\Models\User::whereIn('id', $approverIds)->get()->map(function($u) {
                        return [
                            'id' => $u->id,
                            'name' => $u->name,
                            'email' => $u->email,
                        ];
                    })->toArray()
                ];
                
                $approvalRequest->selected_approvers = $selectedApprovers;
                $approvalRequest->save(); // Lưu selected_approvers trước
                
                // Dùng WorkflowEngine để xử lý workflow (không truyền selectedApprovers vì đã lưu rồi)
                $workflowEngine = app(\App\Services\WorkflowEngine::class);
                $workflowEngine->processApprovalStep(
                    $approvalRequest,
                    'approved',
                    'Đã chọn người phê duyệt (thẩm định hàng loạt): ' . implode(', ', \App\Models\User::whereIn('id', $approverIds)->pluck('name')->toArray()),
                    null // Leave workflow không cần selectedApprovers trong processApprovalStep
                );
                
                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
                $errors[] = 'Đơn #' . ($req['id'] ?? 'N/A') . ': ' . $e->getMessage();
            }
        }
        
        $directorNames = \App\Models\User::whereIn('id', $approverIds)->pluck('name')->toArray();
        
        return response()->json([
            'success' => $failCount === 0,
            'message' => "Đã gán người phê duyệt cho {$successCount} đơn" . ($failCount > 0 ? ", {$failCount} đơn thất bại" : "") . ". Đã chọn: " . implode(', ', $directorNames),
            'data' => [
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'errors' => array_slice($errors, 0, 10),
            ],
        ]);
    }

    /**
     * Bulk approve multiple requests
     */
    public function bulkApprove(Request $request)
    {
        $requests = $request->get('requests', []);
        $pin = $request->get('pin');
        
        if (empty($requests) || !is_array($requests)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có đơn nào được chọn',
            ], 400);
        }

        $user = backpack_user();
        $approvedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($requests as $req) {
            try {
                $id = $req['id'] ?? null;
                $modelType = $req['model_type'] ?? null;

                if (!$id || !$modelType) {
                    $failedCount++;
                    continue;
                }

                // Get request details to check if needs PIN
                $details = $this->service->getRequestDetails($id, $modelType);
                if (!$details) {
                    $failedCount++;
                    $errors[] = 'Không tìm thấy đơn #' . $id;
                    continue;
                }

                // Check if user can approve
                if (!$details['can_approve']) {
                    $failedCount++;
                    $errors[] = 'Bạn không có quyền phê duyệt đơn #' . $id;
                    continue;
                }

                // Determine if needs PIN
                $needsPin = $details['needs_pin'] ?? true;
                
                // Use provided PIN if needed
                $certificatePin = $needsPin ? $pin : null;

                // Approve the request
                $result = $this->service->approveRequest(
                    $id,
                    $modelType,
                    $user,
                    'Phê duyệt hàng loạt',
                    $certificatePin
                );

                if ($result['success']) {
                    $approvedCount++;
                } else {
                    $failedCount++;
                    $errors[] = $result['message'] ?? 'Không thể phê duyệt đơn #' . $id;
                }
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = 'Đơn #' . ($req['id'] ?? 'N/A') . ': ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'approved_count' => $approvedCount,
            'failed_count' => $failedCount,
            'errors' => array_slice($errors, 0, 10), // Limit errors to first 10
            'message' => "Đã phê duyệt {$approvedCount} đơn" . ($failedCount > 0 ? ", {$failedCount} đơn thất bại" : ""),
        ]);
    }

    /**
     * Get pending count for sidebar badge (AJAX endpoint)
     */
    public function getPendingCount(Request $request)
    {
        try {
            $user = backpack_user();
            $count = $this->service->getTotalPendingCountForUser($user);
            
            return response()->json([
                'success' => true,
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}


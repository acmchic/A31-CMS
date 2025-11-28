<?php

namespace Modules\ApprovalWorkflow\Services;

use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\VehicleRegistration\Models\VehicleRegistration;
use Modules\ProductionManagement\Models\MaterialPlan;
use App\Helpers\PermissionHelper;
use Carbon\Carbon;

class ApprovalCenterService
{
    /**
     * Check if rank_code is an officer rank (only numbers and slashes, no CN/QN text)
     * Sĩ quan: 1//, 2//, 3//, 1/, etc. (only numbers and /, //, ///, ////)
     * Nhân viên: CNQP, QNCN, 1//CN, 2/CN, etc. (has CN, QN or other text)
     * 
     * @param string|null $rankCode
     * @return bool
     */
    public function isOfficerRank($rankCode)
    {
        if (empty($rankCode)) {
            return false;
        }
        
        $rankCode = trim($rankCode);
        
        // Remove all slashes and check if remaining is only numbers
        // If there's any letter (CN, QN, etc.), it's not an officer rank
        $withoutSlashes = str_replace(['/', ' '], '', $rankCode);
        
        // If after removing slashes, it's empty or contains letters, it's not officer rank
        if (empty($withoutSlashes)) {
            return false;
        }
        
        // Check if remaining contains only numbers (0-9)
        // Sĩ quan: only numbers and slashes, no letters
        // Pattern: can have numbers and slashes only, like "1//", "2/", "3///", etc.
        return preg_match('/^[\d\/\s]+$/', $rankCode) === 1 && 
               preg_match('/[a-zA-ZÀ-ỹ]/', $rankCode) === 0;
    }
    
    /**
     * Get all approval requests that user can approve
     */
    public function getApprovalRequests($user, $filters = [])
    {
        $requests = collect([]);

        // Get leave requests
        if ($filters['type'] === 'all' || $filters['type'] === 'leave') {
            $leaveRequests = $this->getLeaveRequests($user, $filters);
            $requests = $requests->merge($leaveRequests);
        }

        // Get vehicle registration requests
        if ($filters['type'] === 'all' || $filters['type'] === 'vehicle') {
            $vehicleRequests = $this->getVehicleRequests($user, $filters);
            $requests = $requests->merge($vehicleRequests);
        }

        // Get material plan requests
        if ($filters['type'] === 'all' || $filters['type'] === 'material_plan') {
            $materialPlanRequests = $this->getMaterialPlanRequests($user, $filters);
            $requests = $requests->merge($materialPlanRequests);
        }

        // Sort by created_at desc
        return $requests->sortByDesc('created_at')->values();
    }

    /**
     * Get pending request counts by type for current user
     */
    public function getPendingCountsByType($user)
    {
        $counts = [
            'leave' => [
                'pending' => 0, // Chỉ huy xác nhận
                'review' => 0,  // Thẩm định
                'director' => 0 // BGD phê duyệt
            ],
            'vehicle' => [
                'pending' => 0,
                'review' => 0,
                'director' => 0
            ],
            'material_plan' => [
                'pending' => 0,
                'review' => 0,
                'director' => 0
            ]
        ];

        // Get leave counts
        $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
        $hasOfficerReviewPermission = PermissionHelper::can($user, 'leave.review.officer');
        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);

        // Count pending (department head approval step)
        // Use the same filter logic as getApprovalRequests() to ensure badge matches what user can see
        $pendingQuery = EmployeeLeave::query();
        
        // Apply the same permission filter as getApprovalRequests()
        // This ensures badge count matches what user can actually see in the list
        if (!$user->hasRole('Admin') && !$hasReviewPermission && !$hasOfficerReviewPermission && !$isDirector) {
            // For regular users and department heads, apply same filter as getApprovalRequests()
            $pendingQuery->where(function($q) use ($user, $isDepartmentHead) {
                // User can see their own requests
                if ($user->employee_id) {
                    $q->where('employee_id', $user->employee_id);
                }

                // Department head can see their department
                if ($isDepartmentHead) {
                    // Get user's department_id from user->department_id or user->employee->department_id
                    $userDepartmentId = $user->department_id;
                    if (!$userDepartmentId && $user->employee_id) {
                        $employee = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                        if ($employee && $employee->department_id) {
                            $userDepartmentId = $employee->department_id;
                        }
                    }
                    
                    if ($userDepartmentId) {
                        $q->orWhereHas('employee', function($subQ) use ($userDepartmentId) {
                            $subQ->where('department_id', $userDepartmentId);
                        });
                    }
                }
            });
        }
        
        // Then filter by approval status/step
        $pendingQuery->whereHas('approvalRequest', function($q) {
            $q->where('status', 'submitted')
              ->where('current_step', 'department_head_approval');
        });
        
        // Count review (reviewer approval step)
        $reviewQuery = EmployeeLeave::query()
            ->whereHas('approvalRequest', function($q) {
                $q->where('status', 'in_review')
                  ->where('current_step', 'review');
            });
        
        // Filter review count by rank type if user has specific permission
        if ($hasReviewPermission && !$hasOfficerReviewPermission) {
            // Only count employee ranks (CNQP, QNCN, or has CN/QN in rank_code)
            $reviewQuery->whereHas('employee', function($q) {
                $q->where(function($rankQ) {
                    $rankQ->where('rank_code', 'CNQP')
                          ->orWhere('rank_code', 'QNCN')
                          ->orWhere('rank_code', 'like', '%CN%')
                          ->orWhere('rank_code', 'like', '%QN%');
                });
            });
        } elseif (!$hasReviewPermission && $hasOfficerReviewPermission) {
            // Only count officer ranks (only numbers and slashes, no CN/QN)
            $reviewQuery->whereHas('employee', function($q) {
                // Officer ranks: patterns like 1//, 2//, 3//, 1/, 2/, etc.
                // Must not contain CN or QN
                $q->where(function($rankQ) {
                    // Match patterns that are only numbers and slashes
                    // Using whereRaw with REGEXP for MySQL pattern matching
                    $rankQ->whereRaw('rank_code REGEXP ?', ['^[0-9/ ]+$']);
                })
                ->where('rank_code', 'not like', '%CN%')
                ->where('rank_code', 'not like', '%QN%')
                ->where('rank_code', 'not like', '%cn%')
                ->where('rank_code', 'not like', '%qn%');
            });
        }
        
        $counts['leave']['pending'] = $pendingQuery->count();
        $counts['leave']['review'] = $reviewQuery->count();

        // For director count, only count requests where user is in selected_approvers
        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        if ($isDirector) {
            $userId = (int)$user->id;
            // Director: count requests at director_approval step where user is in selected_approvers
            // Build a fresh query without user permission filters to get accurate count
            // ✅ Sửa: Load và filter trong PHP vì JSON structure phức tạp (array objects)
            $directorQuery = EmployeeLeave::query()
                ->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'in_review')
                      ->where('current_step', 'director_approval')
                      ->whereNotNull('selected_approvers');
                })
                ->with('approvalRequest');
            
            $counts['leave']['director'] = $directorQuery->get()->filter(function($leave) use ($userId) {
                $approvalRequest = $leave->approvalRequest;
                if (!$approvalRequest || !$approvalRequest->selected_approvers) {
                    return false;
                }
                
                $selectedApprovers = is_array($approvalRequest->selected_approvers) 
                    ? $approvalRequest->selected_approvers 
                    : json_decode($approvalRequest->selected_approvers, true);
                
                if (!is_array($selectedApprovers) || !isset($selectedApprovers['director_approval']['users'])) {
                    return false;
                }
                
                $users = $selectedApprovers['director_approval']['users'];
                foreach ($users as $userItem) {
                    if (is_array($userItem) && isset($userItem['id']) && (int)$userItem['id'] === $userId) {
                        return true;
                    }
                }
                
                return false;
            })->count();
        } else {
            $counts['leave']['director'] = 0;
        }

        // Get vehicle counts
        $vehicleQuery = VehicleRegistration::query();
        $this->filterVehicleByUserPermissions($vehicleQuery, $user, []);

        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        
        if ($isDirector) {
            $counts['vehicle']['pending'] = 0;
            $counts['vehicle']['review'] = 0;
            $counts['vehicle']['director'] = (clone $vehicleQuery)->whereHas('approvalRequest', function($q) {
                $q->where('status', 'in_review')
                  ->where('current_step', 'director_approval');
            })->count();
        } else {
            $counts['vehicle']['pending'] = (clone $vehicleQuery)->whereHas('approvalRequest', function($q) {
                $q->whereIn('status', ['submitted', 'in_review'])
                  ->whereIn('current_step', ['vehicle_picked', 'department_head_approval']);
            })->count();
            $counts['vehicle']['review'] = 0;
            $counts['vehicle']['director'] = 0;
        }

        // Get material plan counts
        $materialPlanQuery = MaterialPlan::query();
        $hasMaterialPlanPermission = PermissionHelper::can($user, 'material_plan.approve');
        
        if ($isDirector && $hasMaterialPlanPermission) {
            // BGD: chỉ đếm các plan có selected_approvers chứa user này và workflow_status là pending hoặc approved_by_reviewer
            $userId = (int)$user->id;
            $counts['material_plan']['pending'] = 0;
            $counts['material_plan']['review'] = 0;
            $counts['material_plan']['director'] = (clone $materialPlanQuery)
                ->whereHas('approvalRequest', function($q) use ($userId) {
                    $q->where(function($statusQ) {
                        $statusQ->where('status', 'submitted')
                                ->where('current_step', 'review')
                            ->orWhere(function($reviewQ) {
                                $reviewQ->where('status', 'in_review')
                                        ->where('current_step', 'director_approval');
                            });
                    })
                    ->where(function($jsonQ) use ($userId) {
                        $jsonQ->whereJsonContains('selected_approvers->director_approval', $userId)
                              ->orWhereJsonContains('selected_approvers->director_approval', (string)$userId);
                    });
                })
                ->count();
        } else {
            $counts['material_plan']['pending'] = 0;
            $counts['material_plan']['review'] = 0;
            $counts['material_plan']['director'] = 0;
        }

        return $counts;
    }

    /**
     * Get the appropriate pending count for current user based on their role
     */
    public function getPendingCountForUser($user, $type = 'leave')
    {
        $counts = $this->getPendingCountsByType($user);

        // Determine which count to show based on user role
        $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
        $hasOfficerReviewPermission = PermissionHelper::can($user, 'leave.review.officer');
        
        if ($user->hasRole('Admin') || $hasReviewPermission || $hasOfficerReviewPermission) {
            // Thẩm định: show review count
            return $counts[$type]['review'];
        } elseif ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc'])) {
            // Ban giám đốc: show director count
            return $counts[$type]['director'];
        } else {
            // Trưởng phòng/Quản đốc: show pending count
            return $counts[$type]['pending'];
        }
    }

    /**
     * Get total pending count for menu badge (leave + vehicle)
     */
    public function getTotalPendingCountForUser($user)
    {
        $counts = $this->getPendingCountsByType($user);
        $total = 0;

        // Determine which count to show based on user role
        $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
        $hasOfficerReviewPermission = PermissionHelper::can($user, 'leave.review.officer');
        
        if ($user->hasRole('Admin') || $hasReviewPermission || $hasOfficerReviewPermission) {
            // Thẩm định: show review count
            $total = $counts['leave']['review'] + $counts['vehicle']['review'];
        } elseif ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc'])) {
            // Ban giám đốc: show director count
            $total = $counts['leave']['director'] + $counts['vehicle']['director'];
        } else {
            // Trưởng phòng/Quản đốc: show pending count
            $total = $counts['leave']['pending'] + $counts['vehicle']['pending'];
        }

        return $total;
    }

    /**
     * Helper: Apply leave workflow status filter to approval_requests query
     */
    private function applyLeaveWorkflowStatusFilter($query, $status)
    {
        $statusMap = [
            EmployeeLeave::WORKFLOW_PENDING => ['status' => 'submitted', 'step' => 'department_head_approval'],
            EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD => ['status' => 'in_review', 'step' => 'review'],
            EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER => ['status' => 'in_review', 'step' => 'director_approval'],
            EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR => ['status' => 'approved', 'step' => null],
            EmployeeLeave::WORKFLOW_REJECTED => ['status' => 'rejected', 'step' => null],
        ];

        $mapped = $statusMap[$status] ?? null;
        if (!$mapped) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where('status', $mapped['status']);
        if ($mapped['step'] !== null) {
            $query->where('current_step', $mapped['step']);
        } else {
            $query->whereNull('current_step');
        }
    }

    /**
     * Get leave requests
     */
    protected function getLeaveRequests($user, $filters)
    {
        $query = EmployeeLeave::query();

        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);

        // Apply status filter FIRST - but skip for directors when status is 'all' (let filterLeaveByUserPermissions handle it)
        if ($filters['status'] !== 'all' || !$isDirector) {
            if ($filters['status'] !== 'all') {
                $statusValue = null;
                if ($filters['status'] === 'pending') {
                    $statusValue = EmployeeLeave::WORKFLOW_PENDING;
                } elseif ($filters['status'] === 'completed') {
                    $statusValue = EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR;
                } else {
                    // Direct status match (approved_by_department_head, approved_by_reviewer, etc.)
                    $statusValue = $filters['status'];
                }

                if ($statusValue) {
                    // Apply status filter via approval_requests
                    $query->whereHas('approvalRequest', function($arQ) use ($statusValue) {
                        $this->applyLeaveWorkflowStatusFilter($arQ, $statusValue);
                    });
                }
            }
        }

        // Apply time range filter
        $this->applyTimeRangeFilter($query, $filters['time_range']);

        // Filter by user permissions (includes selected_approvers filter for directors)
        // This respects the status filter already applied above
        $this->filterLeaveByUserPermissions($query, $user, $filters);

        $user = backpack_user();

        $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
        $hasOfficerReviewPermission = PermissionHelper::can($user, 'leave.review.officer');
        $hasViewAllPermission = PermissionHelper::can($user, 'leave.view.all');

        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        $userId = (int)$user->id;
        
        return $query->with(['employee.department', 'approvalRequest'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($leave) use ($hasReviewPermission, $hasOfficerReviewPermission, $hasViewAllPermission, $isDirector, $userId) {
                // ✅ Nếu có permission view.all, xem tất cả (không filter)
                if ($hasViewAllPermission) {
                    return true;
                }
                
                // ✅ Filter theo selected_approvers cho director
                if ($isDirector) {
                    $approvalRequest = $leave->approvalRequest;
                    if ($approvalRequest) {
                        // Nếu ở bước director_approval và status là in_review
                        if ($approvalRequest->current_step === 'director_approval' && $approvalRequest->status === 'in_review') {
                            // Chỉ hiển thị nếu user có trong selected_approvers
                            if ($approvalRequest->selected_approvers) {
                                $selectedApprovers = is_array($approvalRequest->selected_approvers) 
                                    ? $approvalRequest->selected_approvers 
                                    : json_decode($approvalRequest->selected_approvers, true);
                                
                                if (is_array($selectedApprovers) && isset($selectedApprovers['director_approval']['users'])) {
                                    $users = $selectedApprovers['director_approval']['users'];
                                    $found = false;
                                    foreach ($users as $userItem) {
                                        if (is_array($userItem) && isset($userItem['id']) && (int)$userItem['id'] === $userId) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        return false; // User không có trong selected_approvers
                                    }
                                    // User có trong selected_approvers, tiếp tục với logic filter khác
                                } else {
                                    return false; // Không có selected_approvers structure đúng
                                }
                            } else {
                                return false; // Không có selected_approvers
                            }
                        } elseif ($approvalRequest->status === 'approved') {
                            // Nếu đã approved, chỉ hiển thị nếu user đã approve
                            $approvalHistory = $approvalRequest->approval_history ?? [];
                            if (is_string($approvalHistory)) {
                                $approvalHistory = json_decode($approvalHistory, true);
                            }
                            
                            // ✅ Sửa: Kiểm tra approval_history structure
                            // approval_history có thể là: { "director_approval": [{...}] } hoặc { "director_approval": {...} }
                            if (isset($approvalHistory['director_approval'])) {
                                $directorHistory = $approvalHistory['director_approval'];
                                
                                // Nếu là array, lấy phần tử đầu tiên
                                if (is_array($directorHistory) && isset($directorHistory[0])) {
                                    $directorHistory = $directorHistory[0];
                                }
                                
                                // Kiểm tra approved_by
                                if (is_array($directorHistory)) {
                                    $approvedBy = $directorHistory['approved_by'] ?? null;
                                    if ($approvedBy && (int)$approvedBy === $userId) {
                                        return true; // User đã approve, hiển thị
                                    }
                                }
                            }
                            
                            // Nếu không tìm thấy trong approval_history, có thể là request cũ
                            // Cho phép hiển thị nếu user có trong selected_approvers (fallback)
                            if ($approvalRequest->selected_approvers) {
                                $selectedApprovers = is_array($approvalRequest->selected_approvers) 
                                    ? $approvalRequest->selected_approvers 
                                    : json_decode($approvalRequest->selected_approvers, true);
                                
                                if (is_array($selectedApprovers) && isset($selectedApprovers['director_approval']['users'])) {
                                    $users = $selectedApprovers['director_approval']['users'];
                                    foreach ($users as $userItem) {
                                        if (is_array($userItem) && isset($userItem['id']) && (int)$userItem['id'] === $userId) {
                                            return true; // User có trong selected_approvers, hiển thị
                                        }
                                    }
                                }
                            }
                            
                            return false; // User không approve request này
                        }
                        // Nếu không phải director_approval hoặc approved, tiếp tục với logic filter khác
                    }
                    // Nếu không có approvalRequest, tiếp tục với logic filter khác (có thể là request cũ)
                }
                
                // Existing filter logic
                // If user has both permissions, see all
                if ($hasReviewPermission && $hasOfficerReviewPermission) {
                    return true;
                }
                
                // If user has no review permissions, don't filter here (will be filtered by other logic)
                if (!$hasReviewPermission && !$hasOfficerReviewPermission) {
                    return true;
                }
                
                // Get employee rank_code
                $rankCode = $leave->employee ? $leave->employee->rank_code : null;
                $isOfficer = $this->isOfficerRank($rankCode);
                
                // If user only has regular review permission, only see employee ranks (not officer)
                if ($hasReviewPermission && !$hasOfficerReviewPermission) {
                    return !$isOfficer; // Only non-officer ranks
                }
                
                // If user only has officer review permission, only see officer ranks
                if (!$hasReviewPermission && $hasOfficerReviewPermission) {
                    return $isOfficer; // Only officer ranks
                }
                
                return true;
            })
            ->map(function($leave) use ($user) {
                $isReviewerStep = $leave->workflow_status === EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD;
                
                // Check review permission based on employee rank
                $rankCode = $leave->employee ? $leave->employee->rank_code : null;
                $isOfficer = $this->isOfficerRank($rankCode);
                $hasReviewPermission = false;
                if ($isReviewerStep) {
                    // At reviewer step, check permission based on rank
                    $hasReviewPermission = $isOfficer 
                        ? PermissionHelper::can($user, 'leave.review.officer')
                        : PermissionHelper::can($user, 'leave.review');
                }
                
                $hasViewAllPermission = PermissionHelper::can($user, 'leave.view.all');
                
                $needsPin = !($isReviewerStep && $hasReviewPermission);
                
                // ✅ Sửa: Lấy selected_approvers từ approvalRequest, không phải từ leave
                $approvalRequest = $leave->approvalRequest;
                $hasSelectedApprovers = false;
                if ($approvalRequest && $approvalRequest->selected_approvers) {
                    $selectedApprovers = is_array($approvalRequest->selected_approvers) 
                        ? $approvalRequest->selected_approvers 
                        : json_decode($approvalRequest->selected_approvers, true);
                    $hasSelectedApprovers = !empty($selectedApprovers);
                }
                
                // ✅ Sửa: Nếu chỉ có permission view.all, không có quyền approve
                if ($hasViewAllPermission && !$hasReviewPermission && !PermissionHelper::can($user, 'leave.approve')) {
                    $canApprove = false;
                } elseif ($approvalRequest) {
                    // ✅ Sửa: Dùng approvalRequest->canBeApprovedBy() để kiểm tra quyền
                    $canApprove = $approvalRequest->canBeApprovedBy($user);
                } else {
                    // Fallback nếu chưa có approvalRequest
                    if ($isReviewerStep && $hasReviewPermission) {
                        $canApprove = $leave->canBeApproved();
                    } else {
                        $canApprove = $leave->canBeApproved() && $this->canUserApprove($leave, $user);
                    }
                }

                return [
                    'id' => $leave->id,
                    'model_type' => 'leave',
                    'type' => 'Nghỉ phép',
                    'type_label' => 'Nghỉ phép',
                    'title' => $this->getLeaveTitle($leave),
                    'status' => $leave->workflow_status,
                    'status_label' => $leave->workflow_status_text,
                    'status_badge' => $this->getStatusBadge($leave->workflow_status),
                    'initiated_by' => $leave->employee ? $leave->employee->name : 'N/A',
                    'initiated_by_username' => $leave->employee && $leave->employee->user ? $leave->employee->user->username : 'N/A',
                    'created_at' => $leave->created_at,
                    'created_at_formatted' => $this->formatDateWithTimezone($leave->created_at, 'd/m/Y H:i'),
                    'period' => $this->getLeavePeriod($leave),
                    'can_approve' => $canApprove,
                    'can_reject' => PermissionHelper::can($user, 'leave.reject') && 
                                    !in_array($leave->approvalRequest->status ?? '', [
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_APPROVED,
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_REJECTED,
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_CANCELLED
                                    ]),
                    'needs_pin' => $needsPin,
                    'is_reviewer_step' => $isReviewerStep && $hasReviewPermission,
                    'has_selected_approvers' => $hasSelectedApprovers,
                    'can_approve_reviewer_step' => ($isReviewerStep && $hasReviewPermission) ? $hasSelectedApprovers : true,
                ];
            });
    }

    /**
     * Get vehicle registration requests
     */
    protected function getVehicleRequests($user, $filters)
    {
        $query = VehicleRegistration::query();

        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        
        // Filter by user permissions first (this may add workflow_status conditions for directors)
        $this->filterVehicleByUserPermissions($query, $user, $filters);

        // Apply status filter (only if not director, or if director but status is not 'all')
        // Map old status values to new approval_requests status
        if ($filters['status'] !== 'all' && !$isDirector) {
            if ($filters['status'] === 'pending') {
                $query->whereHas('approvalRequest', function($q) {
                    $q->whereIn('status', ['submitted', 'in_review'])
                      ->whereIn('current_step', ['vehicle_picked', 'department_head_approval']);
                });
            } elseif ($filters['status'] === 'director_review') {
                $query->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'in_review')
                      ->where('current_step', 'director_approval');
                });
            } elseif ($filters['status'] === 'approved') {
                $query->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'approved');
                });
            } elseif ($filters['status'] === 'rejected') {
                $query->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'rejected');
                });
            }
        }

        // Apply time range filter
        $this->applyTimeRangeFilter($query, $filters['time_range']);

        return $query->with(['user', 'vehicle', 'driver'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'model_type' => 'vehicle',
                    'type' => 'Official Cars',
                    'type_label' => 'Đăng ký xe',
                    'title' => $this->getVehicleTitle($vehicle),
                    'status' => $vehicle->approvalRequest ? $vehicle->approvalRequest->status : 'draft',
                    'status_label' => $vehicle->approvalRequest ? $vehicle->approvalRequest->status_label : 'Nháp',
                    'status_badge' => $vehicle->approvalRequest ? $this->getVehicleStatusBadge($vehicle->approvalRequest->status) : 'secondary',
                    'initiated_by' => $vehicle->user ? $vehicle->user->name : 'N/A',
                    'initiated_by_username' => $vehicle->user ? $vehicle->user->username : 'N/A',
                    'created_at' => $vehicle->created_at,
                    'created_at_formatted' => $this->formatDateWithTimezone($vehicle->created_at, 'd/m/Y H:i'),
                    'period' => $this->getVehiclePeriod($vehicle),
                    'can_approve' => $vehicle->canBeApproved() && $this->canUserApproveVehicle($vehicle, backpack_user()),
                    'can_reject' => PermissionHelper::can(backpack_user(), 'vehicle_registration.reject') && 
                                    $vehicle->approvalRequest && 
                                    !in_array($vehicle->approvalRequest->status ?? '', [
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_APPROVED,
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_REJECTED,
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_CANCELLED
                                    ]),
                ];
            });
    }

    /**
     * Get material plan requests
     */
    protected function getMaterialPlanRequests($user, $filters)
    {
        $query = MaterialPlan::query();

        // Apply status filter via approval_requests
        if ($filters['status'] !== 'all') {
            $query->whereHas('approvalRequest', function($arQ) use ($filters) {
                if ($filters['status'] === 'pending') {
                    $arQ->where('status', 'submitted')
                        ->where('current_step', 'review');
                } elseif ($filters['status'] === 'approved_by_department_head') {
                    $arQ->where('status', 'in_review')
                        ->where('current_step', 'review');
                } elseif ($filters['status'] === 'approved_by_reviewer') {
                    $arQ->where('status', 'in_review')
                        ->where('current_step', 'director_approval');
                } elseif ($filters['status'] === 'completed') {
                    $arQ->where('status', 'approved')
                        ->whereNull('current_step');
                } elseif ($filters['status'] === 'rejected') {
                    $arQ->where('status', 'rejected');
                } else {
                    // Try to map old status to new
                    $arQ->where('status', $filters['status']);
                }
            });
        }

        // Apply time range filter
        $this->applyTimeRangeFilter($query, $filters['time_range']);

        return $query->with(['nguoiLap', 'items.material'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($plan) use ($user) {
                // Check if user can approve this plan
                return $this->canUserApproveMaterialPlan($plan, $user);
            })
            ->map(function($plan) {
                return [
                    'id' => $plan->id,
                    'model_type' => 'material_plan',
                    'type' => 'Material Plans',
                    'type_label' => 'Phương án vật tư',
                    'title' => $plan->ten_khi_tai . ($plan->ky_hieu_khi_tai ? ' (' . $plan->ky_hieu_khi_tai . ')' : ''),
                    'status' => $plan->workflow_status,
                    'status_label' => $this->getMaterialPlanStatusLabel($plan->workflow_status),
                    'status_badge' => $this->getMaterialPlanStatusBadge($plan->workflow_status),
                    'initiated_by' => $plan->nguoiLap ? $plan->nguoiLap->name : 'N/A',
                    'initiated_by_username' => $plan->nguoiLap && $plan->nguoiLap->user ? $plan->nguoiLap->user->username : 'N/A',
                    'created_at' => $plan->created_at,
                    'created_at_formatted' => $this->formatDateWithTimezone($plan->created_at, 'd/m/Y H:i'),
                    'period' => $plan->ngay_vao_sua_chua ?? '-',
                    'can_approve' => $plan->canBeApproved() && $this->canUserApproveMaterialPlan($plan, backpack_user()),
                    'can_reject' => PermissionHelper::can(backpack_user(), 'material_plan.reject') && 
                                    !in_array($plan->workflow_status ?? '', ['approved', 'rejected', 'cancelled']),
                    'is_reviewer_step' => $plan->workflow_status === 'approved_by_department_head',
                    'has_selected_approvers' => !empty($plan->selected_approvers),
                ];
            });
    }

    /**
     * Check if user can approve material plan
     */
    protected function canUserApproveMaterialPlan($plan, $user)
    {
        // Admin can approve all
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Check permission
        if (!PermissionHelper::can($user, 'material_plan.approve')) {
            return false;
        }

        // Check if user is in selected_approvers for director step
        // MaterialPlan workflow: pending -> approved_by_department_head -> approved_by_reviewer (BGD) -> approved
        // Nếu có selected_approvers và workflow_status là pending hoặc approved_by_reviewer, check selected_approvers
        if (!empty($plan->selected_approvers)) {
            $userId = (int)$user->id;
            $selectedApprovers = is_array($plan->selected_approvers) ? $plan->selected_approvers : json_decode($plan->selected_approvers, true);
            
            // Nếu user là BGD và có trong selected_approvers, có thể approve
            $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
            if ($isDirector && (in_array($userId, $selectedApprovers) || in_array((string)$userId, $selectedApprovers))) {
                // Chỉ hiển thị nếu workflow_status là pending (chờ BGD) hoặc approved_by_reviewer
                return in_array($plan->workflow_status, ['pending', 'approved_by_reviewer']);
            }
        }

        // Nếu không có selected_approvers hoặc user không phải BGD, chỉ hiển thị nếu workflow_status là pending
        // (có thể là thẩm định hoặc các bước khác)
        return $plan->workflow_status === 'pending';
    }

    /**
     * Get material plan status label
     */
    protected function getMaterialPlanStatusLabel($status)
    {
        $labels = [
            'pending' => 'Chờ phê duyệt',
            'approved_by_department_head' => 'Thẩm định',
            'approved_by_reviewer' => 'BGD phê duyệt',
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Đã từ chối',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get material plan status badge
     */
    protected function getMaterialPlanStatusBadge($status)
    {
        $badges = [
            'pending' => 'orange',
            'approved_by_department_head' => 'info',
            'approved_by_reviewer' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];

        return $badges[$status] ?? 'secondary';
    }

    /**
     * Apply time range filter
     */
    protected function applyTimeRangeFilter($query, $timeRange)
    {
        switch ($timeRange) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->where('created_at', '>=', Carbon::now()->startOfWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', Carbon::now()->startOfMonth());
                break;
        }
    }

    /**
     * Filter leave requests by user permissions
     * NOTE: Status filter is already applied before this method is called
     */
    protected function filterLeaveByUserPermissions($query, $user, $filters = [])
    {
        // Admin sees all
        if ($user->hasRole('Admin')) {
            return;
        }
        
        // Check if user has permission to view all leave data
        if (PermissionHelper::can($user, 'leave.view.all')) {
            return; // No filtering - see all data
        }
        
        // Reviewer permissions - check based on rank type
        $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
        $hasOfficerReviewPermission = PermissionHelper::can($user, 'leave.review.officer');
        
        if ($hasReviewPermission || $hasOfficerReviewPermission) {
            // If has both permissions, see all - no filtering needed
            if ($hasReviewPermission && $hasOfficerReviewPermission) {
                return;
            }
            
            // Filter by rank type at query level for efficiency
            // Only filter if status is review step (approved_by_department_head)
            $statusFilter = $filters['status'] ?? 'all';
            $isReviewStep = $statusFilter === 'all' || $statusFilter === 'approved_by_department_head';
            
            if ($isReviewStep) {
                $query->whereHas('employee', function($q) use ($hasReviewPermission, $hasOfficerReviewPermission) {
                    if ($hasReviewPermission && !$hasOfficerReviewPermission) {
                        // Only see employee ranks (CNQP, QNCN, or has CN/QN in rank_code)
                        // Not officer ranks (only numbers and slashes)
                        $q->where(function($rankQ) {
                            $rankQ->where('rank_code', 'CNQP')
                                  ->orWhere('rank_code', 'QNCN')
                                  ->orWhere('rank_code', 'like', '%CN%')
                                  ->orWhere('rank_code', 'like', '%QN%');
                        });
                    } elseif (!$hasReviewPermission && $hasOfficerReviewPermission) {
                        // Only see officer ranks (only numbers and slashes, no CN/QN)
                        // Pattern: can have numbers, slashes, spaces, but no letters
                        $q->where(function($rankQ) {
                            // Match patterns like 1//, 2//, 3//, 1/, 2/, etc.
                            // But exclude if contains CN or QN
                            // Using REGEXP to match only numbers and slashes
                            $rankQ->whereRaw('rank_code REGEXP ?', ['^[0-9/ ]+$'])
                                  ->where('rank_code', 'not like', '%CN%')
                                  ->where('rank_code', 'not like', '%QN%')
                                  ->where('rank_code', 'not like', '%cn%')
                                  ->where('rank_code', 'not like', '%qn%');
                        });
                    }
                });
            }
            return;
        }

        // Director (BGD): see all requests at approved_by_reviewer step (waiting for BGD approval)
        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        if ($isDirector) {
            $statusFilter = $filters['status'] ?? 'all';
            $userId = (int)$user->id;

            // ✅ Sửa: For director, show requests ở bước director_approval (filter selected_approvers sẽ làm trong PHP)
            // Query level: chỉ filter theo step và status, không filter selected_approvers ở đây
            if ($statusFilter === 'all') {
                // Show all requests at director_approval step OR already approved by this user
                $query->where(function($q) use ($userId) {
                    $q->whereHas('approvalRequest', function($arQ) {
                        $arQ->where('status', 'in_review')
                            ->where('current_step', 'director_approval');
                    })
                    ->orWhereHas('approvalRequest', function($arQ) use ($userId) {
                        // ✅ Sửa: Lấy tất cả requests đã approved, filter selected_approvers sẽ làm trong PHP
                        $arQ->where('status', 'approved');
                    });
                });
            } elseif ($statusFilter === 'approved_by_reviewer' || $statusFilter === 'director_review') {
                // Chờ BGD phê duyệt
                $query->whereHas('approvalRequest', function($arQ) {
                    $arQ->where('status', 'in_review')
                        ->where('current_step', 'director_approval');
                });
            } elseif ($statusFilter === 'completed' || $statusFilter === 'approved_by_director') {
                // ✅ Sửa: Lấy tất cả requests đã approved, filter selected_approvers sẽ làm trong PHP
                $query->whereHas('approvalRequest', function($arQ) {
                    $arQ->where('status', 'approved');
                });
            }
            // Note: Filter selected_approvers sẽ được làm trong PHP filter sau khi get()
            return;
        }

        // Apply workflow step filtering for other users (status filter already applied)
        // This is just additional permission check - status filter ensures correct status
        $query->where(function($q) use ($user) {
            // User can see their own requests
            if ($user->employee_id) {
                $q->where('employee_id', $user->employee_id);
            }

            // Department head can see their department
            $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);
            if ($isDepartmentHead && $user->department_id) {
                $q->orWhereHas('employee', function($subQ) use ($user) {
                    $subQ->where('department_id', $user->department_id);
                });
            }
        });
    }

    /**
     * Filter vehicle requests by user permissions
     */
    protected function filterVehicleByUserPermissions($query, $user, $filters = [])
    {
        // Admin sees all
        if ($user->hasRole('Admin')) {
            return;
        }

        // Check if user has permission to view all vehicle data
        if (PermissionHelper::can($user, 'vehicle_registration.view.all')) {
            return; // No filtering - see all data
        }

        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        
        if ($isDirector) {
            $statusFilter = $filters['status'] ?? 'all';
            $userId = (int)$user->id;
            
            // For director, show all requests at director_approval step (waiting for BGD approval)
            if ($statusFilter === 'all') {
                // Show all requests at director_approval step OR already approved
                $query->whereHas('approvalRequest', function($q) use ($userId) {
                    $q->where(function($subQ) use ($userId) {
                        // At director_approval step
                        $subQ->where('status', 'in_review')
                             ->where('current_step', 'director_approval');
                        // OR already approved (check approval_history)
                        $subQ->orWhere(function($approvedQ) use ($userId) {
                            $approvedQ->where('status', 'approved')
                                      ->whereJsonContains('approval_history->director_approval->approved_by', $userId);
                        });
                    });
                });
            } elseif ($statusFilter === 'director_review') {
                $query->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'in_review')
                      ->where('current_step', 'director_approval');
                });
            } elseif ($statusFilter === 'approved') {
                $query->whereHas('approvalRequest', function($q) use ($userId) {
                    $q->where('status', 'approved')
                      ->whereJsonContains('approval_history->director_approval->approved_by', $userId);
                });
            }
            return;
        }

        // Apply filtering based on permissions
        $query->where(function($q) use ($user) {
            // User can see their own requests
            $q->where('user_id', $user->id);

            // Department head can see their department
            if ($user->department_id) {
                $q->orWhereHas('user', function($subQ) use ($user) {
                    $subQ->where('department_id', $user->department_id);
                });
            }
        });
    }

    /**
     * Get request details
     */
    public function getRequestDetails($id, $modelType)
    {
        switch ($modelType) {
            case 'leave':
                $model = EmployeeLeave::with(['employee.department'])->find($id);
                if (!$model) return null;

                $user = backpack_user();
                $isReviewerStep = $model->workflow_status === EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD;
                
                // Check review permission based on employee rank
                $rankCode = $model->employee ? $model->employee->rank_code : null;
                $isOfficer = $this->isOfficerRank($rankCode);
                $hasReviewPermission = false;
                if ($isReviewerStep) {
                    // At reviewer step, check permission based on rank
                    $hasReviewPermission = $isOfficer 
                        ? PermissionHelper::can($user, 'leave.review.officer')
                        : PermissionHelper::can($user, 'leave.review');
                }
                
                $needsPin = !($isReviewerStep && $hasReviewPermission);
                
                // ✅ Sửa: Lấy selected_approvers từ approvalRequest, không phải từ model
                $approvalRequest = $model->approvalRequest;
                $hasSelectedApprovers = false;
                if ($approvalRequest && $approvalRequest->selected_approvers) {
                    $selectedApprovers = is_array($approvalRequest->selected_approvers) 
                        ? $approvalRequest->selected_approvers 
                        : json_decode($approvalRequest->selected_approvers, true);
                    $hasSelectedApprovers = !empty($selectedApprovers);
                }
                
                // ✅ Sửa: Dùng approvalRequest->canBeApprovedBy() để kiểm tra quyền
                if ($approvalRequest) {
                    $canApprove = $approvalRequest->canBeApprovedBy($user);
                    $canReject = PermissionHelper::can($user, 'leave.reject') && 
                                 !in_array($approvalRequest->status, [
                                     \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_APPROVED,
                                     \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_REJECTED,
                                     \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_CANCELLED
                                 ]);
                } else {
                    // Fallback nếu chưa có approvalRequest
                    if ($isReviewerStep && $hasReviewPermission) {
                        $canApprove = $model->canBeApproved();
                        $canReject = PermissionHelper::can($user, 'leave.reject');
                    } else {
                        $canApprove = $model->canBeApproved() && $this->canUserApprove($model, $user);
                        $canReject = PermissionHelper::can($user, 'leave.reject');
                    }
                }

                return [
                    'id' => $model->id,
                    'model_type' => 'leave',
                    'type' => 'Nghỉ phép',
                    'type_label' => 'Nghỉ phép',
                    'title' => $this->getLeaveTitle($model),
                    'status' => $model->workflow_status,
                    'status_label' => $model->workflow_status_text,
                    'status_badge' => $this->getStatusBadge($model->workflow_status),
                    'submitted_by' => $model->employee ? $model->employee->name : 'N/A',
                    'submitted_at' => $this->formatVietnameseDate($model->created_at),
                    'details' => $this->getLeaveDetails($model),
                    'can_approve' => $canApprove,
                    // Flag to check if at reviewer step and can proceed (has selected approvers)
                    'can_approve_reviewer_step' => ($isReviewerStep && $hasReviewPermission) ? $hasSelectedApprovers : true,
                    'can_reject' => $canReject,
                    'needs_pin' => $needsPin,
                    'is_reviewer_step' => $isReviewerStep && $hasReviewPermission,
                    'is_reviewer_role' => $hasReviewPermission || $user->hasRole('Thẩm định'),
                    'has_selected_approvers' => $hasSelectedApprovers,
                    'selected_approvers' => $model->selected_approvers ? (is_array($model->selected_approvers) ? $model->selected_approvers : json_decode($model->selected_approvers, true)) : [],
                    'workflow_data' => $this->getWorkflowProgressData($model),
                    'has_signed_pdf' => $model->hasSignedPdf(),
                    'pdf_url' => $model->hasSignedPdf() ? route('approval.preview-pdf', ['modelClass' => base64_encode(get_class($model)), 'id' => $model->id]) : null,
                    'rejection_reason' => ($approvalRequest && ($approvalRequest->status === 'rejected' || $approvalRequest->status === 'returned')) ? ($approvalRequest->rejection_reason ?? null) : null,
                ];

            case 'vehicle':
                $model = VehicleRegistration::with(['user', 'vehicle', 'driver'])->find($id);
                if (!$model) {
                    return null;
                }
                $user = backpack_user();
                // Check if at department_head_approval step
                $approvalRequest = $model->approvalRequest;
                $isDepartmentHeadStep = $approvalRequest && 
                                       $approvalRequest->status === 'in_review' && 
                                       $approvalRequest->current_step === 'department_head_approval' &&
                                       $model->vehicle_id;
                
                // Check if at review step (Thẩm định)
                $isReviewStep = $approvalRequest && 
                               $approvalRequest->status === 'in_review' && 
                               $approvalRequest->current_step === 'review';
                
                // Check if department_head_approval was already approved (required for review step)
                $approvalHistory = $approvalRequest ? ($approvalRequest->approval_history ?? []) : [];
                if (is_string($approvalHistory)) {
                    $approvalHistory = json_decode($approvalHistory, true) ?? [];
                }
                $hasDepartmentHeadApproved = isset($approvalHistory['department_head_approval']) && 
                                            isset($approvalHistory['department_head_approval']['approved_by']);
                
                $hasDepartmentHeadPermission = $this->canUserApproveVehicle($model, $user);
                $hasReviewPermission = PermissionHelper::can($user, 'vehicle_registration.review');
                
                return [
                    'id' => $model->id,
                    'model_type' => 'vehicle',
                    'type' => 'Official Cars',
                    'type_label' => 'Đăng ký xe',
                    'title' => $this->getVehicleTitle($model),
                    'status' => $model->approvalRequest ? $model->approvalRequest->status : 'draft',
                    'status_label' => $model->approvalRequest ? $model->approvalRequest->status_label : 'Nháp',
                    'status_badge' => $model->approvalRequest ? $this->getVehicleStatusBadge($model->approvalRequest->status) : 'secondary',
                    'submitted_by' => $model->user ? $model->user->name : 'N/A',
                    'submitted_at' => $this->formatDateWithTimezone($model->created_at, 'd/m/Y, H:i'),
                    'details' => $this->getVehicleDetails($model),
                    'can_approve' => $model->canBeApproved() && $this->canUserApproveVehicle($model, $user),
                    'can_reject' => PermissionHelper::can($user, 'vehicle_registration.reject') && 
                                    $approvalRequest && 
                                    !in_array($approvalRequest->status, [
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_APPROVED,
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_REJECTED,
                                        \Modules\ApprovalWorkflow\Models\ApprovalRequest::STATUS_CANCELLED
                                    ]) &&
                                    !$this->isCurrentStepApproved($approvalRequest),
                    'is_department_head_step' => $isDepartmentHeadStep,
                    'is_reviewer_step' => $isReviewStep && $hasDepartmentHeadApproved && $hasReviewPermission,
                    'is_reviewer_role' => $isReviewStep && $hasReviewPermission,
                    'can_approve_reviewer_step' => $isReviewStep ? true : true, // Review step doesn't need selected approvers check
                    'has_selected_approvers' => !empty($model->selected_approvers) || 
                                               ($approvalRequest && !empty($approvalRequest->selected_approvers)),
                    'workflow_data' => $this->getVehicleWorkflowProgressData($model),
                    'has_signed_pdf' => $model->hasSignedPdf(),
                    'pdf_url' => $model->hasSignedPdf() ? route('approval.preview-pdf', ['modelClass' => base64_encode(get_class($model)), 'id' => $model->id]) : null,
                    'rejection_reason' => ($approvalRequest && ($approvalRequest->status === 'rejected' || $approvalRequest->status === 'returned')) ? ($approvalRequest->rejection_reason ?? null) : null,
                    'needs_pin' => $isDepartmentHeadStep, // Department head step needs PIN for digital signature
                ];

            case 'material_plan':
                $model = MaterialPlan::with(['nguoiLap', 'items.material'])->find($id);
                if (!$model) return null;

                $user = backpack_user();
                $hasSelectedApprovers = !empty($model->selected_approvers);

                return [
                    'id' => $model->id,
                    'model_type' => 'material_plan',
                    'type' => 'Material Plans',
                    'type_label' => 'Phương án vật tư',
                    'title' => $model->ten_khi_tai . ($model->ky_hieu_khi_tai ? ' (' . $model->ky_hieu_khi_tai . ')' : ''),
                    'status' => $model->workflow_status,
                    'status_label' => $this->getMaterialPlanStatusLabel($model->workflow_status),
                    'status_badge' => $this->getMaterialPlanStatusBadge($model->workflow_status),
                    'submitted_by' => $model->nguoiLap ? $model->nguoiLap->name : 'N/A',
                    'submitted_at' => $this->formatDateWithTimezone($model->created_at, 'd/m/Y H:i'),
                    'details' => $this->getMaterialPlanDetails($model),
                    'can_approve' => $model->canBeApproved() && $this->canUserApproveMaterialPlan($model, $user),
                    'can_reject' => PermissionHelper::can($user, 'material_plan.reject') && 
                                    !in_array($model->workflow_status ?? '', ['approved', 'rejected', 'cancelled']),
                    'is_reviewer_step' => $model->workflow_status === 'approved_by_department_head',
                    'has_selected_approvers' => $hasSelectedApprovers,
                    'selected_approvers' => $model->selected_approvers ? (is_array($model->selected_approvers) ? $model->selected_approvers : json_decode($model->selected_approvers, true)) : [],
                    'workflow_data' => $this->getMaterialPlanWorkflowProgressData($model),
                    'has_signed_pdf' => $model->hasSignedPdf(),
                    'pdf_url' => $model->hasSignedPdf() ? route('approval.preview-pdf', ['modelClass' => base64_encode(get_class($model)), 'id' => $model->id]) : null,
                ];

            default:
                return null;
        }
    }

    /**
     * Get approval history
     */
    public function getApprovalHistory($id, $modelType)
    {
        $modelClass = $this->getModelClass($modelType);
        if (!$modelClass) return [];

        $model = $modelClass::find($id);
        if (!$model) return [];

        // Read from approval_requests.approval_history JSON field
        $approvalRequest = $model->approvalRequest;
        if (!$approvalRequest || !$approvalRequest->approval_history) {
            return [];
        }

        $history = is_string($approvalRequest->approval_history) 
            ? json_decode($approvalRequest->approval_history, true) 
            : $approvalRequest->approval_history;

        if (!is_array($history)) {
            return [];
        }

        $result = [];
        foreach ($history as $step => $stepData) {
            $stepName = $this->getStepNameFromKey($step, $modelType);
            $result[] = [
                'step_name' => $stepName,
                'approver' => $stepData['approved_by_name'] ?? 'N/A',
                'result' => $this->getActionDisplay($stepData['action'] ?? 'unknown'),
                'result_badge' => $this->getActionBadge($stepData['action'] ?? 'unknown'),
                'comment' => $stepData['comment'] ?? null,
                'time' => isset($stepData['approved_at']) 
                    ? $this->formatDateWithTimezone(\Carbon\Carbon::parse($stepData['approved_at']), 'd M, H:i')
                    : null,
                'time_relative' => isset($stepData['approved_at']) 
                    ? \Carbon\Carbon::parse($stepData['approved_at'])->diffForHumans()
                    : null,
            ];
        }

        return $result;
    }

    protected function getStepNameFromKey($stepKey, $modelType)
    {
        $stepNames = [
            'leave' => [
                'department_head_approval' => 'Trưởng phòng duyệt',
                'review' => 'Thẩm định',
                'director_approval' => 'BGD duyệt',
            ],
            'vehicle' => [
                'vehicle_picked' => 'Phân xe',
                'department_head_approval' => 'Trưởng phòng KH duyệt',
                'director_approval' => 'BGD duyệt',
            ],
        ];

        return $stepNames[$modelType][$stepKey] ?? $stepKey;
    }

    protected function getActionDisplay($action)
    {
        $actions = [
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Đã từ chối',
            'cancelled' => 'Đã hủy',
            'returned' => 'Đã trả lại',
        ];

        return $actions[$action] ?? $action;
    }

    /**
     * Approve request
     */
    public function approveRequest($id, $modelType, $user, $comment = '', $certificatePin = null)
    {
        // Get ApprovalRequest directly
        $approvalRequest = \Modules\ApprovalWorkflow\Models\ApprovalRequest::where('model_type', $this->getModelClassFromType($modelType))
            ->where('model_id', $id)
            ->first();

        if (!$approvalRequest) {
            throw new \Exception('Không tìm thấy yêu cầu phê duyệt');
        }

        // Check if needs PIN based on step
        $needsPin = $this->needsPinForStep($approvalRequest, $user);

        // If no PIN needed, approve without PIN
        if (!$needsPin) {
            if (!$user) {
                $user = backpack_user();
            }
            if (!$user) {
                $user = auth()->user();
            }
            
            $workflowEngine = app(\App\Services\WorkflowEngine::class);
            $workflowEngine->processApprovalStep($approvalRequest, 'approved', $comment, null, $user);
            
            $approvalRequest->refresh();
            
            return [
                'success' => true,
                'message' => 'Đã chuyển lên BGD thành công!',
                'data' => [
                    'status' => $approvalRequest->status,
                    'current_step' => $approvalRequest->current_step,
                ],
            ];
        }

        // For steps that need PIN, require PIN
        if (!$certificatePin) {
            throw new \Exception('Vui lòng nhập mã PIN để phê duyệt');
        }

        // Approve with PIN using ApprovalService
        $model = $this->getModel($id, $modelType);
        if (!$model) {
            throw new \Exception('Request not found');
        }

        $approvalService = app(\Modules\ApprovalWorkflow\Services\ApprovalService::class);
        $result = $approvalService->approveWithSignature(
            $model,
            $user,
            $certificatePin,
            ['comment' => $comment]
        );

        return [
            'success' => true,
            'message' => 'Phê duyệt thành công',
            'data' => $result,
        ];
    }

    /**
     * Check if step needs PIN
     */
    protected function needsPinForStep(\Modules\ApprovalWorkflow\Models\ApprovalRequest $approvalRequest, $user): bool
    {
        // Steps that don't need PIN (intermediate steps)
        $noPinSteps = ['review', 'department_head_approval'];
        
        if (in_array($approvalRequest->current_step, $noPinSteps)) {
            return false;
        }

        // Last step (director_approval) needs PIN
        return true;
    }

    /**
     * Get model class from model type (helper method)
     */
    protected function getModelClassFromType(string $modelType): string
    {
        $map = [
            'leave' => \Modules\PersonnelReport\Models\EmployeeLeave::class,
            'vehicle' => \Modules\VehicleRegistration\Models\VehicleRegistration::class,
            'material_plan' => \Modules\ProductionManagement\Models\MaterialPlan::class,
        ];

        return $map[$modelType] ?? '';
    }

    /**
     * Reject request
     */
    public function rejectRequest($id, $modelType, $user, $reason = '')
    {
        $approvalRequest = \Modules\ApprovalWorkflow\Models\ApprovalRequest::where('model_type', $this->getModelClassFromType($modelType))
            ->where('model_id', $id)
            ->first();

        if (!$approvalRequest) {
            throw new \Exception('Không tìm thấy yêu cầu phê duyệt');
        }

        if (!$user) {
            $user = backpack_user();
        }
        if (!$user) {
            $user = auth()->user();
        }

        $workflowEngine = app(\App\Services\WorkflowEngine::class);
        $workflowEngine->processApprovalStep($approvalRequest, 'rejected', $reason, null, $user);
        
        $approvalRequest->refresh();

        return [
            'success' => true,
            'message' => 'Đã từ chối thành công',
            'data' => [
                'status' => $approvalRequest->status,
                'current_step' => $approvalRequest->current_step,
            ],
        ];
    }

    // Helper methods
    protected function getModel($id, $modelType)
    {
        switch ($modelType) {
            case 'leave':
                return EmployeeLeave::find($id);
            case 'vehicle':
                return VehicleRegistration::find($id);
            case 'material_plan':
                return MaterialPlan::find($id);
            default:
                return null;
        }
    }

    protected function getModelClass($modelType)
    {
        switch ($modelType) {
            case 'leave':
                return EmployeeLeave::class;
            case 'vehicle':
                return VehicleRegistration::class;
            case 'material_plan':
                return MaterialPlan::class;
            default:
                return null;
        }
    }

    protected function getLeaveTitle($leave)
    {
        return $leave->leave_type_text ?? 'Nghỉ phép';
    }

    protected function getLeavePeriod($leave)
    {
        $from = $leave->from_date ? $leave->from_date->format('d/m/Y') : 'N/A';
        $to = $leave->to_date ? $leave->to_date->format('d/m/Y') : 'N/A';

        return "{$from} đến {$to}";
    }

    protected function getLeaveDetails($leave)
    {
        return [
            'Hình thức nghỉ phép' => $leave->leave_type_text ?? 'N/A',
            'Lý do nghỉ phép' => $leave->note ?? 'N/A',
            'Thời gian bắt đầu' => $leave->from_date ? $leave->from_date->format('d/m/Y') : 'N/A',
            'Thời gian kết thúc' => $leave->to_date ? $leave->to_date->format('d/m/Y') : 'N/A',
            'Khoảng thời gian' => $this->calculateLeaveDuration($leave),
        ];
    }

    protected function calculateLeaveDuration($leave)
    {
        if (!$leave->from_date || !$leave->to_date) {
            return 'N/A';
        }

        $days = $leave->from_date->diffInDays($leave->to_date) + 1;
        return "{$days} ngày";
    }

    protected function getVehicleTitle($vehicle)
    {
        return $vehicle->purpose ?? 'Đăng ký xe công';
    }

    protected function getVehiclePeriod($vehicle)
    {
        if ($vehicle->departure_datetime && $vehicle->return_datetime) {
            $from = $this->formatDateWithTimezone($vehicle->departure_datetime, 'd/m/Y');
            $to = $this->formatDateWithTimezone($vehicle->return_datetime, 'd/m/Y');
            return "{$from} - {$to}";
        } elseif ($vehicle->departure_date && $vehicle->return_date) {
            $from = $this->formatDateWithTimezone($vehicle->departure_date, 'd/m/Y');
            $to = $this->formatDateWithTimezone($vehicle->return_date, 'd/m/Y');
            return "{$from} - {$to}";
        }
        return 'N/A';
    }

    protected function getVehicleDetails($vehicle)
    {
        return [
            'Lý do' => $vehicle->purpose ?? 'N/A',
            'Thời gian bắt đầu' => $vehicle->departure_datetime ? $this->formatDateWithTimezone($vehicle->departure_datetime, 'd/m/Y') : ($vehicle->departure_date ? $this->formatDateWithTimezone($vehicle->departure_date, 'd/m/Y') : 'N/A'),
            'Thời gian kết thúc' => $vehicle->return_datetime ? $this->formatDateWithTimezone($vehicle->return_datetime, 'd/m/Y') : ($vehicle->return_date ? $this->formatDateWithTimezone($vehicle->return_date, 'd/m/Y') : 'N/A'),
            'Tuyến đường' => $vehicle->route ?? 'N/A',
            'Xe' => $vehicle->vehicle ? $vehicle->vehicle->name : 'N/A',
            'Tài xế' => $vehicle->driver ? $vehicle->driver->name : 'N/A',
        ];
    }

    /**
     * Get Tabler badge class for leave status
     * Returns Tabler badge classes: bg-orange, bg-azure, bg-indigo, bg-green, bg-red
     * Uses centralized helper function for consistency
     */
    protected function getStatusBadge($status)
    {
        return getStatusBadgeColor($status, 'leave');
    }

    /**
     * Get Tabler badge class name (full class string)
     * Always uses text-white for consistency
     */
    public function getTablerBadgeClass($status, $modelType = 'leave')
    {
        if ($modelType === 'leave') {
            $color = $this->getStatusBadge($status);
        } else {
            $color = $this->getVehicleStatusBadge($status);
        }

        // Always use white text for consistency
        return "badge bg-{$color} text-white badge-pill";
    }

    protected function getVehicleStatusLabel($status)
    {
        $labels = [
            'submitted' => 'Đã gửi',
            'dept_review' => 'Đang được xét duyệt',
            'director_review' => 'Chờ BGD phê duyệt',
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Đã từ chối',
        ];

        return $labels[$status] ?? $status;
    }

    protected function getVehicleStatusBadge($status)
    {
        // Uses centralized helper function for consistency
        return getStatusBadgeColor($status, 'vehicle');
    }

    protected function getActionBadge($action)
    {
        $badges = [
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            'returned' => 'warning',
        ];

        return $badges[$action] ?? 'secondary';
    }

    protected function getStepName($level, $action)
    {
        if ($action === 'submitted') {
            return 'Gửi';
        }

        return "Phê duyệt cấp {$level}";
    }

    protected function canUserApprove($leave, $user)
    {
        // Check if user can approve this leave at current step
        if ($user->hasRole('Admin')) {
            return true;
        }

        $status = $leave->workflow_status;

        // Step 1: Only department head of the employee's department can approve
        if ($status === EmployeeLeave::WORKFLOW_PENDING) {
            if (!$leave->employee || !$leave->employee->department_id) {
                return false;
            }

            $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);
            if (!$isDepartmentHead) {
                return false;
            }

            $userDepartmentId = $user->department_id;
            if ($user->employee_id) {
                $emp = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                if ($emp && $emp->department_id) {
                    $userDepartmentId = $emp->department_id;
                }
            }

            return $userDepartmentId == $leave->employee->department_id;
        }

        // Step 2: Only reviewer can approve (check by role or permission based on rank)
        if ($status === EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD) {
            // Get employee rank_code to determine which permission to check
            $rankCode = $leave->employee ? $leave->employee->rank_code : null;
            $isOfficer = $this->isOfficerRank($rankCode);
            
            // Check by role (admin-like roles can approve all)
            if ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Thẩm định'])) {
                return true;
            }
            
            // Check by permission based on rank type
            if ($isOfficer) {
                // Officer rank - need leave.review.officer permission
                return PermissionHelper::can($user, 'leave.review.officer');
            } else {
                // Employee rank (CNQP, QNCN) - need leave.review permission
                return PermissionHelper::can($user, 'leave.review');
            }
        }

        // Step 3: Only director (BGD) can approve - but only if they are in selected_approvers list
        if ($status === EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER) {
            $hasDirectorRole = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
            if (!$hasDirectorRole) {
                return false;
            }

            // Check if user is in selected_approvers list
            return $leave->isUserSelectedApprover($user->id);
        }

        // Legacy support
        if ($status === 'approved_by_approver') {
            return $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Thẩm định']);
        }

        return false;
    }

    protected function canUserApproveVehicle($vehicle, $user)
    {
        if ($user->hasRole('Admin')) {
            return true;
        }

        if (!PermissionHelper::can($user, 'vehicle_registration.approve')) {
            return false;
        }

        // Get approval request to check current step
        $approvalRequest = $vehicle->approvalRequest;
        if (!$approvalRequest) {
            return false;
        }

        $currentStep = $approvalRequest->current_step;
        $status = $approvalRequest->status;
        
        // Step 1: vehicle_picked - Đội trưởng xe phân công (handled separately)
        // Step 2: department_head_approval - Trưởng phòng kế hoạch duyệt
        if ($currentStep === 'department_head_approval' && $status === 'in_review') {
            $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban', 'Trưởng phòng kế hoạch']);
            return $isDepartmentHead;
        }
        
        // Step 3: review - Thẩm định (chọn người phê duyệt)
        if ($currentStep === 'review' && $status === 'in_review') {
            // Check if department_head_approval was already approved
            $approvalHistory = $approvalRequest->approval_history ?? [];
            if (is_string($approvalHistory)) {
                $approvalHistory = json_decode($approvalHistory, true) ?? [];
            }
            
            // Only show if department_head_approval was approved
            if (!isset($approvalHistory['department_head_approval']) || 
                !isset($approvalHistory['department_head_approval']['approved_by'])) {
                return false;
            }
            
            // Check permission for review step
            return PermissionHelper::can($user, 'vehicle_registration.review');
        }
        
        // Step 4: director_approval - Ban Giám đốc duyệt
        if ($currentStep === 'director_approval' && $status === 'in_review') {
            $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
            if (!$isDirector) {
                return false;
            }
            
            // Check if user is in selected_approvers
            if (method_exists($vehicle, 'isUserSelectedApprover')) {
                return $vehicle->isUserSelectedApprover($user->id);
            }
            
            // Fallback: check selected_approvers from approvalRequest
            $selectedApprovers = $approvalRequest->selected_approvers;
            if (!$selectedApprovers) {
                return false;
            }
            
            $approverIds = is_array($selectedApprovers)
                ? $selectedApprovers
                : json_decode($selectedApprovers, true);
            
            if (!is_array($approverIds)) {
                return false;
            }
            
            // Flatten if nested by step
            $allApprovers = [];
            foreach ($approverIds as $stepApprovers) {
                if (is_array($stepApprovers)) {
                    $allApprovers = array_merge($allApprovers, $stepApprovers);
                } else {
                    $allApprovers[] = $stepApprovers;
                }
            }
            
            return in_array((int)$user->id, array_map('intval', $allApprovers));
        }
        
        // Legacy support: check old workflow_status if approvalRequest doesn't have current_step
        $oldStatus = $vehicle->workflow_status ?? null;
        if ($oldStatus === 'dept_review') {
            $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);
            return $isDepartmentHead;
        }
        
        if ($oldStatus === 'director_review') {
            $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
            return $isDirector;
        }
        
        return false;
    }

    /**
     * Format date in Vietnamese format
     */
    protected function formatVietnameseDate($date)
    {
        if (!$date) {
            return 'N/A';
        }

        if (!$date instanceof \Carbon\Carbon) {
            $date = \Carbon\Carbon::parse($date);
        }

        // Convert to Vietnam timezone (UTC+7)
        $date->setTimezone('Asia/Ho_Chi_Minh');

        // Format: dd/mm/yyyy, HH:mm
        return $date->format('d/m/Y, H:i');
    }

    /**
     * Format date with timezone conversion to Asia/Ho_Chi_Minh
     */
    protected function formatDateWithTimezone($date, $format = 'd/m/Y H:i')
    {
        if (!$date) {
            return 'N/A';
        }

        if (!$date instanceof \Carbon\Carbon) {
            $date = \Carbon\Carbon::parse($date);
        }

        // Convert to Vietnam timezone (UTC+7)
        $date->setTimezone('Asia/Ho_Chi_Minh');

        return $date->format($format);
    }

    /**
     * Get workflow progress data for timeline component
     */
    public function getWorkflowProgressData($model)
    {
        if (!($model instanceof EmployeeLeave)) {
            return null;
        }

        // ✅ QUAN TRỌNG: Dùng approvalRequest->current_step thay vì workflow_status
        $approvalRequest = $model->approvalRequest;
        if (!$approvalRequest) {
            // Fallback nếu chưa có approvalRequest
            return $this->getWorkflowProgressDataLegacy($model);
        }

        $steps = [
            [
                'key' => 'created',
                'label' => 'Tạo đơn'
            ],
            [
                'key' => 'department_head_approval',
                'label' => 'Chỉ huy xác nhận'
            ],
            [
                'key' => 'review',
                'label' => 'Thẩm định'
            ],
            [
                'key' => 'director_approval',
                'label' => 'BGD phê duyệt'
            ]
        ];

        $stepDates = [];
        $stepUsers = [];
        $approvalHistory = $approvalRequest->approval_history ?? [];
        
        if (is_string($approvalHistory)) {
            $approvalHistory = json_decode($approvalHistory, true) ?? [];
        }

        if ($model->created_at) {
            $stepDates['created'] = $this->formatDateWithTimezone($model->created_at, 'd/m/Y H:i');
        }
        if ($model->employee) {
            $stepUsers['created'] = $model->employee->name ?? 'N/A';
        }

        if (isset($approvalHistory['department_head_approval'])) {
            $deptHeadHistory = $approvalHistory['department_head_approval'];
            if (is_array($deptHeadHistory) && isset($deptHeadHistory[0])) {
                $deptHeadHistory = $deptHeadHistory[0];
            }
            
            if (is_array($deptHeadHistory)) {
                if (isset($deptHeadHistory['approved_at'])) {
                    $approvedAt = $deptHeadHistory['approved_at'];
                    if (is_string($approvedAt)) {
                        try {
                            $stepDates['department_head_approval'] = $this->formatDateWithTimezone($approvedAt, 'd/m/Y H:i');
                        } catch (\Exception $e) {
                            $stepDates['department_head_approval'] = $approvedAt;
                        }
                    }
                }
                if (isset($deptHeadHistory['approved_by_name'])) {
                    $stepUsers['department_head_approval'] = $deptHeadHistory['approved_by_name'];
                } elseif (isset($deptHeadHistory['approved_by'])) {
                    $approvedBy = $deptHeadHistory['approved_by'];
                    if ($approvedBy) {
                        $deptHead = \App\Models\User::find($approvedBy);
                        if ($deptHead) {
                            $stepUsers['department_head_approval'] = $deptHead->name ?? 'N/A';
                        }
                    }
                }
            }
        }

        if (isset($approvalHistory['review'])) {
            $reviewHistory = $approvalHistory['review'];
            if (is_array($reviewHistory) && isset($reviewHistory[0])) {
                $reviewHistory = $reviewHistory[0];
            }
            
            if (is_array($reviewHistory)) {
                if (isset($reviewHistory['approved_at'])) {
                    $approvedAt = $reviewHistory['approved_at'];
                    if (is_string($approvedAt)) {
                        try {
                            $stepDates['review'] = $this->formatDateWithTimezone($approvedAt, 'd/m/Y H:i');
                        } catch (\Exception $e) {
                            $stepDates['review'] = $approvedAt;
                        }
                    }
                }
                if (isset($reviewHistory['approved_by_name'])) {
                    $stepUsers['review'] = $reviewHistory['approved_by_name'];
                } elseif (isset($reviewHistory['approved_by'])) {
                    $approvedBy = $reviewHistory['approved_by'];
                    if ($approvedBy) {
                        $reviewer = \App\Models\User::find($approvedBy);
                        if ($reviewer) {
                            $stepUsers['review'] = $reviewer->name ?? 'N/A';
                        }
                    }
                }
            }
        }

        if (isset($approvalHistory['director_approval'])) {
            $directorHistory = $approvalHistory['director_approval'];
            if (is_array($directorHistory) && isset($directorHistory[0])) {
                $directorHistory = $directorHistory[0];
            }
            
            if (is_array($directorHistory)) {
                if (isset($directorHistory['approved_at'])) {
                    $approvedAt = $directorHistory['approved_at'];
                    if (is_string($approvedAt)) {
                        try {
                            $stepDates['director_approval'] = $this->formatDateWithTimezone($approvedAt, 'd/m/Y H:i');
                        } catch (\Exception $e) {
                            $stepDates['director_approval'] = $approvedAt;
                        }
                    }
                }
                if (isset($directorHistory['approved_by_name'])) {
                    $stepUsers['director_approval'] = $directorHistory['approved_by_name'];
                } elseif (isset($directorHistory['approved_by'])) {
                    $approvedBy = $directorHistory['approved_by'];
                    if ($approvedBy) {
                        $director = \App\Models\User::find($approvedBy);
                        if ($director) {
                            $stepUsers['director_approval'] = $director->name ?? 'N/A';
                        }
                    }
                }
            }
        }

        // Xác định currentStepIndex dựa trên current_step từ approvalRequest
        $currentStep = $approvalRequest->current_step;
        $currentStepIndex = 0;
        
        // Map current_step sang step index
        $stepIndexMap = [
            'department_head_approval' => 1,
            'review' => 2,
            'director_approval' => 3,
        ];
        
        if ($approvalRequest->status === 'approved') {
            $currentStepIndex = 4; // Completed
        } elseif ($approvalRequest->status === 'rejected') {
            // Rejected: xác định step dựa trên rejection_step hoặc current_step
            $rejectionStep = $approvalRequest->rejection_step ?? $currentStep;
            $currentStepIndex = $stepIndexMap[$rejectionStep] ?? 1;
        } elseif ($currentStep) {
            $currentStepIndex = $stepIndexMap[$currentStep] ?? 0;
        } else {
            // No current step - should be at created step
            $currentStepIndex = 0;
        }

        return [
            'steps' => $steps,
            'currentStatus' => $approvalRequest->status,
            'currentStep' => $currentStep,
            'currentStepIndex' => $currentStepIndex,
            'rejected' => $approvalRequest->status === 'rejected',
            'stepDates' => $stepDates,
            'stepUsers' => $stepUsers
        ];
    }

    /**
     * Legacy method for backward compatibility
     */
    protected function getWorkflowProgressDataLegacy($model)
    {
        // Old logic using workflow_status - kept for backward compatibility
        $steps = [
            [
                'key' => 'created',
                'label' => 'Tạo đơn'
            ],
            [
                'key' => EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD,
                'label' => 'Chỉ huy xác nhận'
            ],
            [
                'key' => EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER,
                'label' => 'Thẩm định'
            ],
            [
                'key' => EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR,
                'label' => 'BGD phê duyệt'
            ]
        ];

        $stepDates = [];
        $stepUsers = [];

        if ($model->created_at) {
            $stepDates['created'] = $this->formatDateWithTimezone($model->created_at, 'd/m/Y H:i');
        }
        if ($model->employee) {
            $stepUsers['created'] = $model->employee->name ?? 'N/A';
        }

        $currentStatusKey = $model->workflow_status;
        $currentStepIndex = 0;

        switch ($currentStatusKey) {
            case EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR:
                $currentStepIndex = 4;
                break;
            case EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER:
                $currentStepIndex = 3;
                break;
            case EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD:
                $currentStepIndex = 2;
                break;
            case EmployeeLeave::WORKFLOW_PENDING:
                $currentStepIndex = 1;
                break;
            default:
                $currentStepIndex = 1;
                break;
        }

        return [
            'steps' => $steps,
            'currentStatus' => $currentStatusKey,
            'currentStepIndex' => $currentStepIndex,
            'rejected' => $model->workflow_status === EmployeeLeave::WORKFLOW_REJECTED,
            'stepDates' => $stepDates,
            'stepUsers' => $stepUsers
        ];
    }

    public function getVehicleWorkflowProgressData($model)
    {
        if (!($model instanceof VehicleRegistration)) {
            return null;
        }

        $steps = [
            [
                'key' => 'created',
                'label' => 'Tạo đơn'
            ],
            [
                'key' => 'vehicle_assigned',
                'label' => 'Phân xe'
            ],
            [
                'key' => 'dept_review',
                'label' => 'Trưởng phòng KH duyệt'
            ],
            [
                'key' => 'review',
                'label' => 'Thẩm định'
            ],
            [
                'key' => 'approved',
                'label' => 'BGD duyệt'
            ]
        ];

        $stepDates = [];
        $stepUsers = [];

        if ($model->created_at) {
            $stepDates['created'] = $this->formatDateWithTimezone($model->created_at, 'd/m/Y H:i');
        }
        if ($model->user) {
            $stepUsers['created'] = $model->user->name ?? 'N/A';
        }

        if ($model->vehicle_id) {
            $assignedAt = $model->updated_at;
            if ($model->vehicle && $model->vehicle->created_at) {
                $assignedAt = $model->updated_at;
            }
            $stepDates['vehicle_assigned'] = $this->formatDateWithTimezone($assignedAt, 'd/m/Y H:i');
            if ($model->vehicle) {
                $stepUsers['vehicle_assigned'] = $model->vehicle->name ?? 'N/A';
            }
        }

        // Read from approval_history instead of old columns
        $approvalRequest = $model->approvalRequest;
        if ($approvalRequest && $approvalRequest->approval_history) {
            $history = is_string($approvalRequest->approval_history) 
                ? json_decode($approvalRequest->approval_history, true) 
                : $approvalRequest->approval_history;
            
            // Check department_head_approval step (maps to dept_review in display)
            if (isset($history['department_head_approval'])) {
                $deptStep = $history['department_head_approval'];
                if (isset($deptStep['approved_at'])) {
                    try {
                        $stepDates['dept_review'] = $this->formatDateWithTimezone(
                            \Carbon\Carbon::parse($deptStep['approved_at']), 
                            'd/m/Y H:i'
                        );
                    } catch (\Exception $e) {
                        // Fallback if date parsing fails
                        $stepDates['dept_review'] = $deptStep['approved_at'];
                    }
                }
                if (isset($deptStep['approved_by_name']) && !empty($deptStep['approved_by_name'])) {
                    $stepUsers['dept_review'] = $deptStep['approved_by_name'];
                } elseif (isset($deptStep['approved_by'])) {
                    // Fallback: get name from user ID
                    $deptApprover = \App\Models\User::find($deptStep['approved_by']);
                    if ($deptApprover) {
                        $stepUsers['dept_review'] = $deptApprover->name ?? 'N/A';
                    }
                }
            }
            
            // Check review step (Thẩm định)
            if (isset($history['review'])) {
                $reviewStep = $history['review'];
                if (isset($reviewStep['approved_at'])) {
                    try {
                        $stepDates['review'] = $this->formatDateWithTimezone(
                            \Carbon\Carbon::parse($reviewStep['approved_at']), 
                            'd/m/Y H:i'
                        );
                    } catch (\Exception $e) {
                        $stepDates['review'] = $reviewStep['approved_at'];
                    }
                }
                if (isset($reviewStep['approved_by_name']) && !empty($reviewStep['approved_by_name'])) {
                    $stepUsers['review'] = $reviewStep['approved_by_name'];
                } elseif (isset($reviewStep['approved_by'])) {
                    $reviewApprover = \App\Models\User::find($reviewStep['approved_by']);
                    if ($reviewApprover) {
                        $stepUsers['review'] = $reviewApprover->name ?? 'N/A';
                    }
                }
            }
            
            // Check director_approval step
            if (isset($history['director_approval'])) {
                $directorStep = $history['director_approval'];
                if (isset($directorStep['approved_at'])) {
                    $stepDates['approved'] = $this->formatDateWithTimezone(
                        \Carbon\Carbon::parse($directorStep['approved_at']), 
                        'd/m/Y H:i'
                    );
                }
                if (isset($directorStep['approved_by_name'])) {
                    $stepUsers['approved'] = $directorStep['approved_by_name'] ?? 'N/A';
                }
            }
        } else {
            // Fallback to old columns if approval_history not available
            if ($model->department_approved_at) {
                $stepDates['dept_review'] = $this->formatDateWithTimezone($model->department_approved_at, 'd/m/Y H:i');
            }
            if ($model->department_approved_by) {
                $deptApprover = \App\Models\User::find($model->department_approved_by);
                if ($deptApprover) {
                    $stepUsers['dept_review'] = $deptApprover->name ?? 'N/A';
                }
            }

            if ($model->director_approved_at) {
                $stepDates['approved'] = $this->formatDateWithTimezone($model->director_approved_at, 'd/m/Y H:i');
            } elseif ($model->director_approved_by && $model->workflow_status === 'approved') {
                $stepDates['approved'] = $this->formatDateWithTimezone(now(), 'd/m/Y H:i');
            }
            if ($model->director_approved_by) {
                $director = \App\Models\User::find($model->director_approved_by);
                if ($director) {
                    $stepUsers['approved'] = $director->name ?? 'N/A';
                }
            }
        }

        // Determine current step index based on approval_history
        $currentStepIndex = 0;
        $approvalRequest = $model->approvalRequest;
        $currentStatusKey = $approvalRequest ? $approvalRequest->status : ($model->workflow_status ?? 'submitted');
        
        if ($approvalRequest && $approvalRequest->approval_history) {
            $history = is_string($approvalRequest->approval_history) 
                ? json_decode($approvalRequest->approval_history, true) 
                : $approvalRequest->approval_history;
            
            if (is_array($history)) {
                // Helper function to check if a step is approved
                $isStepApproved = function($stepKey) use ($history) {
                    if (!isset($history[$stepKey])) {
                        return false;
                    }
                    $stepData = $history[$stepKey];
                    // Check if action is 'approved' OR if approved_at and approved_by exist (legacy data)
                    return (isset($stepData['action']) && $stepData['action'] === 'approved') ||
                           (isset($stepData['approved_at']) && isset($stepData['approved_by']));
                };
                
                // Step 0: created (always true if model exists)
                $currentStepIndex = 0;
                
                // Step 1: vehicle_assigned
                if ($model->vehicle_id) {
                    $currentStepIndex = 1;
                }
                
                // Step 2: department_head_approval (dept_review)
                if ($isStepApproved('department_head_approval')) {
                    $currentStepIndex = 2;
                }
                
                // Step 3: review (Thẩm định)
                if ($isStepApproved('review')) {
                    $currentStepIndex = 3;
                }
                
                // Step 4: director_approval (approved)
                if ($isStepApproved('director_approval')) {
                    $currentStepIndex = 4;
                }
            }
        }
        
        // Fallback to old logic if approval_history not available
        if (!$approvalRequest || !$approvalRequest->approval_history) {
            $currentStatusKey = $model->workflow_status ?? 'submitted';
            
            switch ($currentStatusKey) {
                case 'approved':
                    $currentStepIndex = 3;
                    break;
                case 'dept_review':
                    if ($model->vehicle_id && $model->department_approved_at) {
                        $currentStepIndex = 3;
                    } elseif ($model->vehicle_id) {
                        $currentStepIndex = 2;
                    } else {
                        $currentStepIndex = 1;
                    }
                    break;
                case 'submitted':
                    if ($model->vehicle_id) {
                        $currentStepIndex = 1;
                    } else {
                        $currentStepIndex = 0;
                    }
                    break;
                case 'rejected':
                    if ($model->rejection_level === 'director') {
                        $currentStepIndex = 3;
                    } elseif ($model->rejection_level === 'department') {
                        $currentStepIndex = 2;
                    } elseif ($model->director_approved_at) {
                        $currentStepIndex = 3;
                    } elseif ($model->department_approved_at) {
                        $currentStepIndex = 3;
                    } elseif ($model->vehicle_id) {
                        $currentStepIndex = 2;
                    } else {
                        $currentStepIndex = 0;
                    }
                    break;
                default:
                    if ($model->director_approved_at) {
                        $currentStepIndex = 3;
                    } elseif ($model->department_approved_at) {
                        $currentStepIndex = 2;
                    } elseif ($model->vehicle_id) {
                        $currentStepIndex = 1;
                    } else {
                        $currentStepIndex = 0;
                    }
                    break;
            }
        }

        $rejectionReason = null;
        if ($approvalRequest && ($approvalRequest->status === 'rejected' || $approvalRequest->status === 'returned')) {
            $rejectionReason = $approvalRequest->rejection_reason ?? null;
        }
        
        return [
            'steps' => $steps,
            'currentStatus' => $currentStatusKey,
            'currentStepIndex' => $currentStepIndex,
            'rejected' => $approvalRequest && $approvalRequest->status === 'rejected',
            'returned' => $approvalRequest && $approvalRequest->status === 'returned',
            'rejection_level' => null,
            'rejection_reason' => $rejectionReason,
            'stepDates' => $stepDates,
            'stepUsers' => $stepUsers
        ];
    }

    protected function isCurrentStepApproved($approvalRequest)
    {
        if (!$approvalRequest || !$approvalRequest->approval_history) {
            return false;
        }

        $history = is_string($approvalRequest->approval_history) 
            ? json_decode($approvalRequest->approval_history, true) 
            : $approvalRequest->approval_history;

        if (!is_array($history)) {
            return false;
        }

        $currentStep = $approvalRequest->current_step;
        
        // Helper function to check if a step is approved
        $isStepApproved = function($stepKey) use ($history) {
            if (!isset($history[$stepKey])) {
                return false;
            }
            $stepData = $history[$stepKey];
            // Check if action is 'approved' OR if approved_at and approved_by exist (legacy data)
            return (isset($stepData['action']) && $stepData['action'] === 'approved') ||
                   (isset($stepData['approved_at']) && isset($stepData['approved_by']));
        };
        
        // For vehicle: after department_head_approval approves, current_step becomes director_approval
        // So if current_step is director_approval, check if department_head_approval was already approved
        if ($approvalRequest->module_type === 'vehicle' && $currentStep === 'director_approval') {
            if ($isStepApproved('department_head_approval')) {
                return true; // Department head already approved, can't reject at director step
            }
        }
        
        // For leave: similar logic
        if ($approvalRequest->module_type === 'leave') {
            if ($currentStep === 'review' && $isStepApproved('department_head_approval')) {
                return true;
            }
            if ($currentStep === 'director_approval' && $isStepApproved('review')) {
                return true;
            }
        }
        
        // Check if current step was already approved (shouldn't happen, but just in case)
        if ($isStepApproved($currentStep)) {
            return true;
        }

        return false;
    }
}



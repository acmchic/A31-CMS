<?php

namespace Modules\ApprovalWorkflow\Services;

use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\VehicleRegistration\Models\VehicleRegistration;
use Modules\ApprovalWorkflow\Models\ApprovalHistory;
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
            ]
        ];

        // Get leave counts
        $leaveQuery = EmployeeLeave::query();
        $this->filterLeaveByUserPermissions($leaveQuery, $user, ['status' => 'all']);

        $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
        $hasOfficerReviewPermission = PermissionHelper::can($user, 'leave.review.officer');

        // Count by status and rank type
        $pendingQuery = (clone $leaveQuery)->where('workflow_status', EmployeeLeave::WORKFLOW_PENDING);
        $reviewQuery = (clone $leaveQuery)->where('workflow_status', EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD);
        
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
        $directorQuery = (clone $leaveQuery)->where('workflow_status', EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER);
        if ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc'])) {
            $userId = (int)$user->id;
            $directorQuery->where(function($q) use ($userId) {
                $q->whereJsonContains('selected_approvers', $userId)
                  ->orWhereJsonContains('selected_approvers', (string)$userId)
                  ->orWhereRaw('JSON_CONTAINS(selected_approvers, ?)', [json_encode($userId)])
                  ->orWhereRaw('JSON_CONTAINS(selected_approvers, ?)', [json_encode((string)$userId)]);
            });
        }
        $counts['leave']['director'] = $directorQuery->count();

        // Get vehicle counts
        $vehicleQuery = VehicleRegistration::query();
        $this->filterVehicleByUserPermissions($vehicleQuery, $user, []);

        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        
        if ($isDirector) {
            $counts['vehicle']['pending'] = 0;
            $counts['vehicle']['review'] = 0;
            $counts['vehicle']['director'] = (clone $vehicleQuery)->where('workflow_status', 'director_review')->count();
        } else {
            $counts['vehicle']['pending'] = (clone $vehicleQuery)->whereIn('workflow_status', ['submitted', 'dept_review'])->count();
            $counts['vehicle']['review'] = 0;
            $counts['vehicle']['director'] = 0;
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
     * Get leave requests
     */
    protected function getLeaveRequests($user, $filters)
    {
        $query = EmployeeLeave::query();

        // Apply status filter FIRST - use simple where() to ensure it's not overridden
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
                // Apply status filter directly - this will be respected by subsequent filters
                $query->where('workflow_status', $statusValue);
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

        return $query->with(['employee.department'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(function($leave) use ($hasReviewPermission, $hasOfficerReviewPermission) {
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
                $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
                $needsPin = !($isReviewerStep && $hasReviewPermission);

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
                    'can_approve' => $leave->canBeApproved() && $this->canUserApprove($leave, $user),
                    'can_reject' => $leave->canBeRejected() && $this->canUserApprove($leave, $user),
                    'needs_pin' => $needsPin,
                    'is_reviewer_step' => $isReviewerStep && $hasReviewPermission,
                    'has_selected_approvers' => !empty($leave->selected_approvers),
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
        if ($filters['status'] !== 'all' && !$isDirector) {
            if ($filters['status'] === 'pending') {
                $query->whereIn('workflow_status', ['submitted', 'dept_review']);
            } elseif ($filters['status'] === 'director_review') {
                $query->where('workflow_status', 'director_review');
            } elseif ($filters['status'] === 'approved') {
                $query->where('workflow_status', 'approved');
            } else {
                $query->where('workflow_status', $filters['status']);
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
                    'status' => $vehicle->workflow_status,
                    'status_label' => $this->getVehicleStatusLabel($vehicle->workflow_status),
                    'status_badge' => $this->getVehicleStatusBadge($vehicle->workflow_status),
                    'initiated_by' => $vehicle->user ? $vehicle->user->name : 'N/A',
                    'initiated_by_username' => $vehicle->user ? $vehicle->user->username : 'N/A',
                    'created_at' => $vehicle->created_at,
                    'created_at_formatted' => $this->formatDateWithTimezone($vehicle->created_at, 'd/m/Y H:i'),
                    'period' => $this->getVehiclePeriod($vehicle),
                    'can_approve' => $vehicle->canBeApproved() && $this->canUserApproveVehicle($vehicle, backpack_user()),
                    'can_reject' => $vehicle->canBeRejected() && $this->canUserApproveVehicle($vehicle, backpack_user()),
                ];
            });
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

        // Director (BGD): only see requests where they are in selected_approvers list (for approved_by_reviewer step)
        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        if ($isDirector) {
            $statusFilter = $filters['status'] ?? 'all';

            // For director, add additional permission check BUT keep the status filter intact
            $query->where(function($q) use ($user, $statusFilter) {
                // Show requests at approved_by_reviewer step where user is in selected_approvers
                // (status filter already ensures workflow_status matches, so we only need to check selected_approvers)
                if ($statusFilter === 'all' || $statusFilter === 'approved_by_reviewer') {
                    $q->where(function($jsonQ) use ($user) {
                        // Check if user ID is in selected_approvers JSON array
                        $userId = (int)$user->id;
                        $jsonQ->whereJsonContains('selected_approvers', $userId)
                              ->orWhereJsonContains('selected_approvers', (string)$userId)
                              ->orWhereRaw('JSON_CONTAINS(selected_approvers, ?)', [json_encode($userId)])
                              ->orWhereRaw('JSON_CONTAINS(selected_approvers, ?)', [json_encode((string)$userId)]);
                    });
                }

                // Also show requests already approved by this user (for history)
                if ($statusFilter === 'all' || $statusFilter === 'completed' || $statusFilter === 'approved_by_director') {
                    $q->orWhere('approved_by_director', $user->id);
                }
            });
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

        $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        
        if ($isDirector) {
            $statusFilter = $filters['status'] ?? 'all';
            $userId = (int)$user->id;
            
            $query->where(function($q) use ($user, $statusFilter, $userId) {
                $jsonCondition = function($jsonQ) use ($userId) {
                    $jsonQ->whereJsonContains('selected_approvers', $userId)
                          ->orWhereJsonContains('selected_approvers', (string)$userId)
                          ->orWhereRaw('JSON_CONTAINS(selected_approvers, ?)', [json_encode($userId)])
                          ->orWhereRaw('JSON_CONTAINS(selected_approvers, ?)', [json_encode((string)$userId)]);
                };
                
                if ($statusFilter === 'all' || $statusFilter === 'director_review') {
                    $q->where(function($directorQ) use ($userId, $jsonCondition) {
                        $directorQ->where('workflow_status', 'director_review')
                                  ->where($jsonCondition);
                    });
                }
                
                if ($statusFilter === 'all' || $statusFilter === 'approved') {
                    $q->orWhere(function($approvedQ) use ($userId, $jsonCondition) {
                        $approvedQ->where('workflow_status', 'approved')
                                  ->where(function($approvedCondition) use ($userId, $jsonCondition) {
                                      $approvedCondition->where('director_approved_by', $userId)
                                                        ->orWhere($jsonCondition);
                                  });
                    });
                }
            });
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
                $hasReviewPermission = PermissionHelper::can($user, 'leave.review');
                $needsPin = !($isReviewerStep && $hasReviewPermission);
                $hasSelectedApprovers = !empty($model->selected_approvers);

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
                    'can_approve' => $model->canBeApproved() && $this->canUserApprove($model, $user),
                    'can_reject' => $model->canBeRejected() && $this->canUserApprove($model, $user),
                    'needs_pin' => $needsPin,
                    'is_reviewer_step' => $isReviewerStep && $hasReviewPermission,
                    'is_reviewer_role' => $hasReviewPermission || $user->hasRole('Thẩm định'),
                    'has_selected_approvers' => $hasSelectedApprovers,
                    'selected_approvers' => $model->selected_approvers ? (is_array($model->selected_approvers) ? $model->selected_approvers : json_decode($model->selected_approvers, true)) : [],
                    'workflow_data' => $this->getWorkflowProgressData($model),
                    'has_signed_pdf' => $model->hasSignedPdf(),
                    'pdf_url' => $model->hasSignedPdf() ? route('approval.preview-pdf', ['modelClass' => base64_encode(get_class($model)), 'id' => $model->id]) : null,
                ];

            case 'vehicle':
                $model = VehicleRegistration::with(['user', 'vehicle', 'driver'])->find($id);
                if (!$model) return null;

                $user = backpack_user();
                $isDepartmentHeadStep = $model->workflow_status === 'dept_review' && $model->vehicle_id && !$model->department_approved_at;
                $hasDepartmentHeadPermission = $this->canUserApproveVehicle($model, $user);
                $hasSelectedApprovers = !empty($model->selected_approvers);

                return [
                    'id' => $model->id,
                    'model_type' => 'vehicle',
                    'type' => 'Official Cars',
                    'type_label' => 'Đăng ký xe',
                    'title' => $this->getVehicleTitle($model),
                    'status' => $model->workflow_status,
                    'status_label' => $this->getVehicleStatusLabel($model->workflow_status),
                    'status_badge' => $this->getVehicleStatusBadge($model->workflow_status),
                    'submitted_by' => $model->user ? $model->user->name : 'N/A',
                    'submitted_at' => $this->formatVietnameseDate($model->created_at),
                    'details' => $this->getVehicleDetails($model),
                    'can_approve' => $model->canBeApproved() && $this->canUserApproveVehicle($model, $user),
                    'can_reject' => $model->canBeRejected() && $this->canUserApproveVehicle($model, $user),
                    'is_department_head_step' => $isDepartmentHeadStep && $hasDepartmentHeadPermission,
                    'has_selected_approvers' => $hasSelectedApprovers,
                    'selected_approvers' => $model->selected_approvers ? (is_array($model->selected_approvers) ? $model->selected_approvers : json_decode($model->selected_approvers, true)) : [],
                    'workflow_data' => $this->getVehicleWorkflowProgressData($model),
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

        return ApprovalHistory::where('approvable_type', get_class($model))
            ->where('approvable_id', $id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($history) {
                return [
                    'step_name' => $this->getStepName($history->level, $history->action),
                    'approver' => $history->user ? $history->user->name : 'N/A',
                    'result' => $history->action_display,
                    'result_badge' => $this->getActionBadge($history->action),
                    'comment' => $history->comment,
                    'time' => $this->formatDateWithTimezone($history->created_at, 'd M, H:i'),
                    'time_relative' => $history->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Approve request
     */
    public function approveRequest($id, $modelType, $user, $comment = '', $certificatePin = null)
    {
        $model = $this->getModel($id, $modelType);
        if (!$model) {
            throw new \Exception('Request not found');
        }

        // Use ApprovalController logic
        $controller = app(\Modules\ApprovalWorkflow\Http\Controllers\ApprovalController::class);

        $needsPin = true;
        if ($modelType === 'leave' && $model instanceof EmployeeLeave) {
            $isReviewerStep = $model->workflow_status === EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD;
            $hasReviewPermission = PermissionHelper::can($user, 'leave.review');

            if ($isReviewerStep && $hasReviewPermission) {
                if (empty($model->selected_approvers)) {
                    throw new \Exception('Vui lòng chọn người phê duyệt trước khi chuyển đơn lên Ban Giám đốc');
                }
                $needsPin = false;
            }
        } elseif ($modelType === 'vehicle' && $model instanceof VehicleRegistration) {
            $isDepartmentHeadStep = $model->workflow_status === 'dept_review' && $model->vehicle_id && !$model->department_approved_at;
            $hasDepartmentHeadPermission = PermissionHelper::can($user, 'vehicle_registration.approve');

            if ($isDepartmentHeadStep && $hasDepartmentHeadPermission) {
                if (empty($model->selected_approvers)) {
                    throw new \Exception('Vui lòng chọn người phê duyệt trước khi chuyển đơn lên Ban Giám đốc');
                }
            }
        }

        // If reviewer step, always use approveWithoutPin (no PIN needed)
        if (!$needsPin) {
            // Approve without PIN (for reviewer step - just forward to BGD)
            $request = new \Illuminate\Http\Request();
            $request->merge(['comment' => $comment]);

            $modelClass = base64_encode(get_class($model));
            $response = $controller->approveWithoutPin($request, $modelClass, $id);

            // Convert response to array format
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $data = $response->getData(true);
                return [
                    'success' => $data['success'] ?? true,
                    'message' => $data['message'] ?? 'Đã chuyển lên BGD',
                    'data' => $data['data'] ?? null,
                ];
            }

            return [
                'success' => true,
                'message' => 'Đã chuyển lên BGD',
                'data' => null,
            ];
        }

        // For other steps, require PIN
        if (!$certificatePin) {
            throw new \Exception('Vui lòng nhập mã PIN để phê duyệt');
        }

        // Approve with PIN
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'certificate_pin' => $certificatePin,
            'comment' => $comment,
        ]);

        $modelClass = base64_encode(get_class($model));
        $response = $controller->approveWithPin($request, $modelClass, $id);

        // Convert response to array format
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            return [
                'success' => $data['success'] ?? true,
                'message' => $data['message'] ?? 'Phê duyệt thành công',
                'data' => $data['data'] ?? null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Phê duyệt thành công',
            'data' => null,
        ];
    }

    /**
     * Reject request
     */
    public function rejectRequest($id, $modelType, $user, $reason = '')
    {
        $model = $this->getModel($id, $modelType);
        if (!$model) {
            throw new \Exception('Request not found');
        }

        $controller = app(\Modules\ApprovalWorkflow\Http\Controllers\ApprovalController::class);
        $request = new \Illuminate\Http\Request();
        $request->merge(['reason' => $reason]);

        $modelClass = base64_encode(get_class($model));
        $response = $controller->reject($request, $modelClass, $id);

        // Convert response to array format
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            return [
                'success' => $data['success'] ?? true,
                'message' => $data['message'] ?? 'Từ chối thành công',
                'data' => $data['data'] ?? null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Từ chối thành công',
            'data' => null,
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
     * Returns Tabler badge classes: bg-blue, bg-azure, bg-indigo, bg-green, bg-red
     */
    protected function getStatusBadge($status)
    {
        // Map to Tabler badge colors
        $badges = [
            EmployeeLeave::WORKFLOW_PENDING => 'blue',  // Chờ chỉ huy xác nhận
            EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD => 'azure',  // Chờ thẩm định
            EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER => 'indigo',  // Chờ BGD ký
            EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR => 'green',  // Đã phê duyệt
            EmployeeLeave::WORKFLOW_REJECTED => 'red',  // Đã từ chối
        ];

        return $badges[$status] ?? 'secondary';
    }

    /**
     * Get Tabler badge class name (full class string)
     */
    public function getTablerBadgeClass($status, $modelType = 'leave')
    {
        if ($modelType === 'leave') {
            $color = $this->getStatusBadge($status);
        } else {
            $color = $this->getVehicleStatusBadge($status);
        }

        if ($color === 'secondary') {
            return 'badge bg-secondary text-secondary-fg badge-pill';
        }

        return "badge bg-{$color} text-{$color}-fg badge-pill";
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
        // Map to Tabler badge colors
        $badges = [
            'submitted' => 'cyan',
            'dept_review' => 'azure',
            'director_review' => 'indigo',
            'approved' => 'green',
            'rejected' => 'red',
        ];

        return $badges[$status] ?? 'secondary';
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

        $status = $vehicle->workflow_status;
        
        if ($status === 'director_review' || ($status === 'approved' && !$vehicle->director_approved_by)) {
            $isDirector = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
            if (!$isDirector) {
                return false;
            }
            
            if (!$vehicle->selected_approvers) {
                return false;
            }
            
            if (method_exists($vehicle, 'isUserSelectedApprover')) {
                return $vehicle->isUserSelectedApprover($user->id);
            }
            
            $approverIds = is_array($vehicle->selected_approvers)
                ? $vehicle->selected_approvers
                : json_decode($vehicle->selected_approvers, true);
            
            if (!is_array($approverIds)) {
                return false;
            }
            
            return in_array((int)$user->id, array_map('intval', $approverIds));
        }
        
        if ($status === 'dept_review') {
            $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);
            return $isDepartmentHead;
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

        if ($model->approved_at_department_head && $model->workflow_status !== EmployeeLeave::WORKFLOW_PENDING) {
            $stepDates[EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD] = $this->formatDateWithTimezone($model->approved_at_department_head, 'd/m/Y H:i');
        }
        if ($model->approved_by_department_head && $model->workflow_status !== EmployeeLeave::WORKFLOW_PENDING) {
            $deptHead = \App\Models\User::find($model->approved_by_department_head);
            if ($deptHead) {
                $stepUsers[EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD] = $deptHead->name ?? 'N/A';
            }
        }

        if ($model->approved_at_reviewer && in_array($model->workflow_status, [
            EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER,
            EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR
        ])) {
            $stepDates[EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER] = $this->formatDateWithTimezone($model->approved_at_reviewer, 'd/m/Y H:i');
        }
        if ($model->approved_by_reviewer && in_array($model->workflow_status, [
            EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER,
            EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR
        ])) {
            $reviewer = \App\Models\User::find($model->approved_by_reviewer);
            if ($reviewer) {
                $stepUsers[EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER] = $reviewer->name ?? 'N/A';
            }
        }

        if ($model->approved_at_director && $model->workflow_status === EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR) {
            $stepDates[EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR] = $this->formatDateWithTimezone($model->approved_at_director, 'd/m/Y H:i');
        }
        if ($model->approved_by_director && $model->workflow_status === EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR) {
            $director = \App\Models\User::find($model->approved_by_director);
            if ($director) {
                $stepUsers[EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR] = $director->name ?? 'N/A';
            }
        }

        $currentStatusKey = $model->workflow_status;
        $currentStepIndex = 0;

        switch ($currentStatusKey) {
            case EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR:
                $currentStepIndex = 4;
                if ($model->approved_at_director) {
                    $stepDates['completed'] = $this->formatDateWithTimezone($model->approved_at_director, 'd/m/Y H:i');
                    if ($model->approved_by_director) {
                        $director = \App\Models\User::find($model->approved_by_director);
                        if ($director) {
                            $stepUsers['completed'] = $director->name ?? 'N/A';
                        }
                    }
                }
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
            case EmployeeLeave::WORKFLOW_REJECTED:
                if ($model->approved_at_reviewer) {
                    $currentStepIndex = 3;
                } elseif ($model->approved_at_department_head) {
                    $currentStepIndex = 2;
                } else {
                    $currentStepIndex = 1;
                }
                break;
            default:
                if ($model->approved_at_director) {
                    $currentStepIndex = 4;
                } elseif ($model->approved_at_reviewer) {
                    $currentStepIndex = 3;
                } elseif ($model->approved_at_department_head) {
                    $currentStepIndex = 2;
                } else {
                    $currentStepIndex = 1;
                }
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

        $currentStatusKey = $model->workflow_status;
        $currentStepIndex = 0;

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

        return [
            'steps' => $steps,
            'currentStatus' => $currentStatusKey,
            'currentStepIndex' => $currentStepIndex,
            'rejected' => $model->workflow_status === 'rejected',
            'stepDates' => $stepDates,
            'stepUsers' => $stepUsers
        ];
    }
}


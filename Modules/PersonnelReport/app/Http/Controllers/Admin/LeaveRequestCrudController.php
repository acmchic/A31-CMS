<?php

namespace Modules\PersonnelReport\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\PersonnelReport\Models\EmployeeLeave;
use Modules\OrganizationStructure\Models\Employee;
use Modules\OrganizationStructure\Models\Department;
use Modules\OrganizationStructure\Models\Leave;
use App\Helpers\PermissionHelper;
use App\Helpers\DateHelper;

class LeaveRequestCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(EmployeeLeave::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/leave-request');
        CRUD::setEntityNameStrings('đơn xin nghỉ phép', 'đơn xin nghỉ phép');

        CRUD::orderBy('id', 'DESC');
        CRUD::set('list.persistentTable', false);

        // Don't apply filtering in setup() - apply only in setupListOperation()
        // This allows show/edit operations to work even if record is filtered out
    }

    /**
     * Apply filtering based on workflow step and user role
     */
    private function applyDepartmentFilter()
    {
        $user = backpack_user();

        // Apply month/year filter first
        $this->applyMonthYearFilter();

        // Apply department filter from URL parameter
        $this->applyDepartmentFilterFromUrl();

        // Check if filtering by specific workflow_status from URL
        $statusFilter = request()->get('workflow_status');

        // Admin sees everything (with or without status filter)
        if ($user->hasRole('Admin')) {
            if ($statusFilter && $statusFilter !== 'all') {
                CRUD::addClause('where', 'workflow_status', $statusFilter);
            }
            return;
        }

        // User with leave.view.all permission sees everything (like admin, with or without status filter)
        if (PermissionHelper::can($user, 'leave.view.all')) {
            if ($statusFilter && $statusFilter !== 'all') {
                CRUD::addClause('where', 'workflow_status', $statusFilter);
            }
            return;
        }

        // User with leave.review permission sees everything (like admin, with or without status filter)
        if (PermissionHelper::can($user, 'leave.review')) {
            if ($statusFilter && $statusFilter !== 'all') {
                CRUD::addClause('where', 'workflow_status', $statusFilter);
            }
            return;
        }

        // Apply workflow step filtering (this will handle statusFilter for regular users)
        $this->applyWorkflowStepFilter($user);
    }

    /**
     * Apply department filter from URL parameter
     */
    private function applyDepartmentFilterFromUrl()
    {
        $departmentFilter = request()->get('department_id');

        if ($departmentFilter && $departmentFilter !== 'all') {
            CRUD::addClause('whereHas', 'employee', function($q) use ($departmentFilter) {
                $q->where('department_id', $departmentFilter);
            });
        }
    }

    /**
     * Apply month/year filter
     */
    private function applyMonthYearFilter()
    {
        $monthYear = request()->get('month_year');

        // If no filter, use current month/year as default
        if (!$monthYear || $monthYear === 'all') {
            if (!$monthYear) {
                // Default to current month/year
                $currentMonth = now()->format('Y-m');
                CRUD::addClause('where', function($q) use ($currentMonth) {
                    $startDate = \Carbon\Carbon::parse($currentMonth . '-01')->startOfMonth();
                    $endDate = $startDate->copy()->endOfMonth();

                    // Filter where from_date or to_date falls within the month
                    $q->where(function($subQ) use ($startDate, $endDate) {
                        $subQ->whereBetween('from_date', [$startDate, $endDate])
                             ->orWhereBetween('to_date', [$startDate, $endDate])
                             ->orWhere(function($q2) use ($startDate, $endDate) {
                                 $q2->where('from_date', '<=', $startDate)
                                    ->where('to_date', '>=', $endDate);
                             });
                    });
                });
            }
            return;
        }

        // Parse month/year (format: YYYY-MM)
        if (preg_match('/^(\d{4})-(\d{2})$/', $monthYear, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            CRUD::addClause('where', function($q) use ($startDate, $endDate) {
                // Filter where from_date or to_date falls within the month
                $q->where(function($subQ) use ($startDate, $endDate) {
                    $subQ->whereBetween('from_date', [$startDate, $endDate])
                         ->orWhereBetween('to_date', [$startDate, $endDate])
                         ->orWhere(function($q2) use ($startDate, $endDate) {
                             $q2->where('from_date', '<=', $startDate)
                                ->where('to_date', '>=', $endDate);
                         });
                });
            });
        }
    }

    /**
     * Helper: Apply leave workflow status filter to approval_requests query
     */
    private function applyWorkflowStatusFilterToApprovalRequest($query, $status)
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
     * Filter by workflow step - only show requests at current user's approval step
     */
    private function applyWorkflowStepFilter($user)
    {
        $statusFilter = request()->get('workflow_status');
        $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);

        // Reviewer: user with leave.review permission (not just role BGD)
        $isReviewer = PermissionHelper::can($user, 'leave.review');

        // Director: BGD role but NOT reviewer (or Giám đốc role)
        $hasBgdRole = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc']);
        $hasGiámDocRole = $user->hasRole(['Giám đốc']);
        $isDirector = ($hasBgdRole && !$isReviewer) || $hasGiámDocRole;

        $userDepartmentId = $user->department_id;

        if ($user->employee_id) {
            $emp = Employee::find($user->employee_id);
            if ($emp && $emp->department_id) {
                $userDepartmentId = $emp->department_id;
            }
        }

        // Build query conditions with OR
        CRUD::addClause('where', function($q) use ($user, $isDepartmentHead, $isReviewer, $isDirector, $userDepartmentId, $statusFilter) {
            $hasCondition = false;

            // Step 1: User who created can see their own requests at any status (including rejected)
            if ($user->employee_id) {
                $q->where(function($subQ) use ($user, $statusFilter) {
                    $subQ->where('employee_id', $user->employee_id);
                    if ($statusFilter && $statusFilter !== 'all') {
                        $subQ->whereHas('approvalRequest', function($arQ) use ($statusFilter) {
                            $this->applyWorkflowStatusFilterToApprovalRequest($arQ, $statusFilter);
                        });
                    }
                });
                $hasCondition = true;
            }

            // Step 2: Department head can see all status requests in their department (including rejected)
            if ($isDepartmentHead && $userDepartmentId) {
                $employeeIds = Employee::where('department_id', $userDepartmentId)->pluck('id');
                if ($employeeIds->isNotEmpty()) {
                    if ($hasCondition) {
                        $q->orWhere(function($subQ) use ($employeeIds, $statusFilter) {
                            $subQ->whereIn('employee_id', $employeeIds);
                            if ($statusFilter && $statusFilter !== 'all') {
                                $subQ->whereHas('approvalRequest', function($arQ) use ($statusFilter) {
                                    $this->applyWorkflowStatusFilterToApprovalRequest($arQ, $statusFilter);
                                });
                            }
                        });
                    } else {
                        $q->where(function($subQ) use ($employeeIds, $statusFilter) {
                            $subQ->whereIn('employee_id', $employeeIds);
                            if ($statusFilter && $statusFilter !== 'all') {
                                $subQ->whereHas('approvalRequest', function($arQ) use ($statusFilter) {
                                    $this->applyWorkflowStatusFilterToApprovalRequest($arQ, $statusFilter);
                                });
                            }
                        });
                        $hasCondition = true;
                    }
                }
            }

            // Step 3: Reviewer can see requests approved by department head (waiting for their approval)
            if ($isReviewer) {
                if ($statusFilter && $statusFilter !== 'all') {
                    // If filtering by specific status, only show if it's reviewer's status
                    if ($statusFilter === EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD) {
                        if ($hasCondition) {
                            $q->orWhereHas('approvalRequest', function($arQ) {
                                $arQ->where('status', 'in_review')
                                    ->where('current_step', 'review');
                            });
                        } else {
                            $q->whereHas('approvalRequest', function($arQ) {
                                $arQ->where('status', 'in_review')
                                    ->where('current_step', 'review');
                            });
                            $hasCondition = true;
                        }
                    }
                } else {
                    if ($hasCondition) {
                        $q->orWhereHas('approvalRequest', function($arQ) {
                            $arQ->where('status', 'in_review')
                                ->where('current_step', 'review');
                        });
                    } else {
                        $q->whereHas('approvalRequest', function($arQ) {
                            $arQ->where('status', 'in_review')
                                ->where('current_step', 'review');
                        });
                        $hasCondition = true;
                    }
                }
            }

            // Step 4: Director can ONLY see requests where they are in selected_approvers
            // AND requests already approved by them (for tracking)
            if ($isDirector) {
                $userId = (int)$user->id;
                
                if ($hasCondition) {
                    $q->orWhere(function($subQ) use ($user, $statusFilter, $userId) {
                        // Show requests at director_approval step where user is in selected_approvers
                        $subQ->whereHas('approvalRequest', function($arQ) use ($userId) {
                            $arQ->where('status', 'in_review')
                                ->where('current_step', 'director_approval')
                                ->where(function($jsonQ) use ($userId) {
                                    $jsonQ->whereJsonContains('selected_approvers->director_approval->users', ['id' => $userId])
                                          ->orWhereJsonContains('selected_approvers->director_approval->users', $userId);
                                });
                        })
                        // Also show requests already approved by this user (for history)
                        ->orWhereHas('approvalRequest', function($arQ) use ($userId) {
                            $arQ->where('status', 'approved')
                                ->whereJsonContains('approval_history->director_approval->approved_by', $userId);
                        });

                        // Apply status filter if specified
                        if ($statusFilter && $statusFilter !== 'all') {
                            $subQ->where(function($statusQ) use ($statusFilter, $userId) {
                                if ($statusFilter === EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER) {
                                    // For approved_by_reviewer, must be in selected_approvers
                                    $statusQ->whereHas('approvalRequest', function($arQ) use ($userId) {
                                        $arQ->where('status', 'in_review')
                                            ->where('current_step', 'director_approval')
                                            ->where(function($jsonQ) use ($userId) {
                                                $jsonQ->whereJsonContains('selected_approvers->director_approval->users', ['id' => $userId])
                                                      ->orWhereJsonContains('selected_approvers->director_approval->users', $userId);
                                            });
                                    });
                                } else {
                                    // For other statuses (like approved_by_director), must be approved by this user
                                    $statusQ->whereHas('approvalRequest', function($arQ) use ($statusFilter, $userId) {
                                        $this->applyWorkflowStatusFilterToApprovalRequest($arQ, $statusFilter);
                                        $arQ->whereJsonContains('approval_history->director_approval->approved_by', $userId);
                                    });
                                }
                            });
                        }
                    });
                } else {
                    $q->where(function($subQ) use ($user, $statusFilter, $userId) {
                        // Show requests at director_approval step where user is in selected_approvers
                        $subQ->whereHas('approvalRequest', function($arQ) use ($userId) {
                            $arQ->where('status', 'in_review')
                                ->where('current_step', 'director_approval')
                                ->where(function($jsonQ) use ($userId) {
                                    $jsonQ->whereJsonContains('selected_approvers->director_approval->users', ['id' => $userId])
                                          ->orWhereJsonContains('selected_approvers->director_approval->users', $userId);
                                });
                        })
                        // Also show requests already approved by this user (for history)
                        ->orWhereHas('approvalRequest', function($arQ) use ($userId) {
                            $arQ->where('status', 'approved')
                                ->whereJsonContains('approval_history->director_approval->approved_by', $userId);
                        });

                        // Apply status filter if specified
                        if ($statusFilter && $statusFilter !== 'all') {
                            $subQ->where(function($statusQ) use ($statusFilter, $userId) {
                                if ($statusFilter === EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER) {
                                    // For approved_by_reviewer, must be in selected_approvers
                                    $statusQ->whereHas('approvalRequest', function($arQ) use ($userId) {
                                        $arQ->where('status', 'in_review')
                                            ->where('current_step', 'director_approval')
                                            ->where(function($jsonQ) use ($userId) {
                                                $jsonQ->whereJsonContains('selected_approvers->director_approval->users', ['id' => $userId])
                                                      ->orWhereJsonContains('selected_approvers->director_approval->users', $userId);
                                            });
                                    });
                                } else {
                                    // For other statuses (like approved_by_director), must be approved by this user
                                    $statusQ->whereHas('approvalRequest', function($arQ) use ($statusFilter, $userId) {
                                        $this->applyWorkflowStatusFilterToApprovalRequest($arQ, $statusFilter);
                                        $arQ->whereJsonContains('approval_history->director_approval->approved_by', $userId);
                                    });
                                }
                            });
                        }
                    });
                    $hasCondition = true;
                }
            }

            // If no conditions, hide all
            if (!$hasCondition) {
                $q->where('id', 0);
            }
        });
    }

    /**
     * Setup buttons based on user permissions - clean approach
     * ✅ Using ApprovalWorkflow module buttons
     */
    private function setupButtonsForRole()
    {
        $user = backpack_user();

        if (!PermissionHelper::can($user, 'leave.create')) {
            CRUD::removeButton('create');
        }

        CRUD::removeButton('update');
        CRUD::removeButton('delete');
        CRUD::removeButton('show');

        CRUD::addButtonFromModelFunction('line', 'delete_conditional', 'deleteButtonConditional', 'beginning');
        CRUD::addButtonFromModelFunction('line', 'edit_conditional', 'editButtonConditional', 'beginning');
        CRUD::addButtonFromModelFunction('line', 'show', 'showButton', 'beginning');

        if (PermissionHelper::can($user, 'leave.view')) {
            CRUD::addButtonFromModelFunction('line', 'download_pdf', 'downloadPdfButton', 'beginning');
        }

        $hasApprove = PermissionHelper::can($user, 'leave.approve');
        $hasReview = PermissionHelper::can($user, 'leave.review');

        if ($hasApprove || $hasReview) {
            CRUD::addButtonFromModelFunction('line', 'reject', 'rejectButton', 'beginning');
            CRUD::addButtonFromModelFunction('line', 'approve', 'approveButton', 'beginning');
        }
    }

    protected function setupListOperation()
    {
        // Apply filtering only for list operation
        $this->applyDepartmentFilter();

        // Ensure searchable table is enabled (already enabled by default in config)
        CRUD::set('list.searchableTable', true);

        // Add status filter cards widget
        $this->addStatusFilterCards();

        // Add month/year filter widget
        $this->addMonthYearFilter();

        // Add department filter widget
        $this->addDepartmentFilter();

        \Widget::add()->type('view')->view('personnelreport::widgets.disable-search-autocomplete');

        $this->setupButtonsForRole();

        CRUD::column('employee_name')
            ->label('Nhân sự')
            ->type('closure')
            ->function(function($entry) {
                return $entry->employee ? $entry->employee->name : 'N/A';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas('employee', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            });

        CRUD::column('department_name')
            ->label('Phòng ban')
            ->type('closure')
            ->function(function($entry) {
                return $entry->employee && $entry->employee->department
                    ? $entry->employee->department->name
                    : 'N/A';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas('employee.department', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            });

        CRUD::column('leave_type')
            ->label('Loại nghỉ')
            ->type('closure')
            ->function(function($entry) {
                return $entry->leave_type_text;
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                // Search by leave_type value
                $query->orWhere('leave_type', 'like', '%'.$searchTerm.'%');
            });

        CRUD::column('from_date')
            ->label('Từ ngày')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->from_date);
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                // Try to parse date and search
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $searchTerm);
                    $query->orWhereDate('from_date', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    // If not a date format, search as string in date field
                    $query->orWhere('from_date', 'like', '%'.$searchTerm.'%');
                }
            });

        CRUD::column('to_date')
            ->label('Đến ngày')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->to_date);
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                // Try to parse date and search
                try {
                    $date = \Carbon\Carbon::createFromFormat('d/m/Y', $searchTerm);
                    $query->orWhereDate('to_date', $date->format('Y-m-d'));
                } catch (\Exception $e) {
                    // If not a date format, search as string in date field
                    $query->orWhere('to_date', 'like', '%'.$searchTerm.'%');
                }
            });

        CRUD::column('location')
            ->label('Địa điểm')
            ->type('text')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('location', 'like', '%'.$searchTerm.'%');
            });

        CRUD::column('workflow_status')
            ->label('Trạng thái')
            ->type('closure')
            ->escaped(false)
            ->function(function($entry) {
                $status = $entry->workflow_status;
                $text = $entry->workflow_status_text;

                // Define icon, badge class and color for each status
                $statusConfig = [
                    EmployeeLeave::WORKFLOW_PENDING => [
                        'icon' => 'la-clock',
                        'badge' => 'warning', // Yellow badge
                        'color' => '#ffc107',
                    ],
                    EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD => [
                        'icon' => 'la-check-circle',
                        'badge' => 'info', // Info blue badge
                        'color' => '#17a2b8',
                    ],
                    EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER => [
                        'icon' => 'la-check-circle',
                        'badge' => 'primary', // Primary blue badge
                        'color' => '#007bff',
                    ],
                    EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR => [
                        'icon' => 'la-check-double',
                        'badge' => 'success', // Success green badge
                        'color' => '#28a745',
                    ],
                    EmployeeLeave::WORKFLOW_REJECTED => [
                        'icon' => 'la-times-circle',
                        'badge' => 'danger', // Danger red badge
                        'color' => '#dc3545',
                    ],
                ];

                // Get config for current status, fallback to default
                $config = $statusConfig[$status] ?? [
                    'icon' => 'la-circle',
                    'badge' => 'secondary',
                    'color' => '#6c757d',
                ];

                return '<span data-workflow-status="' . htmlspecialchars($status) . '">' .
                       '<span class="badge badge-' . $config['badge'] . ' bg-' . $config['badge'] . '" style="font-size: 0.875rem; padding: 0.35em 0.65em;">' .
                       '<i class="la ' . $config['icon'] . '" style="margin-right: 4px;"></i>' .
                       $text .
                       '</span>' .
                       '</span>';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                // Search by workflow_status value from approval_requests
                $query->orWhereHas('approvalRequest', function($q) use ($searchTerm) {
                    $q->where('status', 'like', '%'.$searchTerm.'%')
                      ->orWhere('current_step', 'like', '%'.$searchTerm.'%');
                });
            });

        CRUD::column('note')
            ->label('Ghi chú')
            ->type('text')
            ->limit(50)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('note', 'like', '%'.$searchTerm.'%');
            });
    }

    protected function setupCreateOperation()
    {
        $user = backpack_user();
        $isAdmin = $user->hasRole(['Admin', 'admin']);
        $isBanGiamDoc = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        $isTruongPhong = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);
        $isNhanVien = !($isAdmin || $isBanGiamDoc || $isTruongPhong);

        $validationRules = [
            'leave_type' => 'required|string|in:business,study,leave,hospital,pending,sick,maternity,checkup,other',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'location' => 'required|string|max:255',
            'note' => 'nullable|string|max:500'
        ];

        if ($isNhanVien && $user->employee_id) {
            $validationRules['employee_id'] = 'nullable|exists:employees,id';
        } else {
            $validationRules['employee_id'] = 'required|exists:employees,id';
        }

        CRUD::setValidation($validationRules, [
            'employee_id.required' => 'Vui lòng chọn Nhân sự',
            'employee_id.exists' => 'Nhân sự không tồn tại',
            'leave_type.required' => 'Vui lòng chọn loại nghỉ',
            'leave_type.in' => 'Loại nghỉ không hợp lệ',
            'from_date.required' => 'Vui lòng chọn ngày bắt đầu',
            'from_date.date' => 'Ngày bắt đầu không hợp lệ',
            'from_date.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi',
            'to_date.required' => 'Vui lòng chọn ngày kết thúc',
            'to_date.date' => 'Ngày kết thúc không hợp lệ',
            'to_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
            'location.required' => 'Vui lòng nhập địa điểm',
            'location.max' => 'Địa điểm không được quá 255 ký tự',
            'note.max' => 'Ghi chú không được quá 500 ký tự'
        ]);

        // Filter employees based on user's role and department
        $employeeOptions = [];
        $user = backpack_user();

        // Check user roles
        $isAdmin = $user->hasRole(['Admin', 'admin']);
        $isBanGiamDoc = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        $isTruongPhong = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);

        if ($isAdmin || $isBanGiamDoc) {
            // Admin or Ban Giám đốc: show all employees
            $employeeOptions = Employee::with('department')->get()->mapWithKeys(function($emp) {
                return [$emp->id => $emp->name . ' (' . ($emp->department ? $emp->department->name : 'N/A') . ')'];
            });
        } elseif ($isTruongPhong) {
            // Trưởng phòng: show employees in same department
            // First try to get department from user's direct department_id
            $departmentId = $user->department_id;

            // Fallback to employee's department if user doesn't have direct department
            if (!$departmentId && $user->employee_id) {
                $emp = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                $departmentId = $emp ? $emp->department_id : null;
            }

            if ($departmentId) {
                $employeeOptions = Employee::where('department_id', $departmentId)
                    ->get()
                    ->mapWithKeys(function($emp) {
                        return [$emp->id => $emp->name];
                    });
            }
        } else {
            // Nhân viên cá nhân: chỉ hiển thị chính user đó
            if ($user->employee_id) {
                $employee = Employee::find($user->employee_id);
                if ($employee) {
                    $employeeOptions = [$employee->id => $employee->name];
                }
            }
        }

        // Define leave types using constants
        $leaveOptions = [
            'business'   => 'Công tác - Cơ động',
            'study'      => 'Học',
            'leave'      => 'Phép - Tranh thủ',
            'hospital'   => 'Đi viện',
            'pending'    => 'Chờ hưu',
            'sick'       => 'Ốm tại trại',
            'maternity'  => 'Thai sản',
            'checkup'    => 'Khám bệnh',
            'other'      => 'Khác'
        ];

        // Convert to array if it's a Collection, then add empty option for placeholder "Chọn"
        $employeeOptionsArray = is_array($employeeOptions) ? $employeeOptions : $employeeOptions->toArray();

        // ✅ Sửa: Nếu chỉ có 1 option, tự động selected. Nếu > 1 option, phải chọn
        $defaultValue = null;
        if (count($employeeOptionsArray) === 1) {
            // Chỉ có 1 option → tự động selected
            $defaultValue = array_key_first($employeeOptionsArray);
        }

        $employeeOptionsWithPlaceholder = ['' => 'Chọn'] + $employeeOptionsArray;

        $employeeField = CRUD::field('employee_id')
            ->label('Nhân sự')
            ->type('select_from_array')
            ->options($employeeOptionsWithPlaceholder)
            ->allows_null(true)
            ->default($defaultValue) // ✅ Tự động selected nếu chỉ có 1 option
            ->tab('Thông tin cơ bản');

        CRUD::field('leave_type')
            ->label('Loại nghỉ')
            ->type('select_from_array')
            ->options($leaveOptions)
            ->tab('Thông tin cơ bản');

        CRUD::field('from_date')
            ->label('Từ ngày')
            ->type('date')
            ->wrapper(['class' => 'form-group col-md-6'])
            ->tab('Thông tin cơ bản');

        CRUD::field('to_date')
            ->label('Đến ngày')
            ->type('date')
            ->wrapper(['class' => 'form-group col-md-6'])
            ->tab('Thông tin cơ bản');

        CRUD::field('location')
            ->label('Địa điểm')
            ->type('text')
            ->tab('Thông tin cơ bản');

        CRUD::field('note')
            ->label('Ghi chú')
            ->type('textarea')
            ->tab('Thông tin cơ bản');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        $this->addWorkflowProgressWidget();

        CRUD::field('rejection_reason')
            ->label('Lý do từ chối')
            ->type('textarea')
            ->attributes(['readonly' => true, 'disabled' => false])
            ->visible(function($entry) {
                return $entry && $entry->workflow_status === EmployeeLeave::WORKFLOW_REJECTED && !empty($entry->rejection_reason);
            })
            ->tab('Thông tin cơ bản');
    }


    public function store()
    {
        $user = backpack_user();
        $request = $this->crud->getRequest();

        $isAdmin = $user->hasRole(['Admin', 'admin']);
        $isBanGiamDoc = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        $isTruongPhong = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);

        if (!($isAdmin || $isBanGiamDoc || $isTruongPhong) && $user->employee_id) {
            $request->merge(['employee_id' => $user->employee_id]);
            $this->crud->setRequest($request);
        }

        $this->crud->setRequest($this->crud->validateRequest());
        $request = $this->crud->getRequest();

        $employeeId = $request->input('employee_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($employeeId && $fromDate && $toDate) {
            $overlappingLeaves = EmployeeLeave::where('employee_id', $employeeId)
                ->where(function($q) use ($fromDate, $toDate) {
                    $q->where(function($subQ) use ($fromDate, $toDate) {
                        $subQ->whereBetween('from_date', [$fromDate, $toDate])
                             ->orWhereBetween('to_date', [$fromDate, $toDate])
                             ->orWhere(function($q2) use ($fromDate, $toDate) {
                                 $q2->where('from_date', '<=', $fromDate)
                                    ->where('to_date', '>=', $toDate);
                             });
                    });
                })
                ->first();

            if ($overlappingLeaves) {
                \Alert::error('Nhân sự này đã có đơn nghỉ phép trong khoảng thời gian từ ' .
                             \Carbon\Carbon::parse($overlappingLeaves->from_date)->format('d/m/Y') .
                             ' đến ' .
                             \Carbon\Carbon::parse($overlappingLeaves->to_date)->format('d/m/Y') .
                             '. Vui lòng chọn khoảng thời gian khác.')->flash();

                return redirect()->back()->withInput();
            }
        }

        $employee = Employee::find($employeeId);
        $departmentHead = null;

        if ($employee && $employee->department_id) {
            $departmentHead = \App\Models\User::where('department_id', $employee->department_id)
                ->where(function($q) {
                    $q->where('is_department_head', true)
                      ->orWhereHas('roles', function($r) {
                          $r->whereIn('name', ['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);
                      });
                })
                ->first();
        }

        // Set additional fields
        $request->merge([
            'status' => EmployeeLeave::STATUS_PENDING,
            'workflow_status' => EmployeeLeave::WORKFLOW_PENDING,
            'created_by' => $user->name ?: $user->username,
            'updated_by' => $user->name ?: $user->username
        ]);

        $this->crud->setRequest($request);
        $this->crud->unsetValidation();

        $response = $this->traitStore();

        if ($departmentHead) {
            \Log::info('Leave request created, assigned to department head', [
                'leave_id' => $this->crud->entry->id ?? null,
                'department_head' => $departmentHead->name,
                'department_id' => $employee->department_id
            ]);
        }

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $saveAction = $request->input('_save_action', 'save_and_back');

            if ($saveAction === 'save_and_back' || $saveAction === 'save_and_new') {
                return redirect($this->crud->route);
            }
        }

        return $response;
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $user = backpack_user();
        $request = $this->crud->getRequest();

        $entry = $this->crud->getCurrentEntry();
        $employeeId = $request->input('employee_id');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($employeeId && $fromDate && $toDate && $entry) {
            $overlappingLeaves = EmployeeLeave::where('employee_id', $employeeId)
                ->where('id', '!=', $entry->id)
                ->where(function($q) use ($fromDate, $toDate) {
                    $q->where(function($subQ) use ($fromDate, $toDate) {
                        $subQ->whereBetween('from_date', [$fromDate, $toDate])
                             ->orWhereBetween('to_date', [$fromDate, $toDate])
                             ->orWhere(function($q2) use ($fromDate, $toDate) {
                                 $q2->where('from_date', '<=', $fromDate)
                                    ->where('to_date', '>=', $toDate);
                             });
                    });
                })
                ->first();

            if ($overlappingLeaves) {
                \Alert::error('Nhân sự này đã có đơn nghỉ phép trong khoảng thời gian từ ' .
                             \Carbon\Carbon::parse($overlappingLeaves->from_date)->format('d/m/Y') .
                             ' đến ' .
                             \Carbon\Carbon::parse($overlappingLeaves->to_date)->format('d/m/Y') .
                             '. Vui lòng chọn khoảng thời gian khác.')->flash();

                return redirect()->back()->withInput();
            }
        }

        // If status is being changed to approved, set approved_by and approved_at
        if ($request->input('status') == EmployeeLeave::STATUS_APPROVED) {
            $request->merge([
                'approved_by' => $user->id,
                'approved_at' => now(),
                'workflow_status' => EmployeeLeave::WORKFLOW_APPROVED
            ]);
        } elseif ($request->input('status') == EmployeeLeave::STATUS_REJECTED) {
            $request->merge(['workflow_status' => EmployeeLeave::WORKFLOW_REJECTED]);
        }

        $request->merge(['updated_by' => $user->name ?: $user->username]);

        $this->crud->setRequest($request);
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }

    // ❌ REMOVED: approve(), reject(), generateSignedPdf(), generatePdfContent()
    // ✅ These are now handled by ApprovalWorkflow module's ApprovalController!

    /**
     * Add status filter cards widget
     */
    private function addStatusFilterCards()
    {
        $user = backpack_user();

        // Get counts for each status (respecting current user's filtering)
        // Cards should always show total counts for each status, not filtered by current status filter
        $baseQuery = EmployeeLeave::query();

        // Apply same filtering as list (for user permissions, not status filter)
        // For admin, leave.review, and leave.view.all users, show all counts
        $hasViewAllPermission = PermissionHelper::can($user, 'leave.view.all');
        if (!$user->hasRole('Admin') && !PermissionHelper::can($user, 'leave.review') && !$hasViewAllPermission) {
            // Apply same filtering logic but for counting
            $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);

            // Reviewer: user with leave.review permission (not just role BGD)
            $isReviewer = PermissionHelper::can($user, 'leave.review');

            // Director: BGD role but NOT reviewer (or Giám đốc role)
            $hasBgdRole = $user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc']);
            $hasGiámDocRole = $user->hasRole(['Giám đốc']);
            $isDirector = ($hasBgdRole && !$isReviewer) || $hasGiámDocRole;

            $userDepartmentId = $user->department_id;

            if ($user->employee_id) {
                $emp = Employee::find($user->employee_id);
                if ($emp && $emp->department_id) {
                    $userDepartmentId = $emp->department_id;
                }
            }

            $baseQuery->where(function($q) use ($user, $isDepartmentHead, $isReviewer, $isDirector, $userDepartmentId) {
                $hasCondition = false;

                if ($user->employee_id) {
                    $q->where(function($subQ) use ($user) {
                        $subQ->where('employee_id', $user->employee_id);
                    });
                    $hasCondition = true;
                }

                if ($isDepartmentHead && $userDepartmentId) {
                    $employeeIds = Employee::where('department_id', $userDepartmentId)->pluck('id');
                    if ($employeeIds->isNotEmpty()) {
                        if ($hasCondition) {
                            $q->orWhere(function($subQ) use ($employeeIds) {
                                $subQ->whereIn('employee_id', $employeeIds);
                            });
                        } else {
                            $q->where(function($subQ) use ($employeeIds) {
                                $subQ->whereIn('employee_id', $employeeIds);
                            });
                            $hasCondition = true;
                        }
                    }
                }

                if ($isReviewer) {
                    if ($hasCondition) {
                        $q->orWhereHas('approvalRequest', function($arQ) {
                            $arQ->where('status', 'in_review')
                                ->where('current_step', 'review');
                        });
                    } else {
                        $q->whereHas('approvalRequest', function($arQ) {
                            $arQ->where('status', 'in_review')
                                ->where('current_step', 'review');
                        });
                        $hasCondition = true;
                    }
                }

                // Director can see requests approved by reviewer (waiting for approval)
                // AND requests already approved by them (for tracking)
                if ($isDirector) {
                    $userId = (int)$user->id;
                    if ($hasCondition) {
                        $q->orWhere(function($subQ) use ($userId) {
                            $subQ->whereHas('approvalRequest', function($arQ) use ($userId) {
                                $arQ->where('status', 'in_review')
                                    ->where('current_step', 'director_approval')
                                    ->where(function($jsonQ) use ($userId) {
                                        $jsonQ->whereJsonContains('selected_approvers->director_approval->users', ['id' => $userId])
                                              ->orWhereJsonContains('selected_approvers->director_approval->users', $userId);
                                    });
                            })
                            ->orWhereHas('approvalRequest', function($arQ) use ($userId) {
                                $arQ->where('status', 'approved')
                                    ->whereJsonContains('approval_history->director_approval->approved_by', $userId);
                            });
                        });
                    } else {
                        $q->where(function($subQ) use ($userId) {
                            $subQ->whereHas('approvalRequest', function($arQ) use ($userId) {
                                $arQ->where('status', 'in_review')
                                    ->where('current_step', 'director_approval')
                                    ->where(function($jsonQ) use ($userId) {
                                        $jsonQ->whereJsonContains('selected_approvers->director_approval->users', ['id' => $userId])
                                              ->orWhereJsonContains('selected_approvers->director_approval->users', $userId);
                                    });
                            })
                            ->orWhereHas('approvalRequest', function($arQ) use ($userId) {
                                $arQ->where('status', 'approved')
                                    ->whereJsonContains('approval_history->director_approval->approved_by', $userId);
                            });
                        });
                        $hasCondition = true;
                    }
                }

                if (!$hasCondition) {
                    $q->where('id', 0);
                }
            });
        }

        // Get counts for each status - query from approval_requests
        $statusCounts = [
            // pending = submitted status in approval_requests or no approval_request
            'pending' => (clone $baseQuery)
                ->where(function($q) {
                    $q->whereHas('approvalRequest', function($subQ) {
                        $subQ->where('status', 'submitted');
                    })
                    ->orWhereDoesntHave('approvalRequest');
                })
                ->count(),

            // approved_by_department_head = in_review with current_step = department_head_approval
            'approved_by_department_head' => (clone $baseQuery)
                ->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'in_review')
                      ->where('current_step', 'department_head_approval');
                })
                ->count(),

            // approved_by_reviewer = in_review with current_step = review
            'approved_by_reviewer' => (clone $baseQuery)
                ->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'in_review')
                      ->where('current_step', 'review');
                })
                ->count(),

            // approved_by_director = approved status
            'approved_by_director' => (clone $baseQuery)
                ->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'approved');
                })
                ->count(),

            // rejected = rejected status
            'rejected' => (clone $baseQuery)
                ->whereHas('approvalRequest', function($q) {
                    $q->where('status', 'rejected');
                })
                ->count(),

            'all' => (clone $baseQuery)->count(),
        ];

        \Widget::add([
            'type' => 'view',
            'view' => 'personnelreport::widgets.status_filter_cards',
            'content' => [
                'statusCounts' => $statusCounts,
                'route' => $this->crud->getRoute()
            ]
        ])->to('before_content');
    }

    /**
     * Add month/year filter widget
     */
    private function addMonthYearFilter()
    {
        // Get all distinct month/year from employee_leave table
        $monthYears = EmployeeLeave::selectRaw('DATE_FORMAT(from_date, "%Y-%m") as month_year')
            ->distinct()
            ->whereNotNull('from_date')
            ->orderBy('month_year', 'desc')
            ->pluck('month_year')
            ->toArray();

        // Get current month/year
        $currentMonthYear = now()->format('Y-m');

        // Add current month if not in list
        if (!in_array($currentMonthYear, $monthYears)) {
            array_unshift($monthYears, $currentMonthYear);
        }

        // Get selected month/year from request (default to current)
        $selectedMonthYear = request()->get('month_year', $currentMonthYear);

        // Build options array
        $options = [['value' => 'all', 'label' => 'Tất cả']];
        foreach ($monthYears as $my) {
            $date = \Carbon\Carbon::parse($my . '-01');
            $options[] = [
                'value' => $my,
                'label' => $date->format('m/Y') // Format: 11/2025
            ];
        }

        \Widget::add([
            'type' => 'view',
            'view' => 'personnelreport::widgets.month_year_filter',
            'content' => [
                'options' => $options,
                'selected' => $selectedMonthYear,
                'route' => $this->crud->getRoute()
            ]
        ])->to('before_content');
    }

    /**
     * Add department filter widget
     */
    private function addDepartmentFilter()
    {
        $user = backpack_user();

        // Show all departments (not just those with leave requests)
        // Respecting user permissions
        if ($user->hasRole('Admin') || PermissionHelper::can($user, 'leave.review')) {
            // Admin and reviewer: show all departments
            $departments = Department::orderBy('name', 'asc')->get();
        } else {
            // For other users, only show departments they have access to
            $userDepartmentId = $user->department_id;
            if ($user->employee_id) {
                $emp = Employee::find($user->employee_id);
                if ($emp && $emp->department_id) {
                    $userDepartmentId = $emp->department_id;
                }
            }

            // Check if user is department head - they can see their department
            $isDepartmentHead = $user->is_department_head || $user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng', 'Trưởng phòng ban']);

            $departmentIds = [];
            if ($userDepartmentId && ($isDepartmentHead || $user->employee_id)) {
                $departmentIds[] = $userDepartmentId;
            }

            // Get departments user can access
            if (!empty($departmentIds)) {
                $departments = Department::whereIn('id', $departmentIds)
                    ->orderBy('name', 'asc')
                    ->get();
            } else {
                $departments = collect([]);
            }
        }

        // Get selected department from request
        $selectedDepartment = request()->get('department_id', 'all');

        // Build options array
        $options = [['value' => 'all', 'label' => 'Tất cả phòng ban']];
        foreach ($departments as $dept) {
            $options[] = [
                'value' => $dept->id,
                'label' => $dept->name
            ];
        }

        \Widget::add([
            'type' => 'view',
            'view' => 'personnelreport::widgets.department_filter',
            'content' => [
                'options' => $options,
                'selected' => $selectedDepartment,
                'route' => $this->crud->getRoute()
            ]
        ])->to('before_content');
    }

    protected function addWorkflowProgressWidget()
    {
        $entry = $this->crud->getCurrentEntry();

        if (!$entry) {
            return;
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

        if ($entry->created_at) {
            $stepDates['created'] = $entry->created_at->format('d/m/Y H:i');
        }
        if ($entry->employee) {
            $stepUsers['created'] = $entry->employee->name ?? 'N/A';
        }

        if ($entry->approved_at_department_head && $entry->workflow_status !== EmployeeLeave::WORKFLOW_PENDING) {
            $date = $entry->approved_at_department_head;
            if (!$date instanceof \Carbon\Carbon) {
                $date = \Carbon\Carbon::parse($date);
            }
            $stepDates[EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD] = $date->format('d/m/Y H:i');
        }
        if ($entry->approved_by_department_head && $entry->workflow_status !== EmployeeLeave::WORKFLOW_PENDING) {
            $deptHead = \App\Models\User::find($entry->approved_by_department_head);
            if ($deptHead) {
                $stepUsers[EmployeeLeave::WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD] = $deptHead->name ?? 'N/A';
            }
        }

        if ($entry->approved_at_reviewer && in_array($entry->workflow_status, [
            EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER,
            EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR
        ])) {
            $date = $entry->approved_at_reviewer;
            if (!$date instanceof \Carbon\Carbon) {
                $date = \Carbon\Carbon::parse($date);
            }
            $stepDates[EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER] = $date->format('d/m/Y H:i');
        }
        if ($entry->approved_by_reviewer && in_array($entry->workflow_status, [
            EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER,
            EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR
        ])) {
            $reviewer = \App\Models\User::find($entry->approved_by_reviewer);
            if ($reviewer) {
                $stepUsers[EmployeeLeave::WORKFLOW_APPROVED_BY_REVIEWER] = $reviewer->name ?? 'N/A';
            }
        }

        if ($entry->approved_at_director && $entry->workflow_status === EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR) {
            $date = $entry->approved_at_director;
            if (!$date instanceof \Carbon\Carbon) {
                $date = \Carbon\Carbon::parse($date);
            }
            $stepDates[EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR] = $date->format('d/m/Y H:i');
        }
        if ($entry->approved_by_director && $entry->workflow_status === EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR) {
            $director = \App\Models\User::find($entry->approved_by_director);
            if ($director) {
                $stepUsers[EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR] = $director->name ?? 'N/A';
            }
        }

        $currentStatusKey = $entry->workflow_status;
        $currentStepIndex = 0;

        switch ($currentStatusKey) {
            case EmployeeLeave::WORKFLOW_APPROVED_BY_DIRECTOR:
                $currentStepIndex = 4;
                if ($entry->approved_at_director) {
                    $date = $entry->approved_at_director;
                    if (!$date instanceof \Carbon\Carbon) {
                        $date = \Carbon\Carbon::parse($date);
                    }
                    $stepDates['completed'] = $date->format('d/m/Y H:i');
                    if ($entry->approved_by_director) {
                        $director = \App\Models\User::find($entry->approved_by_director);
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
                if ($entry->approved_at_reviewer) {
                    $currentStepIndex = 3;
                } elseif ($entry->approved_at_department_head) {
                    $currentStepIndex = 2;
                } else {
                    $currentStepIndex = 1;
                }
                break;
            default:
                if ($entry->approved_at_director) {
                    $currentStepIndex = 4;
                } elseif ($entry->approved_at_reviewer) {
                    $currentStepIndex = 3;
                } elseif ($entry->approved_at_department_head) {
                    $currentStepIndex = 2;
                } else {
                    $currentStepIndex = 1;
                }
                break;
        }

        \Widget::add([
            'type' => 'view',
            'view' => 'components.workflow-progress',
            'content' => [
                'steps' => $steps,
                'currentStatus' => $entry->workflow_status,
                'currentStepIndex' => $currentStepIndex,
                'rejected' => $entry->workflow_status === EmployeeLeave::WORKFLOW_REJECTED,
                'stepDates' => $stepDates,
                'stepUsers' => $stepUsers
            ]
        ])->to('before_content');
    }

    protected function setupShowOperation()
    {
        $this->addWorkflowProgressWidget();

        // Setup columns for show (but don't apply filtering)
        $this->setupButtonsForRole();

        CRUD::column('employee_name')
            ->label('Nhân sự')
            ->type('closure')
            ->function(function($entry) {
                return $entry->employee ? $entry->employee->name : 'N/A';
            });

        CRUD::column('department_name')
            ->label('Phòng ban')
            ->type('closure')
            ->function(function($entry) {
                return $entry->employee && $entry->employee->department
                    ? $entry->employee->department->name
                    : 'N/A';
            });

        CRUD::column('leave_type')
            ->label('Loại nghỉ')
            ->type('closure')
            ->function(function($entry) {
                $types = [
                    'business'   => 'Công tác - Cơ động',
                    'study'      => 'Học',
                    'leave'      => 'Phép - Tranh thủ',
                    'hospital'   => 'Đi viện',
                    'pending'    => 'Chờ hưu',
                    'sick'       => 'Ốm tại trại',
                    'maternity'  => 'Thai sản',
                    'checkup'    => 'Khám bệnh',
                    'other' => 'Khác'
                ];
                return $types[$entry->leave_type] ?? $entry->leave_type;
            });

        CRUD::column('from_date')
            ->label('Từ ngày')
            ->type('date');

        CRUD::column('to_date')
            ->label('Đến ngày')
            ->type('date');

        CRUD::column('location')
            ->label('Địa điểm');

        CRUD::column('workflow_status')
            ->label('Trạng thái')
            ->type('closure')
            ->function(function($entry) {
                return $entry->workflow_status_text;
            });

        CRUD::column('note')
            ->label('Ghi chú')
            ->limit(500);

        CRUD::column('rejection_reason')
            ->label('Lý do từ chối')
            ->type('closure')
            ->function(function($entry) {
                $approvalRequest = $entry->approvalRequest;
                if ($approvalRequest && ($approvalRequest->status === 'rejected' || $approvalRequest->status === 'returned')) {
                    $reason = $approvalRequest->rejection_reason;
                    if (is_string($reason)) {
                        $decoded = json_decode($reason, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            return 'Dữ liệu không hợp lệ';
                        }
                    }
                    return $reason ?: null;
                }
                return null;
            })
            ->visible(function($entry) {
                $approvalRequest = $entry->approvalRequest;
                if ($approvalRequest && ($approvalRequest->status === 'rejected' || $approvalRequest->status === 'returned')) {
                    return !empty($approvalRequest->rejection_reason);
                }
                return false;
            });
    }

    /**
     * Download signed PDF
     */
    public function downloadPdf($id)
    {
        $leaveRequest = EmployeeLeave::findOrFail($id);

        if (!$leaveRequest->signed_pdf_path) {
            abort(404, 'PDF chưa được tạo');
        }

        $filePath = storage_path('app/public/' . $leaveRequest->signed_pdf_path);

        if (!file_exists($filePath)) {
            abort(404, 'File PDF không tồn tại');
        }

        return response()->download($filePath, 'don_xin_nghi_phep_' . $leaveRequest->id . '.pdf');
    }

}

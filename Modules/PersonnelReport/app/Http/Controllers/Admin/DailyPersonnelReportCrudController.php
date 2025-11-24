<?php

namespace Modules\PersonnelReport\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\PersonnelReport\Models\DailyPersonnelReport;
use Modules\OrganizationStructure\Models\Department;

class DailyPersonnelReportCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(DailyPersonnelReport::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/daily-personnel-report');
        CRUD::setEntityNameStrings('báo cáo quân số', 'báo cáo quân số');

        CRUD::orderBy('report_date', 'DESC');

        // Apply department filtering based on user permissions
        $this->applyDepartmentFilter();

        // Setup buttons based on user role
        $this->setupButtonsForRole();
    }

    /**
     * Apply department filtering based on user permissions
     */
    private function applyDepartmentFilter()
    {
        $user = backpack_user();

        // Admin and BAN GIÁM ĐỐC can see all data
        if ($user->hasRole('admin') || $user->department_id == 1) {
            return; // No filtering for admin and BAN GIÁM ĐỐC
        }

        // First try to get department from user's direct department_id
        $departmentId = $user->department_id;

        // Fallback to employee's department if user doesn't have direct department
        if (!$departmentId && $user->employee_id) {
            $employee = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
            if ($employee) {
                $departmentId = $employee->department_id;
            }
        }

        if ($departmentId) {
            CRUD::addClause('where', 'department_id', $departmentId);
        } else {
            // If user has no department, show no reports
            CRUD::addClause('where', 'id', 0);
        }
    }

    /**
     * Setup buttons based on user role
     */
    private function setupButtonsForRole()
    {
        $user = backpack_user();

        // For Nhân sự role - only view, no create/edit/delete
        if ($user->hasRole('Nhân sự')) {
            CRUD::removeButton('create');
            CRUD::removeButton('edit');
            CRUD::removeButton('delete');
        }
    }

    protected function setupListOperation()
    {
        // Check permission - only users with "report.view.company" can access summary
        if (!backpack_user()->hasPermissionTo('report.view.company')) {
            abort(403, 'Không có quyền xem tổng hợp báo cáo quân số');
        }
    }
    
    /**
     * Override index to show custom summary view
     */
    public function index()
    {
        // Check permission
        if (!backpack_user()->hasPermissionTo('report.view.company')) {
            abort(403, 'Không có quyền xem tổng hợp báo cáo quân số');
        }
        
        $selectedDate = request('report_date', now()->format('Y-m-d'));
        
        // Get all departments
        $departments = \Modules\OrganizationStructure\Models\Department::orderBy('id')->get();
        
        // Get reports for selected date
        $reports = DailyPersonnelReport::whereDate('report_date', $selectedDate)->get();
        
        // ✅ Get all approved leave requests for this date
        // Only use workflow_status as source of truth
        // Include both level 1 and level 2 approvals
        $approvedLeaves = \Modules\PersonnelReport\Models\EmployeeLeave::whereIn('workflow_status', [
                'approved_by_approver',  // Approved by level 1 (still valid for tracking)
                'approved_by_director',  // Final approval by level 2
                'approved'               // Alternative final status
            ])
            ->where('from_date', '<=', $selectedDate)
            ->where('to_date', '>=', $selectedDate)
            ->with('employee.department')
            ->get();
        
        \Log::info('Auto-load approved leaves (summary)', [
            'selected_date' => $selectedDate,
            'approved_leaves_count' => $approvedLeaves->count()
        ]);
        
        return view('personnelreport::daily_report_summary', compact('departments', 'reports', 'selectedDate', 'approvedLeaves'));
    }

    /**
     * Override create to load existing data if available
     */
    public function create()
    {
        $this->crud->hasAccessOrFail('create');
        
        // Filter departments based on user's department
        $user = backpack_user();
        $departmentOptions = [];
        $userDepartmentId = null;
        
        if ($user->hasRole('admin') || $user->department_id == 1) {
            $departmentOptions = Department::pluck('name', 'id')->toArray();
        } else {
            // First try to get department from user's direct department_id
            $departmentId = $user->department_id;

            // Fallback to employee's department if user doesn't have direct department
            if (!$departmentId && $user->employee_id) {
                $employee = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                if ($employee) {
                    $departmentId = $employee->department_id;
                }
            }

            if ($departmentId) {
                $department = Department::find($departmentId);
                if ($department) {
                    $departmentOptions = [$departmentId => $department->name];
                    $userDepartmentId = $departmentId;
                }
            }
        }
        
        // Check if there's a report_date in query string
        $reportDate = request('report_date');
        $existingReport = null;
        
        if ($reportDate && $userDepartmentId) {
            // Try to find existing report for this department and date
            $existingReport = DailyPersonnelReport::where('department_id', $userDepartmentId)
                ->where('report_date', $reportDate)
                ->first();
        }
        
        return view('personnelreport::daily_report_create', [
            'crud' => $this->crud,
            'departmentOptions' => $departmentOptions,
            'existingReport' => $existingReport,
            'reportDate' => $reportDate ?: now()->format('Y-m-d'),
            'userDepartmentId' => $userDepartmentId
        ]);
    }
    
    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'department_id' => 'required|exists:departments,id',
            'report_date' => 'required|date',
        ]);

        // Use custom create view
        $this->crud->setCreateView('personnelreport::daily_report_create');

        // Filter departments based on user's department
        $departmentOptions = [];
        $user = backpack_user();
        if ($user->hasRole('admin') || $user->department_id == 1) {
            $departmentOptions = Department::pluck('name', 'id')->toArray();
        } else {
            // First try to get department from user's direct department_id
            $departmentId = $user->department_id;

            // Fallback to employee's department if user doesn't have direct department
            if (!$departmentId && $user->employee_id) {
                $employee = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                if ($employee) {
                    $departmentId = $employee->department_id;
                }
            }

            if ($departmentId) {
                $department = Department::find($departmentId);
                if ($department) {
                    $departmentOptions = [$departmentId => $department->name];
                }
            }
        }
        
        // Pass to view
        $this->data['departmentOptions'] = $departmentOptions;

        CRUD::field('department_id')
            ->label('Phòng ban')
            ->type('select_from_array')
            ->options($departmentOptions)
            ->tab('Thông tin cơ bản');

        CRUD::field('report_date')
            ->label('Ngày báo cáo')
            ->type('date')
            ->default(today())
            ->tab('Thông tin cơ bản');

        // Read-only fields that will be calculated
        CRUD::field('total_employees')
            ->label('Tổng số Nhân sự')
            ->type('number')
            ->attributes(['readonly' => true])
            ->tab('Thống kê');

        CRUD::field('present_count')
            ->label('Số người có mặt')
            ->type('number')
            ->tab('Thống kê');

        CRUD::field('absent_count')
            ->label('Số người vắng mặt')
            ->type('number')
            ->tab('Thống kê');

        CRUD::field('on_leave_count')
            ->label('Số người nghỉ phép')
            ->type('number')
            ->tab('Thống kê');

        CRUD::field('sick_count')
            ->label('Công tác')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');

        CRUD::field('annual_leave_count')
            ->label('Cơ Động')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');

        CRUD::field('personal_leave_count')
            ->label('Đi học')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');

        CRUD::field('military_leave_count')
            ->label('Nghỉ phép')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');

        CRUD::field('other_leave_count')
            ->label('Khác')
            ->type('number')
            ->tab('Chi tiết nghỉ phép');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        $this->setupListOperation();

        CRUD::column('sick_count')->label('Công tác');
        CRUD::column('annual_leave_count')->label('Cơ Động');
        CRUD::column('personal_leave_count')->label('Đi học');
        CRUD::column('military_leave_count')->label('Nghỉ phép');
        CRUD::column('other_leave_count')->label('Khác');
        CRUD::column('created_by')->label('Người tạo');
        CRUD::column('updated_by')->label('Người cập nhật');
    }
    
    /**
     * API: Lấy thống kê quân số theo phòng ban
     */
    public function getDepartmentStats($departmentId)
    {
        try {
            \Log::info("API getDepartmentStats called", ['department_id' => $departmentId]);
            
            $department = Department::find($departmentId);
            
            if (!$department) {
                \Log::warning("Department not found", ['department_id' => $departmentId]);
                return response()->json(['error' => 'Phòng ban không tồn tại'], 404);
            }
            
            // Đếm tổng nhân viên active
            $totalEmployees = $department->employees()->active()->count();
            
            // Tạm thời: Đếm theo rank_code để phân loại
            // SQ: Sĩ quan (có chữ "úy" hoặc "tá" hoặc "tướng")
            // QNCN: Quân nhân chuyên nghiệp (có chữ "sĩ")
            // CNQP: Còn lại (null hoặc không match)
            
            $employees = $department->employees()->active()->get();
            
            $sqCount = 0; // Sĩ quan
            $qncnCount = 0; // Quân nhân CN
            $cnqpCount = 0; // Công nhân QP
            
            foreach ($employees as $emp) {
                $rankCode = strtolower($emp->rank_code ?? '');
                
                if (strpos($rankCode, 'úy') !== false || 
                    strpos($rankCode, 'tá') !== false || 
                    strpos($rankCode, 'tướng') !== false ||
                    strpos($rankCode, 'đại') !== false) {
                    $sqCount++;
                } elseif (strpos($rankCode, 'sĩ') !== false) {
                    $qncnCount++;
                } else {
                    $cnqpCount++;
                }
            }
            
            $result = [
                'total' => $totalEmployees,
                'sq' => $sqCount,
                'qncn' => $qncnCount,
                'cnqp' => $cnqpCount
            ];
            
            \Log::info("Department stats calculated", $result);
            
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error("Error in getDepartmentStats", [
                'department_id' => $departmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Hiển thị báo cáo quân số format đẹp
     */
    public function showReport($id)
    {
        $report = $this->crud->getEntry($id);
        
        if (!$report) {
            abort(404, 'Báo cáo không tồn tại');
        }
        
        // Calculate statistics
        $stats = [
            'total_sq' => $report->total_strength ?? 0,
            'total_qncn' => $report->present_count ?? 0,
            'total_cnqp' => $report->absent_count ?? 0,
            'present_sq' => $report->present_count ?? 0,
            'present_qncn' => $report->present_count ?? 0,
            'present_cnqp' => 0,
            'absent_sq' => $report->absent_count ?? 0,
            'absent_qncn' => $report->absent_count ?? 0,
            'absent_cnqp' => 0,
        ];
        
        return view('personnelreport::daily_report_view', compact('report', 'stats'));
    }

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $user = backpack_user();
        $request = $this->crud->getRequest();

        // Auto-generate report data
        $departmentId = $request->input('department_id');
        $reportDate = $request->input('report_date');

        // ✅ Check if report already exists
        $existingReport = DailyPersonnelReport::where('department_id', $departmentId)
            ->where('report_date', $reportDate)
            ->first();

        if ($existingReport) {
            // ✅ Update existing report instead of showing error
            $existingReport->update([
                'total_employees' => $request->input('total_sq', 0) + $request->input('total_qncn', 0) + $request->input('total_cnqp', 0),
                'present_count' => $request->input('present_sq', 0) + $request->input('present_qncn', 0) + $request->input('present_cnqp', 0),
                'absent_count' => $request->input('absent_sq', 0) + $request->input('absent_qncn', 0) + $request->input('absent_cnqp', 0),
                'on_leave_count' => 0,
                'sick_count' => $request->input('cong_tac_sq', 0) + $request->input('cong_tac_qncn', 0) + $request->input('cong_tac_cnqp', 0),
                'annual_leave_count' => $request->input('co_dong_sq', 0) + $request->input('co_dong_qncn', 0) + $request->input('co_dong_cnqp', 0),
                'personal_leave_count' => $request->input('di_hoc_sq', 0) + $request->input('di_hoc_qncn', 0) + $request->input('di_hoc_cnqp', 0),
                'military_leave_count' => $request->input('di_phep_sq', 0) + $request->input('di_phep_qncn', 0) + $request->input('di_phep_cnqp', 0),
                'other_leave_count' => $request->input('ly_do_khac_sq', 0) + $request->input('ly_do_khac_qncn', 0) + $request->input('ly_do_khac_cnqp', 0),
                'note' => $request->input('note'),
                'updated_by' => $user->name ?: $user->username
            ]);

            \Alert::success('Cập nhật báo cáo thành công!')->flash();
        } else {
            // ✅ Create report with custom data from form
            DailyPersonnelReport::create([
                'department_id' => $departmentId,
                'report_date' => $reportDate,
                'total_employees' => $request->input('total_sq', 0) + $request->input('total_qncn', 0) + $request->input('total_cnqp', 0),
                'present_count' => $request->input('present_sq', 0) + $request->input('present_qncn', 0) + $request->input('present_cnqp', 0),
                'absent_count' => $request->input('absent_sq', 0) + $request->input('absent_qncn', 0) + $request->input('absent_cnqp', 0),
                'on_leave_count' => 0,
                'sick_count' => $request->input('cong_tac_sq', 0) + $request->input('cong_tac_qncn', 0) + $request->input('cong_tac_cnqp', 0),
                'annual_leave_count' => $request->input('co_dong_sq', 0) + $request->input('co_dong_qncn', 0) + $request->input('co_dong_cnqp', 0),
                'personal_leave_count' => $request->input('di_hoc_sq', 0) + $request->input('di_hoc_qncn', 0) + $request->input('di_hoc_cnqp', 0),
                'military_leave_count' => $request->input('di_phep_sq', 0) + $request->input('di_phep_qncn', 0) + $request->input('di_phep_cnqp', 0),
                'other_leave_count' => $request->input('ly_do_khac_sq', 0) + $request->input('ly_do_khac_qncn', 0) + $request->input('ly_do_khac_cnqp', 0),
                'note' => $request->input('note'),
                'created_by' => $user->name ?: $user->username,
                'updated_by' => $user->name ?: $user->username
            ]);

            \Alert::success('Tạo báo cáo thành công!')->flash();
        }

        // ✅ Redirect back to create form with report_date
        return redirect(backpack_url('daily-personnel-report/create') . '?report_date=' . $reportDate);
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        $user = backpack_user();
        $request = $this->crud->getRequest();

        $request->merge(['updated_by' => $user->name ?: $user->username]);

        $this->crud->setRequest($request);
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }
    
    /**
     * Smart UI version - Create report with better UX
     */
    public function create2()
    {
        $this->crud->hasAccessOrFail('create');
        
        $user = backpack_user();
        $departmentId = null;
        
        // Get user's department
        if ($user->hasRole('admin') || $user->department_id == 1) {
            // Admin can select any department - but for create-2, we'll still use their department
            $departmentId = $user->department_id ?: 1;
        } else {
            $departmentId = $user->department_id;
            
            // Fallback to employee's department
            if (!$departmentId && $user->employee_id) {
                $employee = \Modules\OrganizationStructure\Models\Employee::find($user->employee_id);
                if ($employee) {
                    $departmentId = $employee->department_id;
                }
            }
        }
        
        if (!$departmentId) {
            abort(403, 'Bạn chưa được gán phòng ban. Vui lòng liên hệ admin.');
        }
        
        // Get department info
        $department = Department::find($departmentId);
        
        if (!$department) {
            abort(404, 'Phòng ban không tồn tại.');
        }
        
        // Get all active employees in this department with relationships
        $employees = $department->employees()->active()
            ->with(['position', 'department'])
            ->get();
        
        // Get report date from query string or use today
        $reportDate = request('report_date', now()->format('Y-m-d'));
        
        // Check if report already exists
        $existingReport = DailyPersonnelReport::where('department_id', $departmentId)
            ->where('report_date', $reportDate)
            ->first();
        
        // Parse existing absent employees if report exists
        $absentEmployees = [];
        if ($existingReport && $existingReport->note) {
            // We'll store absent employees as JSON in the note field (for now)
            // Format: [{"employee_id": 1, "reason": "cong_tac", "note": "..."}]
            $decoded = json_decode($existingReport->note, true);
            $absentEmployees = is_array($decoded) ? $decoded : [];
        }
        
        // ✅ Auto-load approved leave requests for this date
        // Only use workflow_status as source of truth
        // Include both level 1 and level 2 approvals (both are considered "approved" for attendance tracking)
        $approvedLeaves = \Modules\PersonnelReport\Models\EmployeeLeave::whereIn('workflow_status', [
                'approved_by_approver',  // Approved by level 1 (still valid for tracking)
                'approved_by_director',  // Final approval by level 2
                'approved'               // Alternative final status
            ])
            ->where('from_date', '<=', $reportDate)
            ->where('to_date', '>=', $reportDate)
            ->whereHas('employee', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->with('employee.position')
            ->get();
        
        \Log::info('Auto-load approved leaves (create-2)', [
            'report_date' => $reportDate,
            'department_id' => $departmentId,
            'approved_leaves_count' => $approvedLeaves->count(),
            'leaves' => $approvedLeaves->map(function($l) {
                return [
                    'id' => $l->id,
                    'employee' => $l->employee->name ?? 'N/A',
                    'from' => $l->from_date->format('Y-m-d'),
                    'to' => $l->to_date->format('Y-m-d'),
                    'type' => $l->leave_type,
                    'workflow_status' => $l->workflow_status
                ];
            })->toArray()
        ]);
        
        // Map leave type to reason (matching EmployeeLeave leave types)
        $leaveTypeMap = [
            'business' => 'cong_tac_co_dong',    // Công tác - Cơ động
            'attendance' => 'cong_tac_co_dong',  // Legacy: Cơ động -> Công tác - Cơ động
            'study' => 'hoc',                    // Học
            'leave' => 'phep',                   // Phép - Tranh thủ
            'hospital' => 'di_vien',             // Đi viện
            'pending' => 'cho_huu',               // Chờ hưu
            'sick' => 'om_tai_trai',             // Ốm tại trại
            'maternity' => 'thai_san',            // Thai sản
            'checkup' => 'kham_benh',             // Khám bệnh
            'other' => 'khac'                     // Khác
        ];
        
        // Add approved leaves to absent employees (if not already added manually)
        foreach ($approvedLeaves as $leave) {
            // Check if employee already in absent list
            $alreadyAdded = false;
            foreach ($absentEmployees as $absent) {
                if ($absent['employee_id'] == $leave->employee_id) {
                    $alreadyAdded = true;
                    break;
                }
            }
            
            // Add if not already in list
            if (!$alreadyAdded) {
                $reason = $leaveTypeMap[$leave->leave_type] ?? 'khac';
                $absentEmployees[] = [
                    'employee_id' => $leave->employee_id,
                    'reason' => $reason,
                    'note' => $leave->note ?: '-', // Use note from leave request directly
                    'from_leave_request' => true, // Mark as from leave request
                    'leave_request_id' => $leave->id
                ];
            }
        }
        
        // Kiểm tra nếu ngày báo cáo là quá khứ (trước ngày hiện tại)
        // So sánh với start of today để chính xác hơn
        $reportDateCarbon = \Carbon\Carbon::parse($reportDate)->startOfDay();
        $todayCarbon = \Carbon\Carbon::today()->startOfDay();
        $isReadOnly = $reportDateCarbon->lt($todayCarbon); // Less than today = past date
        
        return view('personnelreport::daily_report_create_v2', [
            'crud' => $this->crud,
            'department' => $department,
            'employees' => $employees,
            'reportDate' => $reportDate,
            'existingReport' => $existingReport,
            'absentEmployees' => $absentEmployees,
            'isReadOnly' => $isReadOnly
        ]);
    }
    
    /**
     * Store report from smart UI
     */
    public function store2()
    {
        $user = backpack_user();
        $departmentId = request('department_id');
        $reportDate = request('report_date');
        $absentEmployeesRaw = request('absent_employees', '[]');
        
        // Parse JSON if it's a string
        if (is_string($absentEmployeesRaw)) {
            $absentEmployees = json_decode($absentEmployeesRaw, true);
            if (!is_array($absentEmployees)) {
                $absentEmployees = [];
            }
        } else {
            $absentEmployees = is_array($absentEmployeesRaw) ? $absentEmployeesRaw : [];
        }
        
        // Validate
        if (!$departmentId || !$reportDate) {
            \Alert::error('Thiếu thông tin phòng ban hoặc ngày báo cáo')->flash();
            return redirect()->back();
        }
        
        // Get department to count total employees
        $department = Department::find($departmentId);
        $totalEmployees = $department->employees()->active()->count();
        
        // Calculate statistics from absent employees
        $absentCount = count($absentEmployees);
        $presentCount = $totalEmployees - $absentCount;
        
        $congTacCount = 0;
        $coDongCount = 0;
        $hocCount = 0;
        $phepCount = 0;
        $khacCount = 0;
        
        foreach ($absentEmployees as $absent) {
            switch ($absent['reason'] ?? '') {
                case 'cong_tac':
                    $congTacCount++;
                    break;
                case 'co_dong':
                    $coDongCount++;
                    break;
                case 'hoc':
                    $hocCount++;
                    break;
                case 'phep':
                    $phepCount++;
                    break;
                case 'khac':
                    $khacCount++;
                    break;
            }
        }
        
        // Store absent employees as JSON
        $noteData = json_encode($absentEmployees, JSON_UNESCAPED_UNICODE);
        
        // Check if report exists
        $existingReport = DailyPersonnelReport::where('department_id', $departmentId)
            ->where('report_date', $reportDate)
            ->first();
        
        if ($existingReport) {
            // Update
            $existingReport->update([
                'total_employees' => $totalEmployees,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'sick_count' => $congTacCount,
                'annual_leave_count' => $coDongCount,
                'personal_leave_count' => $hocCount,
                'military_leave_count' => $phepCount,
                'other_leave_count' => $khacCount,
                'note' => $noteData,
                'updated_by' => $user->name ?: $user->username
            ]);
            
            \Alert::success('Cập nhật báo cáo thành công!')->flash();
        } else {
            // Create
            DailyPersonnelReport::create([
                'department_id' => $departmentId,
                'report_date' => $reportDate,
                'total_employees' => $totalEmployees,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'sick_count' => $congTacCount,
                'annual_leave_count' => $coDongCount,
                'personal_leave_count' => $hocCount,
                'military_leave_count' => $phepCount,
                'other_leave_count' => $khacCount,
                'note' => $noteData,
                'created_by' => $user->name ?: $user->username,
                'updated_by' => $user->name ?: $user->username
            ]);
            
            \Alert::success('Tạo báo cáo thành công!')->flash();
        }
        
        // Redirect back with report_date
        return redirect(backpack_url('daily-personnel-report/create-2') . '?report_date=' . $reportDate);
    }
    
}

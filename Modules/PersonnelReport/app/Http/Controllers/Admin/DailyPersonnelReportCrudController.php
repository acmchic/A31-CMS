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
        // Check permission - only users with "tong_hop_bao_cao_quan_so" can access summary
        if (!backpack_user()->hasPermissionTo('tong_hop_bao_cao_quan_so')) {
            abort(403, 'Không có quyền xem tổng hợp báo cáo quân số');
        }
    }
    
    /**
     * Override index to show custom summary view
     */
    public function index()
    {
        // Check permission
        if (!backpack_user()->hasPermissionTo('tong_hop_bao_cao_quan_so')) {
            abort(403, 'Không có quyền xem tổng hợp báo cáo quân số');
        }
        
        $selectedDate = request('report_date', now()->format('Y-m-d'));
        
        // Get all departments
        $departments = \Modules\OrganizationStructure\Models\Department::orderBy('id')->get();
        
        // Get reports for selected date
        $reports = DailyPersonnelReport::whereDate('report_date', $selectedDate)->get();
        
        return view('personnelreport::daily_report_summary', compact('departments', 'reports', 'selectedDate'));
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
            \Alert::error('Báo cáo cho phòng ban này vào ngày ' . date('d/m/Y', strtotime($reportDate)) . ' đã tồn tại!')->flash();
            return redirect()->back()->withInput();
        }

        // ✅ Create report with custom data from form
        $reportData = DailyPersonnelReport::create([
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
        return redirect(backpack_url('daily-personnel-report'));
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
}

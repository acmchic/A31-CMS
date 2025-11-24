@extends(backpack_view('blank'))

@section('content')
<style>
    .daily-report-container {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    
    .daily-report-table-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
    }
    
    .daily-report-table-wrapper table {
        width: 100%;
        table-layout: auto;
        min-width: 1800px;
    }
    
    .daily-report-table-wrapper th {
        white-space: normal;
        word-wrap: break-word;
        line-height: 1.3;
        padding: 10px 6px;
        vertical-align: middle;
        text-align: center;
        font-size: 0.8rem;
        min-width: 80px;
    }
    
    .daily-report-table-wrapper td {
        white-space: nowrap;
        padding: 8px 6px;
        text-align: center;
        font-size: 0.9rem;
    }
    
    /* Specific column widths for better fit */
    .daily-report-table-wrapper th:nth-child(1),
    .daily-report-table-wrapper td:nth-child(1) {
        min-width: 180px;
        width: 180px;
    }
    
    .daily-report-table-wrapper th:nth-child(2),
    .daily-report-table-wrapper td:nth-child(2) {
        min-width: 80px;
        width: 80px;
    }
    
    .daily-report-table-wrapper th:nth-child(3),
    .daily-report-table-wrapper td:nth-child(3),
    .daily-report-table-wrapper th:nth-child(4),
    .daily-report-table-wrapper td:nth-child(4),
    .daily-report-table-wrapper th:nth-child(5),
    .daily-report-table-wrapper td:nth-child(5) {
        min-width: 90px;
        width: 90px;
    }
    
    /* Leave type columns */
    .daily-report-table-wrapper th:nth-child(n+6),
    .daily-report-table-wrapper td:nth-child(n+6) {
        min-width: 110px;
        width: 110px;
    }
</style>

<div class="daily-report-container">
    <!-- Modern Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                <div class="card-body text-center py-4" style="color: #ffffff !important;">
                    <h3 class="mb-2" style="color: #ffffff !important;"><i class="la la-calendar-check" style="color: #ffffff !important;"></i> BÁO CÁO QUÂN SỐ HÀNG NGÀY</h3>
                    <h5 class="mb-0" style="color: #ffffff !important;">Sổ Tổng Hợp Quân Số Toàn Nhà Máy A31</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Selection Card -->
    <div class="row mb-4">
        <div class="col-md-6 offset-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ backpack_url('daily-personnel-report') }}">
                        <div class="row align-items-end">
                            <div class="col-md-8">
                                <label class="form-label"><strong><i class="la la-calendar"></i> Chọn ngày xem:</strong></label>
                                <input type="date" name="report_date" class="form-control" value="{{ $selectedDate }}" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-block" style="color: #ffffff !important;">
                                    <i class="la la-search" style="color: #ffffff !important;"></i> Xem báo cáo
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Date Badge -->
    <div class="row mb-3">
        <div class="col-12 text-center">
            <h4>
                <span class="badge badge-danger badge-lg" style="font-size: 1.1rem; padding: 0.6rem 1.5rem;">
                    <i class="la la-star"></i> Báo cáo ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                </span>
            </h4>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="daily-report-table-wrapper">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th style="background-color: #f5e6d3;">ĐƠN VỊ</th>
                                    <th style="background-color: #f5e6d3;">PHÂN LOẠI</th>
                                    <th style="background-color: #f5e6d3;">TỔNG QS</th>
                                    <th style="background-color: #c8e6c9;">CÓ MẶT</th>
                                    <th style="background-color: #ffccbc;">VẮNG MẶT</th>
                                    <th style="background-color: #d4e6f1; white-space: normal; line-height: 1.3;">CÔNG TÁC<br>-<br>CƠ ĐỘNG</th>
                                    <th style="background-color: #d4e6f1;">HỌC</th>
                                    <th style="background-color: #d4e6f1; white-space: normal; line-height: 1.3;">PHÉP<br>-<br>TRANH THỦ</th>
                                    <th style="background-color: #d4e6f1;">ĐI VIỆN</th>
                                    <th style="background-color: #d4e6f1;">CHỜ HƯU</th>
                                    <th style="background-color: #d4e6f1;">ỐM TẠI TRẠI</th>
                                    <th style="background-color: #d4e6f1;">THAI SẢN</th>
                                    <th style="background-color: #d4e6f1;">KHÁM BỆNH</th>
                                    <th style="background-color: #d4e6f1;">KHÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grandTotal = [
                                        'total' => 0, 'present' => 0, 'absent' => 0,
                                        'cong_tac_co_dong' => 0, 'hoc' => 0, 'phep' => 0,
                                        'di_vien' => 0, 'cho_huu' => 0, 'om_tai_trai' => 0,
                                        'thai_san' => 0, 'kham_benh' => 0, 'khac' => 0
                                    ];
                                @endphp

                                @foreach($departments as $dept)
                                    @php
                                        $report = $reports->where('department_id', $dept->id)->first();
                                        
                                        // Get approved leaves for this department on this date
                                        $deptLeaves = $approvedLeaves->where('employee.department_id', $dept->id);
                                        
                                        // Map leave types to reason counts (matching EmployeeLeave leave types)
                                        $leaveTypeCounts = [
                                            'cong_tac_co_dong' => $deptLeaves->where('leave_type', 'business')->count() + $deptLeaves->where('leave_type', 'attendance')->count(),
                                            'hoc' => $deptLeaves->where('leave_type', 'study')->count(),
                                            'phep' => $deptLeaves->where('leave_type', 'leave')->count(),
                                            'di_vien' => $deptLeaves->where('leave_type', 'hospital')->count(),
                                            'cho_huu' => $deptLeaves->where('leave_type', 'pending')->count(),
                                            'om_tai_trai' => $deptLeaves->where('leave_type', 'sick')->count(),
                                            'thai_san' => $deptLeaves->where('leave_type', 'maternity')->count(),
                                            'kham_benh' => $deptLeaves->where('leave_type', 'checkup')->count(),
                                            'khac' => $deptLeaves->where('leave_type', 'other')->count(),
                                        ];
                                        
                                        $totalLeaveAbsent = $deptLeaves->count();
                                        
                                        // If report exists, use its data and merge with leave data
                                        if ($report) {
                                            $total = $report->total_employees;
                                            
                                            // Merge counts: report data + approved leaves
                                            // Note: Legacy report fields may not match new leave types exactly
                                            $congTacCoDong = ($report->sick_count ?? 0) + ($report->annual_leave_count ?? 0) + $leaveTypeCounts['cong_tac_co_dong'];
                                            $hoc = ($report->personal_leave_count ?? 0) + $leaveTypeCounts['hoc'];
                                            $phep = ($report->military_leave_count ?? 0) + $leaveTypeCounts['phep'];
                                            $diVien = $leaveTypeCounts['di_vien'];
                                            $choHuu = $leaveTypeCounts['cho_huu'];
                                            $omTaiTrai = $leaveTypeCounts['om_tai_trai'];
                                            $thaiSan = $leaveTypeCounts['thai_san'];
                                            $khamBenh = $leaveTypeCounts['kham_benh'];
                                            $khac = ($report->other_leave_count ?? 0) + $leaveTypeCounts['khac'];
                                            
                                            $absent = $report->absent_count + $totalLeaveAbsent;
                                            $present = $total - $absent;
                                        } else {
                                            // No report means all employees present EXCEPT those on approved leave
                                            $activeEmployeeCount = $dept->employees()->active()->count();
                                            $total = $activeEmployeeCount;
                                            $absent = $totalLeaveAbsent;
                                            $present = $activeEmployeeCount - $absent;
                                            
                                            $congTacCoDong = $leaveTypeCounts['cong_tac_co_dong'];
                                            $hoc = $leaveTypeCounts['hoc'];
                                            $phep = $leaveTypeCounts['phep'];
                                            $diVien = $leaveTypeCounts['di_vien'];
                                            $choHuu = $leaveTypeCounts['cho_huu'];
                                            $omTaiTrai = $leaveTypeCounts['om_tai_trai'];
                                            $thaiSan = $leaveTypeCounts['thai_san'];
                                            $khamBenh = $leaveTypeCounts['kham_benh'];
                                            $khac = $leaveTypeCounts['khac'];
                                        }
                                        
                                        // Add to grand total
                                        $grandTotal['total'] += $total;
                                        $grandTotal['present'] += $present;
                                        $grandTotal['absent'] += $absent;
                                        $grandTotal['cong_tac_co_dong'] += $congTacCoDong;
                                        $grandTotal['hoc'] += $hoc;
                                        $grandTotal['phep'] += $phep;
                                        $grandTotal['di_vien'] += $diVien;
                                        $grandTotal['cho_huu'] += $choHuu;
                                        $grandTotal['om_tai_trai'] += $omTaiTrai;
                                        $grandTotal['thai_san'] += $thaiSan;
                                        $grandTotal['kham_benh'] += $khamBenh;
                                        $grandTotal['khac'] += $khac;
                                    @endphp
                                    
                                    <tr class="{{ $report ? '' : '' }}">
                                        <td><strong>{{ $dept->name }}</strong></td>
                                        <td class="text-center">TỔNG</td>
                                        <td class="text-center">{{ $total }}</td>
                                        <td class="text-center bg-light-success">{{ $present }}</td>
                                        <td class="text-center bg-light-danger">{{ $absent }}</td>
                                        <td class="text-center">{{ $congTacCoDong }}</td>
                                        <td class="text-center">{{ $hoc }}</td>
                                        <td class="text-center">{{ $phep }}</td>
                                        <td class="text-center">{{ $diVien }}</td>
                                        <td class="text-center">{{ $choHuu }}</td>
                                        <td class="text-center">{{ $omTaiTrai }}</td>
                                        <td class="text-center">{{ $thaiSan }}</td>
                                        <td class="text-center">{{ $khamBenh }}</td>
                                        <td class="text-center">{{ $khac }}</td>
                                    </tr>
                                @endforeach

                                <!-- Grand Total Row -->
                                <tr class="table-warning font-weight-bold">
                                    <td><strong style="color: red;">Nhà máy A31</strong></td>
                                    <td class="text-center"><strong>TỔNG</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['total'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['present'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['absent'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['cong_tac_co_dong'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['hoc'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['phep'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['di_vien'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['cho_huu'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['om_tai_trai'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['thai_san'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['kham_benh'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['khac'] }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Absence Reasons Summary -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><strong>TỔNG HỢP CÁC LÝ DO VẮNG:</strong></h5>
                </div>
                <div class="card-body">
                    @php
                        $hasAbsentEmployees = false;
                        $reasonMap = [
                            'cong_tac_co_dong' => 'Công tác - Cơ động',
                            'cong_tac' => 'Công tác - Cơ động', // Legacy support
                            'co_dong' => 'Công tác - Cơ động', // Legacy support
                            'hoc' => 'Học',
                            'phep' => 'Phép - Tranh thủ',
                            'di_vien' => 'Đi viện',
                            'cho_huu' => 'Chờ hưu',
                            'om_tai_trai' => 'Ốm tại trại',
                            'thai_san' => 'Thai sản',
                            'kham_benh' => 'Khám bệnh',
                            'khac' => 'Khác'
                        ];
                    @endphp
                    
                    <table class="table table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="20%">Phòng ban</th>
                                <th width="25%">Họ tên</th>
                                <th width="15%">Lý do</th>
                                <th width="40%">Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                    @foreach($departments as $dept)
                        @php
                            $report = $reports->where('department_id', $dept->id)->first();
                            $allAbsentEmployees = [];
                            
                            // 1. Get absent from report
                            if ($report && $report->note) {
                                $decoded = json_decode($report->note, true);
                                if (is_array($decoded)) {
                                    $allAbsentEmployees = $decoded;
                                }
                            }
                            
                            // 2. Add approved leaves for this department
                            $deptLeaves = $approvedLeaves->filter(function($leave) use ($dept) {
                                return $leave->employee && $leave->employee->department_id == $dept->id;
                            });
                            
                            // Map leave type to reason
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
                            
                            foreach ($deptLeaves as $leave) {
                                // Check if not already in list (avoid duplicate)
                                $alreadyAdded = false;
                                foreach ($allAbsentEmployees as $absent) {
                                    if ($absent['employee_id'] == $leave->employee_id) {
                                        $alreadyAdded = true;
                                        break;
                                    }
                                }
                                
                                if (!$alreadyAdded) {
                                    $reason = $leaveTypeMap[$leave->leave_type] ?? 'khac';
                                    $allAbsentEmployees[] = [
                                        'employee_id' => $leave->employee_id,
                                        'reason' => $reason,
                                        'note' => $leave->note ?: '-' // Use note from leave request directly
                                    ];
                                }
                            }
                            
                            // Skip if no absent employees
                            if (count($allAbsentEmployees) == 0) continue;
                            
                            $hasAbsentEmployees = true;
                            $rowspan = count($allAbsentEmployees);
                        @endphp
                        
                        @foreach($allAbsentEmployees as $index => $absent)
                            @php
                                $employee = \Modules\OrganizationStructure\Models\Employee::find($absent['employee_id']);
                                if (!$employee) continue;
                                
                                $reasonText = $reasonMap[$absent['reason']] ?? 'Không rõ';
                                $note = $absent['note'] ?? '-';
                                
                                // Color coding for reasons
                                $reasonColors = [
                                    'cong_tac_co_dong' => 'primary',
                                    'cong_tac' => 'primary', // Legacy support
                                    'co_dong' => 'primary', // Legacy support
                                    'hoc' => 'success',
                                    'phep' => 'warning',
                                    'di_vien' => 'danger',
                                    'cho_huu' => 'secondary',
                                    'om_tai_trai' => 'dark',
                                    'thai_san' => 'info',
                                    'kham_benh' => 'primary',
                                    'khac' => 'secondary'
                                ];
                                // Map legacy reasons to new format for color
                                $reasonForColor = $absent['reason'];
                                if ($reasonForColor === 'cong_tac' || $reasonForColor === 'co_dong') {
                                    $reasonForColor = 'cong_tac_co_dong';
                                }
                                $colorClass = $reasonColors[$reasonForColor] ?? 'secondary';
                            @endphp
                            <tr>
                                @if($index == 0)
                                    <td rowspan="{{ $rowspan }}" class="align-middle bg-light">
                                        <strong>{{ $dept->name }}</strong>
                                    </td>
                                @endif
                                <td>{{ $employee->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $colorClass }}">{{ $reasonText }}</span>
                                </td>
                                <td>{{ $note }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                        </tbody>
                    </table>
                    
                    @if(!$hasAbsentEmployees)
                        <p class="text-dark mb-0"><em>Không có nhân viên vắng mặt.</em></p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<style>
@media print {
    .navbar, .sidebar, form {
        display: none !important;
    }
    
    .card {
        page-break-inside: avoid;
        border: none;
        box-shadow: none;
    }
}

.bg-light-success {
    background-color: #d4edda !important;
}

.bg-light-danger {
    background-color: #f8d7da !important;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid #333 !important;
    vertical-align: middle !important;
}

/* ============================================
   CHUẨN HÓA FONT SIZE CHO TRANG SUMMARY
   ============================================ */

/* Header gradient - đồng nhất cho cả 2 trang */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* Đảm bảo text trong header luôn màu trắng - CSS mạnh nhất */
.card.bg-gradient-primary,
.card.bg-gradient-primary *,
.card.bg-gradient-primary h3,
.card.bg-gradient-primary h4,
.card.bg-gradient-primary h5,
.card.bg-gradient-primary p,
.card.bg-gradient-primary strong,
.card.bg-gradient-primary i,
.card.bg-gradient-primary .card-body,
.card.bg-gradient-primary .card-body *,
.card.bg-gradient-primary .card-body h3,
.card.bg-gradient-primary .card-body h5,
.card.bg-gradient-primary .card-body strong {
    color: #ffffff !important;
}

/* Header chính */
h3 {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
}

h5 {
    font-size: 1.1rem !important;
    font-weight: 500 !important;
}

/* Labels */
.form-label,
label {
    font-size: 0.95rem !important;
    font-weight: 600 !important;
}

/* Form controls */
.form-control {
    font-size: 0.95rem !important;
}

/* Table headers */
.table thead th {
    font-weight: 600 !important;
    font-size: 0.9rem !important;
}

/* Table cells */
.table tbody td {
    font-size: 0.9rem !important;
    color: #000 !important;
}

/* Badge */
.badge-lg {
    font-size: 1rem !important;
}

/* Card headers */
.card-header h5 {
    font-size: 1.1rem !important;
    font-weight: 600 !important;
}

/* Buttons */
.btn {
    font-size: 0.95rem !important;
}

/* Đảm bảo text trong nút btn-primary màu trắng */
.btn-primary,
.btn-primary *,
.btn-primary i,
.btn-primary span {
    color: #ffffff !important;
}

/* Force all text to black */
.table th,
.table td,
.table tbody td,
.table thead th,
.table tbody tr td,
.card-body,
.card-body *,
.card-body p,
.card-body strong,
.card-body em,
.text-muted,
label,
.form-label {
    color: #000 !important;
}

/* Override Bootstrap text-muted */
.text-muted,
.text-muted * {
    color: #000 !important;
}

/* Table specific */
table.table td,
table.table th {
    color: #000 !important;
}

table.table tbody tr td,
table.table tbody tr td strong {
    color: #000 !important;
}

/* Ensure all text in cards is black */
.card .card-body,
.card .card-body *,
.card .card-body p,
.card .card-body strong,
.card .card-body td,
.card .card-body th {
    color: #000 !important;
}
</style>
@endsection



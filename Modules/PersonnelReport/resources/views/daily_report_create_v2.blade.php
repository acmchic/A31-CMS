@extends(backpack_view('blank'))

@php
    // Ensure $absentEmployees is always an array
    $absentEmployees = is_array($absentEmployees ?? null) ? $absentEmployees : [];
    $absentCount = count($absentEmployees);
    $totalEmployees = $employees->count();
    $presentCount = $totalEmployees - $absentCount;
    
    // Kiểm tra chế độ read-only (ngày quá khứ)
    $isReadOnly = $isReadOnly ?? false;
    
    // Tính danh sách nhân viên có mặt (loại trừ nhân viên vắng mặt)
    $absentEmployeeIds = array_column($absentEmployees, 'employee_id');
    $presentEmployees = $employees->filter(function($employee) use ($absentEmployeeIds) {
        return !in_array($employee->id, $absentEmployeeIds);
    });
    
    // Debug
    \Log::info('Create-2 View Data', [
        'absentEmployees_count' => count($absentEmployees),
        'absentEmployees' => $absentEmployees,
        'isReadOnly' => $isReadOnly,
        'presentEmployees_count' => $presentEmployees->count()
    ]);
@endphp

@section('content')
<div class="container-fluid" id="smart-report-app">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;">
                <div class="card-body text-center py-4" style="color: #ffffff !important;">
                    <h3 class="mb-2" style="color: #ffffff !important;"><i class="la la-calendar-check" style="color: #ffffff !important;"></i> BÁO CÁO QUÂN SỐ HÀNG NGÀY</h3>
                    <h5 class="mb-0" style="color: #ffffff !important;"><strong style="color: #ffffff !important;">{{ $department->name }}</strong></h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Selection -->
    <div class="row mb-4">
        <div class="col-md-4 offset-md-4">
            <label class="form-label"><strong>Chọn ngày báo cáo:</strong></label>
            <div class="input-group">
                <input type="date" id="report-date-input" class="form-control" value="{{ $reportDate }}">
                <button type="button" class="btn btn-primary" onclick="changeDate()" style="color: #ffffff !important;">
                    <i class="la la-search" style="color: #ffffff !important;"></i> Xem
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center bg-light">
                    <h6 class="text-dark mb-2">TỔNG QUÂN SỐ</h6>
                    <h2 class="mb-0 text-dark" id="stat-total">{{ $totalEmployees }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center" style="background-color: #d4edda;">
                    <h6 class="text-dark mb-2">CÓ MẶT</h6>
                    <h2 class="mb-0 text-success" id="stat-present">{{ $presentCount }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center" style="background-color: #f8d7da;">
                    <h6 class="text-dark mb-2">VẮNG MẶT</h6>
                    <h2 class="mb-0 text-danger" id="stat-absent">{{ $absentCount }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Reason Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><strong>Chi tiết lý do vắng mặt:</strong></h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col">
                            <div class="badge badge-info badge-lg p-2">
                                Công tác: <strong id="reason-cong-tac">0</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="badge badge-primary badge-lg p-2">
                                Cơ động: <strong id="reason-co-dong">0</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="badge badge-success badge-lg p-2">
                                Học: <strong id="reason-hoc">0</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="badge badge-warning badge-lg p-2">
                                Phép: <strong id="reason-phep">0</strong>
                            </div>
                        </div>
                        <div class="col">
                            <div class="badge badge-secondary badge-lg p-2">
                                Khác: <strong id="reason-khac">0</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$isReadOnly)
    <!-- Add Absent Employee Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="la la-user-plus"></i> <strong>Thêm nhân viên vắng mặt</strong></h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label><strong>Chọn nhân viên</strong></label>
                                <select id="select-employee" class="form-control">
                                    <option value="">-- Chọn nhân viên --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                                data-name="{{ $emp->name }}"
                                                data-position="{{ $emp->position->name ?? '-' }}">
                                            {{ $emp->name }} - {{ $emp->position->name ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label><strong>Lý do vắng mặt</strong></label>
                                <select id="select-reason" class="form-control">
                                    <option value="">-- Chọn lý do --</option>
                                    <option value="cong_tac">Công tác</option>
                                    <option value="co_dong">Cơ động</option>
                                    <option value="hoc">Học</option>
                                    <option value="phep">Phép</option>
                                    <option value="khac">Khác</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label><strong>Ghi chú</strong></label>
                                <input type="text" id="input-note" class="form-control" placeholder="Ví dụ: Đi công tác...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group mb-0">
                                <button type="button" class="btn btn-primary btn-block" onclick="addAbsentEmployee()">
                                    <i class="la la-plus"></i> Thêm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Absent Employees List -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><strong>Danh sách nhân viên vắng mặt</strong></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="absent-table">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Họ tên</th>
                                    <th width="15%">Chức vụ</th>
                                    <th width="15%">Lý do</th>
                                    <th width="30%">Ghi chú</th>
                                    @if(!$isReadOnly)
                                    <th width="10%">Thao tác</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="absent-tbody">
                                @if($absentCount == 0)
                                <tr id="no-data-row">
                                    <td colspan="{{ $isReadOnly ? '5' : '6' }}" class="text-center text-dark">
                                        <i class="la la-inbox la-3x"></i>
                                        <p class="mb-0">Chưa có nhân viên vắng mặt</p>
                                    </td>
                                </tr>
                                @else
                                    @foreach($absentEmployees as $index => $absent)
                                        @php
                                            $employee = $employees->firstWhere('id', $absent['employee_id']);
                                            if (!$employee) continue;

                                            $reasonMap = [
                                                'cong_tac' => 'Công tác',
                                                'co_dong' => 'Cơ động',
                                                'hoc' => 'Học',
                                                'phep' => 'Phép',
                                                'khac' => 'Khác'
                                            ];
                                            $reasonText = $reasonMap[$absent['reason']] ?? 'Không rõ';
                                        @endphp
                                        <tr data-employee-id="{{ $employee->id }}" data-reason="{{ $absent['reason'] }}" data-note="{{ $absent['note'] ?? '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $employee->name }}</strong></td>
                                            <td>{{ $employee->position->name ?? '-' }}</td>
                                            <td><span class="badge badge-info">{{ $reasonText }}</span></td>
                                            <td>{{ $absent['note'] ?? '-' }}</td>
                                            @if(!$isReadOnly)
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeAbsent(this)">
                                                    <i class="la la-trash"></i>
                                                </button>
                                            </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!$isReadOnly)
    <!-- Submit Form -->
    <form method="POST" action="{{ route('daily-personnel-report.store-2') }}" id="report-form">
        @csrf
        <input type="hidden" name="department_id" value="{{ $department->id }}">
        <input type="hidden" name="report_date" id="hidden-report-date" value="{{ $reportDate }}">
        <input type="hidden" name="absent_employees" id="hidden-absent-employees" value="">

        <div class="row mb-4">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="la la-save"></i> {{ $existingReport ? 'Cập nhật báo cáo' : 'Lưu báo cáo' }}
                </button>
            </div>
        </div>
    </form>
    @endif

    <!-- Present Employees List -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><strong>Danh sách nhân viên có mặt</strong></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="present-table">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">Họ tên</th>
                                    <th width="20%">Chức vụ</th>
                                    <th width="15%">Cấp bậc</th>
                                    <th width="30%">Phòng ban</th>
                                </tr>
                            </thead>
                            <tbody id="present-tbody">
                                @if($presentEmployees->count() == 0)
                                <tr id="no-present-data-row">
                                    <td colspan="5" class="text-center text-dark">
                                        <i class="la la-inbox la-3x"></i>
                                        <p class="mb-0">Không có nhân viên có mặt</p>
                                    </td>
                                </tr>
                                @else
                                    @foreach($presentEmployees as $index => $employee)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $employee->name }}</strong></td>
                                            <td>{{ $employee->position->name ?? '-' }}</td>
                                            <td>{{ $employee->rank_code ?? '-' }}</td>
                                            <td>{{ $employee->department->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal removed - using separate page instead -->

<style>
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

.card {
    border-radius: 10px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175) !important;
}

.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
}

.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem !important;
}

/* Smooth table row hover without transform */
#absent-table tbody tr {
    transition: background-color 0.2s ease;
}

#absent-table tbody tr:hover {
    background-color: #f0f7ff;
}

/* Smooth button hover - only for large buttons */
.btn-lg {
    transition: all 0.2s ease;
}

.btn-lg:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,.15);
}

.btn-lg:active {
    transform: translateY(0);
}

/* No hover effect for add button */
.card-header .btn {
    transition: none;
}

.card-header .btn:hover {
    transform: none;
    box-shadow: none;
}

/* No custom modal CSS - use Backpack/Bootstrap default */

/* Table action buttons */
.btn-sm {
    transition: all 0.15s ease;
}

.btn-sm:hover {
    transform: scale(1.1);
}

.btn-sm:active {
    transform: scale(0.95);
}

.la-3x {
    font-size: 3rem;
}

/* Fade in animation for new rows */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeInUp 0.3s ease-out;
}

/* Smooth scroll */
html {
    scroll-behavior: smooth;
}

/* Remove outline on focus for better UX */
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3 0%, #64408b 100%);
}

/* Statistics cards hover effect */
.row .col-md-3 .card {
    cursor: default;
}

.row .col-md-3 .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
}

/* Select2 styling improvements */
.select2-container--default .select2-selection--single {
    height: 38px !important;
    border-color: #ced4da;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}

/* Select2 dropdown z-index - must be above modal */
.select2-container {
    z-index: 9999 !important;
}

.select2-dropdown {
    z-index: 9999 !important;
}

.select2-container--default .select2-selection--single {
    height: 38px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
}

/* ============================================
   CHUẨN HÓA FONT SIZE CHO TRANG CREATE-2
   ============================================ */

/* Header chính - đồng nhất với summary page */
.container-fluid h3,
.container-fluid h4 {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
}

.container-fluid h5 {
    font-size: 1.1rem !important;
    font-weight: 500 !important;
}

/* Card headers - đồng nhất tất cả */
.card-header h5,
.card-header h6 {
    font-size: 1.1rem !important;
    font-weight: 600 !important;
}

/* Statistics cards labels */
.card-body h6.text-dark {
    font-size: 0.95rem !important;
    font-weight: 600 !important;
}

/* Statistics numbers */
.card-body h2 {
    font-size: 2rem !important;
    font-weight: 700 !important;
}

/* Labels trong form */
.card-body label,
.card-body label strong {
    font-size: 0.95rem !important;
    font-weight: 600 !important;
}

/* Form controls - đồng nhất */
.card-body .form-control,
.card-body select,
.card-body input,
#report-date-input {
    font-size: 0.95rem !important;
    padding: 0.5rem 0.75rem !important;
}

/* Badge trong "Chi tiết lý do vắng mặt" */
.badge-lg {
    font-size: 0.95rem !important;
    padding: 0.5rem 1rem !important;
}

.badge-lg strong {
    font-size: 1.1rem !important;
    font-weight: 700 !important;
}

/* Table headers */
.table thead th {
    font-size: 0.9rem !important;
    font-weight: 600 !important;
}

/* Table cells */
.table tbody td {
    font-size: 0.9rem !important;
}

/* Buttons */
.btn {
    font-size: 0.95rem !important;
}

.btn-lg {
    font-size: 1rem !important;
    padding: 0.6rem 1.5rem !important;
}

/* Đảm bảo text trong nút btn-primary màu trắng */
.btn-primary,
.btn-primary *,
.btn-primary i,
.btn-primary span {
    color: #ffffff !important;
}
</style>

<script>
// Store employees data
const employeesData = @json($employees);
const departmentId = {{ $department->id }};

// Initialize absent employees from backend
let absentEmployees = @json($absentEmployees);

// Check if read-only mode (past date)
const isReadOnly = {{ $isReadOnly ? 'true' : 'false' }};

document.addEventListener('DOMContentLoaded', function() {
    // Update statistics on load
    updateStatistics();
    updateReasonBreakdown();
    updatePresentEmployeesTable();
    
    // Disable form inputs if read-only
    if (isReadOnly) {
        const dateInput = document.getElementById('report-date-input');
        if (dateInput) {
            dateInput.disabled = false; // Allow changing date to view other reports
        }
    }
});

function changeDate() {
    const date = document.getElementById('report-date-input').value;
    if (date) {
        window.location.href = '{{ backpack_url("daily-personnel-report/create-2") }}?report_date=' + date;
    }
}

function addAbsentEmployee() {
    // Prevent adding if read-only
    if (isReadOnly) {
        alert('Không thể thêm nhân viên vắng mặt cho báo cáo quá khứ.');
        return;
    }
    
    const employeeSelect = document.getElementById('select-employee');
    const reasonSelect = document.getElementById('select-reason');
    const noteInput = document.getElementById('input-note');

    const employeeId = employeeSelect.value;
    const reason = reasonSelect.value;
    const note = noteInput.value;

    // Validate
    if (!employeeId) {
        alert('Vui lòng chọn nhân viên');
        return;
    }

    if (!reason) {
        alert('Vui lòng chọn lý do vắng mặt');
        return;
    }

    // Check if employee already in list
    const exists = absentEmployees.find(emp => emp.employee_id == employeeId);
    if (exists) {
        alert('Nhân viên này đã có trong danh sách vắng mặt!');
        return;
    }

    // Find employee data
    const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
    const employeeName = selectedOption.getAttribute('data-name');
    const employeePosition = selectedOption.getAttribute('data-position');

    const employee = {
        id: employeeId,
        name: employeeName,
        position: { name: employeePosition }
    };

    // Add to array
    absentEmployees.push({
        employee_id: employeeId,
        reason: reason,
        note: note
    });

    // Add to table
    addRowToTable(employee, reason, note);

    // Update statistics
    updateStatistics();
    updateReasonBreakdown();
    updatePresentEmployeesTable();

    // Reset form
    employeeSelect.value = '';
    reasonSelect.value = '';
    noteInput.value = '';
}

function addRowToTable(employee, reason, note) {
    const tbody = document.getElementById('absent-tbody');

    // Remove "no data" row if exists
    const noDataRow = document.getElementById('no-data-row');
    if (noDataRow) {
        noDataRow.remove();
    }

    // Reason map
    const reasonMap = {
        'cong_tac': 'Công tác',
        'co_dong': 'Cơ động',
        'hoc': 'Học',
        'phep': 'Phép',
        'khac': 'Khác'
    };

    const reasonText = reasonMap[reason] || 'Không rõ';
    const rowNumber = tbody.children.length + 1;

    const row = document.createElement('tr');
    row.className = 'fade-in';
    row.setAttribute('data-employee-id', employee.id);
    row.setAttribute('data-reason', reason);
    row.setAttribute('data-note', note);

    const actionColumn = isReadOnly ? '' : `
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeAbsent(this)">
                <i class="la la-trash"></i>
            </button>
        </td>
    `;
    
    row.innerHTML = `
        <td>${rowNumber}</td>
        <td><strong>${employee.name}</strong></td>
        <td>${employee.position ? employee.position.name : '-'}</td>
        <td><span class="badge badge-info">${reasonText}</span></td>
        <td>${note || '-'}</td>
        ${actionColumn}
    `;

    tbody.appendChild(row);
}

function removeAbsent(button) {
    // Prevent removing if read-only
    if (isReadOnly) {
        alert('Không thể xóa nhân viên vắng mặt từ báo cáo quá khứ.');
        return;
    }
    
    if (!confirm('Bạn có chắc muốn xóa nhân viên này khỏi danh sách vắng mặt?')) {
        return;
    }

    const row = button.closest('tr');
    const employeeId = row.getAttribute('data-employee-id');

    // Remove from array
    absentEmployees = absentEmployees.filter(emp => emp.employee_id != employeeId);

    // Remove row
    row.remove();

    // Update row numbers
    const tbody = document.getElementById('absent-tbody');
    const rows = tbody.querySelectorAll('tr:not(#no-data-row)');
    rows.forEach((r, index) => {
        r.querySelector('td:first-child').textContent = index + 1;
    });

    // If no rows left, show "no data" message
    if (rows.length === 0) {
        const colspan = isReadOnly ? 5 : 6;
        tbody.innerHTML = `
            <tr id="no-data-row">
                <td colspan="${colspan}" class="text-center text-dark">
                    <i class="la la-inbox la-3x"></i>
                    <p class="mb-0">Chưa có nhân viên vắng mặt</p>
                </td>
            </tr>
        `;
    }

    // Update statistics
    updateStatistics();
    updateReasonBreakdown();
    updatePresentEmployeesTable();
}

function updateStatistics() {
    const totalEmployees = employeesData.length;
    const absentCount = absentEmployees.length;
    const presentCount = totalEmployees - absentCount;

    document.getElementById('stat-total').textContent = totalEmployees;
    document.getElementById('stat-present').textContent = presentCount;
    document.getElementById('stat-absent').textContent = absentCount;
}

function updateReasonBreakdown() {
    const counts = {
        'cong_tac': 0,
        'co_dong': 0,
        'hoc': 0,
        'phep': 0,
        'khac': 0
    };

    absentEmployees.forEach(emp => {
        if (counts.hasOwnProperty(emp.reason)) {
            counts[emp.reason]++;
        }
    });

    document.getElementById('reason-cong-tac').textContent = counts['cong_tac'];
    document.getElementById('reason-co-dong').textContent = counts['co_dong'];
    document.getElementById('reason-hoc').textContent = counts['hoc'];
    document.getElementById('reason-phep').textContent = counts['phep'];
    document.getElementById('reason-khac').textContent = counts['khac'];
}

function updatePresentEmployeesTable() {
    const presentTbody = document.getElementById('present-tbody');
    if (!presentTbody) return;
    
    // Get absent employee IDs
    const absentEmployeeIds = absentEmployees.map(emp => parseInt(emp.employee_id));
    
    // Filter present employees (not in absent list)
    const presentEmployeesList = employeesData.filter(emp => !absentEmployeeIds.includes(emp.id));
    
    // Clear existing rows
    presentTbody.innerHTML = '';
    
    if (presentEmployeesList.length === 0) {
        presentTbody.innerHTML = `
            <tr id="no-present-data-row">
                <td colspan="5" class="text-center text-dark">
                    <i class="la la-inbox la-3x"></i>
                    <p class="mb-0">Không có nhân viên có mặt</p>
                </td>
            </tr>
        `;
        return;
    }
    
    // Add present employees rows
    presentEmployeesList.forEach((employee, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td><strong>${employee.name}</strong></td>
            <td>${employee.position ? employee.position.name : '-'}</td>
            <td>${employee.rank_code || '-'}</td>
            <td>${employee.department ? employee.department.name : '-'}</td>
        `;
        presentTbody.appendChild(row);
    });
}

// Before form submit, update hidden field
const reportForm = document.getElementById('report-form');
if (reportForm) {
    reportForm.addEventListener('submit', function(e) {
        // Prevent submit if read-only
        if (isReadOnly) {
            e.preventDefault();
            alert('Không thể lưu báo cáo quá khứ.');
            return false;
        }
        
        document.getElementById('hidden-absent-employees').value = JSON.stringify(absentEmployees);
        document.getElementById('hidden-report-date').value = document.getElementById('report-date-input').value;
    });
}
</script>
@endsection

@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-2">
                    <h4 class="mb-0">
                        PHẦN MỀM BÁO CÁO QUÂN SỐ HÀNG NGÀY
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Title Section -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-warning">
                <div class="card-body text-center py-2">
                    <h5 class="mb-0">📊 SỔ TỔNG HỢP QUÂN SỐ TOÀN NHÀ MÁY</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Selection -->
    <div class="row mb-3">
        <div class="col-md-4 offset-md-4">
            <form method="GET" action="{{ backpack_url('daily-personnel-report') }}" class="form-inline">
                <label class="mr-2"><strong>Chọn ngày xem:</strong></label>
                <div class="input-group">
                    <input type="date" name="report_date" class="form-control" value="{{ $selectedDate }}" required>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-info">
                            <i class="la la-search"></i> Xem
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Title -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="alert alert-danger">
                <strong>⭐ Bảng tổng hợp quân số Nhà máy A31 ngày {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}:</strong>
            </div>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-light">
                                <tr class="text-center">
                                    <th style="background-color: #f5e6d3;">ĐƠN VỊ</th>
                                    <th style="background-color: #f5e6d3;">PHÂN LOẠI</th>
                                    <th style="background-color: #f5e6d3;">TỔNG QS</th>
                                    <th style="background-color: #c8e6c9;">CÓ MẶT</th>
                                    <th style="background-color: #ffccbc;">VẮNG MẶT</th>
                                    <th style="background-color: #d4e6f1;">CÔNG TÁC</th>
                                    <th style="background-color: #d4e6f1;">CƠ ĐỘNG</th>
                                    <th style="background-color: #d4e6f1;">HỌC</th>
                                    <th style="background-color: #d4e6f1;">PHÉP</th>
                                    <th style="background-color: #d4e6f1;">KHÁC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grandTotal = [
                                        'total' => 0, 'present' => 0, 'absent' => 0,
                                        'cong_tac' => 0, 'co_dong' => 0, 'hoc' => 0, 'phep' => 0, 'khac' => 0
                                    ];
                                @endphp

                                @foreach($departments as $dept)
                                    @php
                                        $report = $reports->where('department_id', $dept->id)->first();
                                        $total = $report ? $report->total_employees : 0;
                                        $present = $report ? $report->present_count : 0;
                                        $absent = $report ? $report->absent_count : 0;
                                        $congTac = $report ? $report->sick_count : 0;
                                        $coDong = $report ? $report->annual_leave_count : 0;
                                        $hoc = $report ? $report->personal_leave_count : 0;
                                        $phep = $report ? $report->military_leave_count : 0;
                                        $khac = $report ? $report->other_leave_count : 0;
                                        
                                        // Add to grand total
                                        $grandTotal['total'] += $total;
                                        $grandTotal['present'] += $present;
                                        $grandTotal['absent'] += $absent;
                                        $grandTotal['cong_tac'] += $congTac;
                                        $grandTotal['co_dong'] += $coDong;
                                        $grandTotal['hoc'] += $hoc;
                                        $grandTotal['phep'] += $phep;
                                        $grandTotal['khac'] += $khac;
                                    @endphp
                                    
                                    <tr class="{{ $report ? '' : 'text-muted' }}">
                                        <td><strong>{{ $dept->name }}</strong></td>
                                        <td class="text-center">TỔNG</td>
                                        <td class="text-center">{{ $total }}</td>
                                        <td class="text-center bg-light-success">{{ $present }}</td>
                                        <td class="text-center bg-light-danger">{{ $absent }}</td>
                                        <td class="text-center">{{ $congTac }}</td>
                                        <td class="text-center">{{ $coDong }}</td>
                                        <td class="text-center">{{ $hoc }}</td>
                                        <td class="text-center">{{ $phep }}</td>
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
                                    <td class="text-center"><strong>{{ $grandTotal['cong_tac'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['co_dong'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['hoc'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $grandTotal['phep'] }}</strong></td>
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
                    <ol class="mb-0">
                        @foreach($departments as $dept)
                            @php
                                $report = $reports->where('department_id', $dept->id)->first();
                            @endphp
                            @if($report && $report->note)
                                <li><strong>{{ $dept->name }}:</strong> {{ $report->note }}</li>
                            @endif
                        @endforeach
                    </ol>
                    
                    @if($reports->where('note', '!=', null)->count() == 0)
                        <p class="text-muted mb-0"><em>Không có ghi chú nào.</em></p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-3">
        <div class="col-12 text-center">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="la la-print"></i> In báo cáo
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, .sidebar, form {
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

.table thead th {
    font-weight: bold;
    font-size: 14px;
}

.table tbody td {
    font-size: 13px;
}
</style>
@endsection



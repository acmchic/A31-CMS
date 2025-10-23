@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-2">
                    <h5 class="mb-0 text-uppercase">
                        📊 Báo cáo quân số hàng ngày | 📋 Sổ tổng hợp quân số đơn vị | 📝 Sổ tổng hợp đăng ký phép
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Phần xuống 4 báo cáo -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-danger mb-0">
                <strong>Phần xuống 4 báo cáo quân số ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}:</strong>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- CỘT TRÁI -->
        <div class="col-md-6">
            <!-- 1. TỔNG QUÂN SỐ -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><strong>1. TỔNG QUÂN SỐ:</strong></h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small mb-1">Tổng quân số SQ:</label>
                            <input type="text" class="form-control" value="{{ $stats['total_sq'] ?? 'Điền tổng số SQ Quân' }}" readonly>
                        </div>
                        <div class="col-6"></div>
                        
                        <div class="col-6">
                            <label class="form-label small mb-1">Tổng quân số QNCN:</label>
                            <input type="text" class="form-control" value="{{ $stats['total_qncn'] ?? 'Điền tổng số QNCN' }}" readonly>
                        </div>
                        <div class="col-6"></div>
                        
                        <div class="col-6">
                            <label class="form-label small mb-1">Tổng quân số CNQP:</label>
                            <input type="text" class="form-control" value="{{ $stats['total_cnqp'] ?? 'Điền tổng số CNQP' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. QUÂN SỐ CÓ MẶT -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><strong>2. QUÂN SỐ CÓ MẶT:</strong></h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label small mb-1">Quân số SQ có mặt:</label>
                            <input type="text" class="form-control" value="{{ $stats['present_sq'] ?? 'Điền số SQ Quân có mặt' }}" readonly>
                        </div>
                        <div class="col-4"></div>
                        <div class="col-4"></div>
                        
                        <div class="col-4">
                            <label class="form-label small mb-1">Quân số QNCN có mặt:</label>
                            <input type="text" class="form-control" value="{{ $stats['present_qncn'] ?? 'Điền số QNCN có mặt' }}" readonly>
                        </div>
                        <div class="col-4"></div>
                        <div class="col-4"></div>
                        
                        <div class="col-4">
                            <label class="form-label small mb-1">Quân số CNQP có mặt:</label>
                            <input type="text" class="form-control" value="{{ $stats['present_cnqp'] ?? 'Điền số CNQP có mặt' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. QUÂN SỐ VẮNG MẶT -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><strong>3. QUÂN SỐ VẮNG MẶT:</strong></h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label small mb-1">Quân số SQ vắng mặt:</label>
                            <input type="text" class="form-control" value="{{ $stats['absent_sq'] ?? 'Điền số SQ Quân vắng mặt' }}" readonly>
                        </div>
                        <div class="col-4"></div>
                        <div class="col-4"></div>
                        
                        <div class="col-4">
                            <label class="form-label small mb-1">Quân số QNCN vắng mặt:</label>
                            <input type="text" class="form-control" value="{{ $stats['absent_qncn'] ?? 'Điền số QNCN vắng mặt' }}" readonly>
                        </div>
                        <div class="col-4"></div>
                        <div class="col-4"></div>
                        
                        <div class="col-4">
                            <label class="form-label small mb-1">Quân số CNQP vắng mặt:</label>
                            <input type="text" class="form-control" value="{{ $stats['present_cnqp'] ?? 'Điền số CNQP vắng mặt' }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI -->
        <div class="col-md-6">
            <!-- 4. LÝ DO VẮNG MẶT -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><strong>4. LÝ DO VẮNG MẶT:</strong></h6>
                </div>
                <div class="card-body">
                    <!-- 4.1 Quân số công tác -->
                    <div class="mb-3">
                        <h6 class="text-primary mb-2"><em>4.1. Quân số công tác:</em></h6>
                        <div class="row g-2">
                            <div class="col-2"><label class="form-label small mb-1">SQ:</label></div>
                            <div class="col-3"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                            <div class="col-2"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                            <div class="col-1"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                        </div>
                    </div>

                    <!-- 4.2 Quân số có đông -->
                    <div class="mb-3">
                        <h6 class="text-primary mb-2"><em>4.2. Quân số có đông:</em></h6>
                        <div class="row g-2">
                            <div class="col-2"><label class="form-label small mb-1">SQ:</label></div>
                            <div class="col-3"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                            <div class="col-2"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                            <div class="col-1"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                        </div>
                    </div>

                    <!-- 4.3 Quân số đi học -->
                    <div class="mb-3">
                        <h6 class="text-primary mb-2"><em>4.3. Quân số đi học:</em></h6>
                        <div class="row g-2">
                            <div class="col-2"><label class="form-label small mb-1">SQ:</label></div>
                            <div class="col-3"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                            <div class="col-2"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                            <div class="col-1"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                        </div>
                    </div>

                    <!-- 4.4 Quân số đi Phép -->
                    <div class="mb-3">
                        <h6 class="text-primary mb-2"><em>4.4. Quân số đi Phép:</em></h6>
                        <div class="row g-2">
                            <div class="col-2"><label class="form-label small mb-1">SQ:</label></div>
                            <div class="col-3"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                            <div class="col-2"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                            <div class="col-1"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                        </div>
                    </div>

                    <!-- 4.5 Lý do khác -->
                    <div class="mb-3">
                        <h6 class="text-primary mb-2"><em>4.5. Lý do khác:</em></h6>
                        <div class="row g-2">
                            <div class="col-2"><label class="form-label small mb-1">SQ:</label></div>
                            <div class="col-3"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                            <div class="col-2"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                            <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                            <div class="col-1"><input type="text" class="form-control form-control-sm" value="0" readonly></div>
                        </div>
                    </div>

                    <!-- Ghi chú -->
                    <div>
                        <label class="form-label small mb-1"><strong>Ghi chú:</strong></label>
                        <textarea class="form-control" rows="3" readonly>{{ $report->note ?? 'Nguyễn Văn A: Công tác tại CKTĐ ĐT Đức (R. C. D)- QĐ đồng tài CSĐT ngày 10/03/24; Nguyễn Văn D: Phượng Nguyễn Văn D: ốm, thai sản, etc, nằm.' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Button -->
    <div class="row">
        <div class="col-12 text-center">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="la la-arrow-left"></i> Quay lại
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="la la-print"></i> In báo cáo
            </button>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .navbar, .sidebar {
            display: none !important;
        }
        
        .card {
            page-break-inside: avoid;
        }
    }
    
    .form-control-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }
    
    .form-label.small {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    
    .card-header {
        padding: 0.5rem 1rem;
    }
    
    h6 em {
        font-weight: normal;
    }
</style>
@endsection















@extends(backpack_view('blank'))

@php
    // Prepare data from existing report if available
    $formData = [];
    if (isset($existingReport) && $existingReport) {
        // Since we don't store breakdown by SQ/QNCN/CNQP, we'll put all in the first category
        // User can adjust manually if needed
        $formData = [
            'total_sq' => $existingReport->total_employees ?? 0,
            'total_qncn' => 0,
            'total_cnqp' => 0,
            'present_sq' => $existingReport->present_count ?? 0,
            'present_qncn' => 0,
            'present_cnqp' => 0,
            'absent_sq' => $existingReport->absent_count ?? 0,
            'absent_qncn' => 0,
            'absent_cnqp' => 0,
            'cong_tac_sq' => $existingReport->sick_count ?? 0,
            'cong_tac_qncn' => 0,
            'cong_tac_cnqp' => 0,
            'co_dong_sq' => $existingReport->annual_leave_count ?? 0,
            'co_dong_qncn' => 0,
            'co_dong_cnqp' => 0,
            'di_hoc_sq' => $existingReport->personal_leave_count ?? 0,
            'di_hoc_qncn' => 0,
            'di_hoc_cnqp' => 0,
            'di_phep_sq' => $existingReport->military_leave_count ?? 0,
            'di_phep_qncn' => 0,
            'di_phep_cnqp' => 0,
            'ly_do_khac_sq' => $existingReport->other_leave_count ?? 0,
            'ly_do_khac_qncn' => 0,
            'ly_do_khac_cnqp' => 0,
            'note' => $existingReport->note ?? '',
        ];
    }
    
    // Helper function to get value with fallback
    function getFieldValue($field, $formData, $default = '') {
        return old($field) ?? ($formData[$field] ?? $default);
    }
@endphp

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-2">
                    <h5 class="mb-0">
                        📊 BÁO CÁO QUÂN SỐ HÀNG NGÀY | 📋 SỔ TỔNG HỢP QUÂN SỐ ĐƠN VỊ | 📝 SỔ TỔNG HỢP ĐĂNG KÝ PHÉP
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ backpack_url('daily-personnel-report') }}">
        @csrf

        @if(isset($existingReport) && $existingReport)
            <!-- Show alert that we're editing -->
            <div class="alert alert-info">
                <i class="la la-info-circle"></i> <strong>Đang chỉnh sửa báo cáo ngày {{ \Carbon\Carbon::parse($existingReport->report_date)->format('d/m/Y') }}</strong> - Bạn có thể cập nhật thông tin và submit lại.
            </div>
        @endif

        <!-- Basic Info -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Phòng ban <span class="text-danger">*</span></label>
                <select name="department_id" id="department-select" class="form-control" required {{ isset($userDepartmentId) && $userDepartmentId ? 'readonly disabled' : '' }}>
                    @if(isset($departmentOptions) && count($departmentOptions) == 1)
                        @foreach($departmentOptions as $id => $name)
                            <option value="{{ $id }}" selected>{{ $name }}</option>
                        @endforeach
                    @else
                        <option value="">- Chọn phòng ban -</option>
                        @foreach($departmentOptions ?? [] as $id => $name)
                            <option value="{{ $id }}" {{ isset($existingReport) && $existingReport && $existingReport->department_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    @endif
                </select>
                @if(isset($userDepartmentId) && $userDepartmentId)
                    <input type="hidden" name="department_id" value="{{ $userDepartmentId }}">
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label">Ngày báo cáo <span class="text-danger">*</span></label>
                <input type="date" name="report_date" id="report-date" class="form-control" value="{{ $reportDate ?? now()->format('Y-m-d') }}" required>
            </div>
        </div>

        <div class="row">
            <!-- CỘT TRÁI -->
            <div class="col-md-6">
                <!-- 1. TỔNG QUÂN SỐ -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><strong>1. TỔNG QUÂN SỐ:</strong></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Tổng quân số SQ:</label>
                                <input type="number" name="total_sq" class="form-control" placeholder="Điền tổng số SQ Quân" min="0" value="{{ getFieldValue('total_sq', $formData, 0) }}">
                            </div>
                            <div class="col-6"></div>

                            <div class="col-6">
                                <label class="form-label small mb-1">Tổng quân số QNCN:</label>
                                <input type="number" name="total_qncn" class="form-control" placeholder="Điền tổng số QNCN" min="0" value="{{ getFieldValue('total_qncn', $formData, 0) }}">
                            </div>
                            <div class="col-6"></div>

                            <div class="col-6">
                                <label class="form-label small mb-1">Tổng quân số CNQP:</label>
                                <input type="number" name="total_cnqp" class="form-control" placeholder="Điền tổng số CNQP" min="0" value="{{ getFieldValue('total_cnqp', $formData, 0) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. QUÂN SỐ CÓ MẶT -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><strong>2. QUÂN SỐ CÓ MẶT:</strong></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số SQ có mặt:</label>
                                <input type="number" name="present_sq" class="form-control" placeholder="Điền số SQ Quân có mặt" min="0" value="{{ getFieldValue('present_sq', $formData, 0) }}">
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4"></div>

                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số QNCN có mặt:</label>
                                <input type="number" name="present_qncn" class="form-control" placeholder="Điền số QNCN có mặt" min="0" value="{{ getFieldValue('present_qncn', $formData, 0) }}">
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4"></div>

                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số CNQP có mặt:</label>
                                <input type="number" name="present_cnqp" class="form-control" placeholder="Điền số CNQP có mặt" min="0" value="{{ getFieldValue('present_cnqp', $formData, 0) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. QUÂN SỐ VẮNG MẶT -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><strong>3. QUÂN SỐ VẮNG MẶT:</strong></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số SQ vắng mặt:</label>
                                <input type="number" name="absent_sq" class="form-control" placeholder="Điền số SQ Quân vắng mặt" min="0" value="{{ getFieldValue('absent_sq', $formData, 0) }}">
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4"></div>

                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số QNCN vắng mặt:</label>
                                <input type="number" name="absent_qncn" class="form-control" placeholder="Điền số QNCN vắng mặt" min="0" value="{{ getFieldValue('absent_qncn', $formData, 0) }}">
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4"></div>

                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số CNQP vắng mặt:</label>
                                <input type="number" name="absent_cnqp" class="form-control" placeholder="Điền số CNQP vắng mặt" min="0" value="{{ getFieldValue('absent_cnqp', $formData, 0) }}">
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
                        <h5 class="mb-0"><strong>4. LÝ DO VẮNG MẶT:</strong></h5>
                    </div>
                    <div class="card-body">
                        <!-- 4.1 Quân số công tác -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.1. Quân số công tác:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="cong_tac_sq" class="form-control form-control-sm" value="{{ getFieldValue('cong_tac_sq', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="cong_tac_qncn" class="form-control form-control-sm" value="{{ getFieldValue('cong_tac_qncn', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="cong_tac_cnqp" class="form-control form-control-sm" value="{{ getFieldValue('cong_tac_cnqp', $formData, 0) }}" min="0"></div>
                            </div>
                        </div>

                        <!-- 4.2 Quân số cơ động -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.2. Quân số cơ động:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="co_dong_sq" class="form-control form-control-sm" value="{{ getFieldValue('co_dong_sq', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="co_dong_qncn" class="form-control form-control-sm" value="{{ getFieldValue('co_dong_qncn', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="co_dong_cnqp" class="form-control form-control-sm" value="{{ getFieldValue('co_dong_cnqp', $formData, 0) }}" min="0"></div>
                            </div>
                        </div>

                        <!-- 4.3 Quân số đi học -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.3. Quân số đi học:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="di_hoc_sq" class="form-control form-control-sm" value="{{ getFieldValue('di_hoc_sq', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="di_hoc_qncn" class="form-control form-control-sm" value="{{ getFieldValue('di_hoc_qncn', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="di_hoc_cnqp" class="form-control form-control-sm" value="{{ getFieldValue('di_hoc_cnqp', $formData, 0) }}" min="0"></div>
                            </div>
                        </div>

                        <!-- 4.4 Quân số đi Phép -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.4. Quân số đi Phép:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="di_phep_sq" class="form-control form-control-sm" value="{{ getFieldValue('di_phep_sq', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="di_phep_qncn" class="form-control form-control-sm" value="{{ getFieldValue('di_phep_qncn', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="di_phep_cnqp" class="form-control form-control-sm" value="{{ getFieldValue('di_phep_cnqp', $formData, 0) }}" min="0"></div>
                            </div>
                        </div>

                        <!-- 4.5 Lý do khác -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.5. Lý do khác:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="ly_do_khac_sq" class="form-control form-control-sm" value="{{ getFieldValue('ly_do_khac_sq', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="ly_do_khac_qncn" class="form-control form-control-sm" value="{{ getFieldValue('ly_do_khac_qncn', $formData, 0) }}" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="ly_do_khac_cnqp" class="form-control form-control-sm" value="{{ getFieldValue('ly_do_khac_cnqp', $formData, 0) }}" min="0"></div>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div>
                            <label class="form-label small mb-1"><strong>Ghi chú:</strong></label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Nguyễn Văn A: Công tác tại ...">{{ getFieldValue('note', $formData, '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12 text-center">
                <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                    <i class="la la-refresh"></i> Làm mới
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="la la-save"></i> {{ isset($existingReport) && $existingReport ? 'Cập nhật' : 'Xác nhận' }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Auto-fill thống kê khi chọn phòng ban
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department-select');

    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            const departmentId = this.value;

            if (!departmentId) {
                // Clear all fields if no department selected
                clearAllFields();
                return;
            }

            // Fetch department statistics
            fetch(`/daily-personnel-report/api/department-stats/${departmentId}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.error || `HTTP ${response.status}`);
                        }).catch(() => {
                            throw new Error(`HTTP ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Department stats:', data);

                    // Fill TỔNG QUÂN SỐ
                    document.querySelector('input[name="total_sq"]').value = data.sq || 0;
                    document.querySelector('input[name="total_qncn"]').value = data.qncn || 0;
                    document.querySelector('input[name="total_cnqp"]').value = data.cnqp || 0;

                    // Fill QUÂN SỐ CÓ MẶT (mặc định = tổng)
                    document.querySelector('input[name="present_sq"]').value = data.sq || 0;
                    document.querySelector('input[name="present_qncn"]').value = data.qncn || 0;
                    document.querySelector('input[name="present_cnqp"]').value = data.cnqp || 0;

                    // QUÂN SỐ VẮNG MẶT = 0 (user sẽ điền sau)
                    document.querySelector('input[name="absent_sq"]').value = 0;
                    document.querySelector('input[name="absent_qncn"]').value = 0;
                    document.querySelector('input[name="absent_cnqp"]').value = 0;
                })
                .catch(error => {
                    console.error('Error fetching department stats:', error);
                    alert('Lỗi khi tải thống kê phòng ban: ' + error.message);
                });
        });

        // Auto-trigger if department is pre-selected (but only if no existing report loaded)
        @if(!isset($existingReport) || !$existingReport)
        if (departmentSelect.value) {
            departmentSelect.dispatchEvent(new Event('change'));
        }
        @endif
    }

    function clearAllFields() {
        const fields = [
            'total_sq', 'total_qncn', 'total_cnqp',
            'present_sq', 'present_qncn', 'present_cnqp',
            'absent_sq', 'absent_qncn', 'absent_cnqp'
        ];

        fields.forEach(field => {
            const input = document.querySelector(`input[name="${field}"]`);
            if (input) input.value = '';
        });
    }
});
</script>

<style>
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

.card {
    border-radius: 8px;
}

.alert {
    border-radius: 8px;
}
</style>
@endsection


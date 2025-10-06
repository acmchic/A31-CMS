@extends(backpack_view('blank'))

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
        

        <!-- Basic Info -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Phòng ban <span class="text-danger">*</span></label>
                <select name="department_id" id="department-select" class="form-control" required>
                    @if(isset($departmentOptions) && count($departmentOptions) == 1)
                        @foreach($departmentOptions as $id => $name)
                            <option value="{{ $id }}" selected>{{ $name }}</option>
                        @endforeach
                    @else
                        <option value="">- Chọn phòng ban -</option>
                        @foreach($departmentOptions ?? [] as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">Ngày báo cáo <span class="text-danger">*</span></label>
                <input type="date" name="report_date" id="report-date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
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
                                <input type="number" name="total_sq" class="form-control" placeholder="Điền tổng số SQ Quân" min="0">
                            </div>
                            <div class="col-6"></div>
                            
                            <div class="col-6">
                                <label class="form-label small mb-1">Tổng quân số QNCN:</label>
                                <input type="number" name="total_qncn" class="form-control" placeholder="Điền tổng số QNCN" min="0">
                            </div>
                            <div class="col-6"></div>
                            
                            <div class="col-6">
                                <label class="form-label small mb-1">Tổng quân số CNQP:</label>
                                <input type="number" name="total_cnqp" class="form-control" placeholder="Điền tổng số CNQP" min="0">
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
                                <input type="number" name="present_sq" class="form-control" placeholder="Điền số SQ Quân có mặt" min="0">
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4"></div>
                            
                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số QNCN có mặt:</label>
                                <input type="number" name="present_qncn" class="form-control" placeholder="Điền số QNCN có mặt" min="0">
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4"></div>
                            
                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số CNQP có mặt:</label>
                                <input type="number" name="present_cnqp" class="form-control" placeholder="Điền số CNQP có mặt" min="0">
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
                                <input type="number" name="absent_sq" class="form-control" placeholder="Điền số SQ Quân vắng mặt" min="0">
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4"></div>
                            
                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số QNCN vắng mặt:</label>
                                <input type="number" name="absent_qncn" class="form-control" placeholder="Điền số QNCN vắng mặt" min="0">
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4"></div>
                            
                            <div class="col-4">
                                <label class="form-label small mb-1">Quân số CNQP vắng mặt:</label>
                                <input type="number" name="absent_cnqp" class="form-control" placeholder="Điền số CNQP vắng mặt" min="0">
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
                                <div class="col-3"><input type="number" name="cong_tac_sq" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="cong_tac_qncn" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="cong_tac_cnqp" class="form-control form-control-sm" value="0" min="0"></div>
                            </div>
                        </div>

                        <!-- 4.2 Quân số cơ động -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.2. Quân số cơ động:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="co_dong_sq" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="co_dong_qncn" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="co_dong_cnqp" class="form-control form-control-sm" value="0" min="0"></div>
                            </div>
                        </div>

                        <!-- 4.3 Quân số đi học -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.3. Quân số đi học:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="di_hoc_sq" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="di_hoc_qncn" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="di_hoc_cnqp" class="form-control form-control-sm" value="0" min="0"></div>
                            </div>
                        </div>

                        <!-- 4.4 Quân số đi Phép -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.4. Quân số đi Phép:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="di_phep_sq" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="di_phep_qncn" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="di_phep_cnqp" class="form-control form-control-sm" value="0" min="0"></div>
                            </div>
                        </div>

                        <!-- 4.5 Lý do khác -->
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><em>4.5. Lý do khác:</em></h6>
                            <div class="row g-2">
                                <div class="col-1"><label class="form-label small mb-1">SQ:</label></div>
                                <div class="col-3"><input type="number" name="ly_do_khac_sq" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">QNCN:</label></div>
                                <div class="col-2"><input type="number" name="ly_do_khac_qncn" class="form-control form-control-sm" value="0" min="0"></div>
                                <div class="col-2"><label class="form-label small mb-1">CNQP:</label></div>
                                <div class="col-2"><input type="number" name="ly_do_khac_cnqp" class="form-control form-control-sm" value="0" min="0"></div>
                            </div>
                        </div>

                        <!-- Ghi chú -->
                        <div>
                            <label class="form-label small mb-1"><strong>Ghi chú:</strong></label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Nguyễn Văn A: Công tác tại CKTĐ ĐT Đức (R. C. D)- QĐ đồng tài..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12 text-center">
                <a href="{{ backpack_url('daily-personnel-report') }}" class="btn btn-secondary">
                    <i class="la la-times"></i> Hủy
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="la la-save"></i> Xác nhận
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
        
        // Auto-trigger if department is pre-selected
        if (departmentSelect.value) {
            departmentSelect.dispatchEvent(new Event('change'));
        }
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


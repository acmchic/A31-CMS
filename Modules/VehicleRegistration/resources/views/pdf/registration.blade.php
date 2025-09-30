<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký Sử Dụng Xe - #{{ $registration->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        
        .header h2 {
            font-size: 16px;
            margin: 5px 0;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            width: 150px;
            font-weight: bold;
            color: #333;
        }
        
        .info-value {
            flex: 1;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin: 20px 0 10px 0;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-box {
            border: 1px solid #333;
            padding: 15px;
            margin: 10px 0;
            min-height: 80px;
            position: relative;
        }
        
        .signature-title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .signature-info {
            font-size: 11px;
            color: #666;
        }
        
        .signature-image {
            position: absolute;
            right: 15px;
            top: 15px;
            max-width: 100px;
            max-height: 60px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        .vehicle-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ĐĂNG KÝ SỬ DỤNG XE</h1>
        <h2>Nhà máy A31 - Bộ Quốc Phòng</h2>
        <p>Số: {{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</p>
    </div>

    <div class="section-title">I. THÔNG TIN ĐĂNG KÝ</div>
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Người đăng ký:</div>
            <div class="info-value">{{ $registration->user->name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Phòng ban:</div>
            <div class="info-value">{{ $registration->user->department->name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Ngày đăng ký:</div>
            <div class="info-value">{{ $registration->created_at->format('d/m/Y H:i') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Trạng thái:</div>
            <div class="info-value">
                <span class="status-badge {{ $registration->status === 'approved' ? 'status-approved' : ($registration->status === 'rejected' ? 'status-rejected' : 'status-pending') }}">
                    {{ $registration->status_display }}
                </span>
            </div>
        </div>
    </div>

    <div class="section-title">II. THÔNG TIN CHUYẾN ĐI</div>
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Ngày giờ đi:</div>
            <div class="info-value">
                {{ $registration->departure_datetime ? \Carbon\Carbon::parse($registration->departure_datetime)->format('d/m/Y H:i') : 'N/A' }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Ngày giờ về:</div>
            <div class="info-value">
                {{ $registration->return_datetime ? \Carbon\Carbon::parse($registration->return_datetime)->format('d/m/Y H:i') : 'N/A' }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Tuyến đường:</div>
            <div class="info-value">{{ $registration->route }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Mục đích:</div>
            <div class="info-value">{{ $registration->purpose }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Số lượng người:</div>
            <div class="info-value">{{ $registration->passenger_count }} người</div>
        </div>
        @if($registration->cargo_description)
        <div class="info-row">
            <div class="info-label">Hàng hóa:</div>
            <div class="info-value">{{ $registration->cargo_description }}</div>
        </div>
        @endif
    </div>

    @if($registration->vehicle_id || $registration->driver_name)
    <div class="section-title">III. THÔNG TIN XE VÀ LÁI XE</div>
    <div class="vehicle-info">
        @if($registration->vehicle)
        <div class="info-row">
            <div class="info-label">Xe được phân:</div>
            <div class="info-value">{{ $registration->vehicle->full_name ?? 'N/A' }}</div>
        </div>
        @endif
        
        <div class="info-row">
            <div class="info-label">Lái xe:</div>
            <div class="info-value">
                {{ $registration->driver->name ?? $registration->driver_name ?? 'N/A' }}
            </div>
        </div>
        
        @if($registration->driver_license)
        <div class="info-row">
            <div class="info-label">Số bằng lái:</div>
            <div class="info-value">{{ $registration->driver_license }}</div>
        </div>
        @endif
    </div>
    @endif

    @if($registration->status === 'approved')
    <div class="section-title">IV. PHÊ DUYỆT</div>
    <div class="signature-section">
        @if($registration->director_approved_at)
        <div class="signature-box">
            <div class="signature-title">NGƯỜI PHÊ DUYỆT</div>
            <div class="signature-info">
                <strong>Họ tên:</strong> {{ $registration->directorApprover->name ?? 'N/A' }}<br>
                <strong>Chức vụ:</strong> {{ $registration->directorApprover->position_display ?? 'N/A' }}<br>
                <strong>Thời gian:</strong> {{ $registration->director_approved_at->format('d/m/Y H:i') }}<br>
                <strong>Ý kiến:</strong> Đồng ý cho sử dụng xe theo đăng ký
            </div>
            
            @if($registration->directorApprover && $registration->directorApprover->signature_path)
            <img src="{{ public_path('storage/' . $registration->directorApprover->signature_path) }}" 
                 alt="Chữ ký" class="signature-image">
            @endif
            
            <div style="position: absolute; bottom: 15px; right: 15px; font-size: 10px; color: #999;">
                Ký số: {{ $registration->director_approved_at->format('dmY_His') }}
            </div>
        </div>
        @endif
    </div>
    @endif

    @if($registration->status === 'rejected')
    <div class="section-title">IV. TỪ CHỐI</div>
    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Lý do từ chối:</div>
            <div class="info-value">{{ $registration->rejection_reason ?? 'Không đáp ứng yêu cầu' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Cấp từ chối:</div>
            <div class="info-value">{{ $registration->rejection_level === 'department' ? 'Phòng ban' : 'Ban Giám Đốc' }}</div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Tài liệu được tạo tự động bởi hệ thống A31 CMS</p>
        <p>Thời gian tạo: {{ $generated_at }}</p>
        @if($registration->status === 'approved')
        <p><strong>Tài liệu đã được ký số điện tử</strong></p>
        @endif
    </div>
</body>
</html>

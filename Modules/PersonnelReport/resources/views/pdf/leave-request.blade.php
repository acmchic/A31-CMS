<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Đơn xin nghỉ phép #{{ $leave->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            margin: 15px;
        }
        .header-container {
            width: 100%;
            margin-bottom: 5px;
        }
        .header-center {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.4;
        }
        .logo {
            width: 70px;
            height: auto;
            margin-bottom: 5px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        h1 {
            text-align: center;
            color: #000;
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0 12px 0;
            text-transform: uppercase;
        }
        h2 {
            color: #000;
            font-size: 12px;
            font-weight: bold;
            margin: 12px 0 6px 0;
            border-bottom: none;
        }
        table.info-table td {
            border: 1px solid #333;
            padding: 6px 8px;
            font-size: 11px;
        }
        table.info-table td:first-child {
            width: 32%;
            background-color: #f5f5f5;
            font-weight: normal;
        }
        table.info-table td:nth-child(2) {
            width: 68%;
        }
        .signature-section {
            margin-top: 35px;
        }
        table.signature-table {
            border: none;
            margin-top: 20px;
        }
        table.signature-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            padding: 10px;
        }
        .signature-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
        }
        .signature-note {
            font-style: italic;
            font-size: 9px;
            color: #666;
            margin-bottom: 40px;
        }
        .signature-name {
            font-weight: bold;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-rejected {
            color: red;
            font-weight: bold;
        }
        .rejection-box {
            background-color: #ffe6e6;
            border-left: 4px solid #cc0000;
            padding: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <div class="header-center">
            <img src="{{ public_path('assets/logo/logo.png') }}" class="logo" alt="Logo"><br>
            QUÂN CHỦNG PHÒNG KHÔNG - KHÔNG QUÂN<br>
            NHÀ MÁY A31
        </div>
    </div>

    <h1>Đơn xin nghỉ phép</h1>

    <h2>I. THÔNG TIN NHÂN VIÊN</h2>
    <table border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse; margin: 6px 0 10px 0;">
        <tr>
            <td style="width: 30%; background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Họ và tên:</td>
            <td style="width: 70%; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $employee ? $employee->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Phòng ban:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $department ? $department->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Chức vụ:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $employee && $employee->position ? $employee->position->name : 'N/A' }}</td>
        </tr>
    </table>

    <h2>II. NỘI DUNG NGHỈ PHÉP</h2>
    <table border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse; margin: 6px 0 10px 0;">
        <tr>
            <td style="width: 30%; background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Loại nghỉ:</td>
            <td style="width: 70%; border: 1px solid #333; padding: 6px 8px; font-size: 10px;"><strong>{{ $leave->leave_type_text }}</strong></td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Từ ngày:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $leave->from_date ? $leave->from_date->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Đến ngày:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $leave->to_date ? $leave->to_date->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Địa điểm:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $leave->location ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Lý do:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $leave->note ?? 'N/A' }}</td>
        </tr>
    </table>

    @if($leave->isApproved() || $leave->workflow_status === 'approved_by_approver' || $leave->workflow_status === 'approved_by_director')
    <h2>III. PHÊ DUYỆT</h2>
    <table border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse; margin: 6px 0 10px 0;">
        @if($leave->level1Approver || $leave->approverUser)
        <tr>
            <td style="width: 30%; background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Cấp 1 - Người duyệt:</td>
            <td style="width: 70%; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $leave->level1Approver ? $leave->level1Approver->name : ($leave->approverUser ? $leave->approverUser->name : 'N/A') }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Cấp 1 - Thời gian:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $leave->workflow_level1_at ? \Carbon\Carbon::parse($leave->workflow_level1_at)->format('d/m/Y H:i') : ($leave->approved_at_approver ? \Carbon\Carbon::parse($leave->approved_at_approver)->format('d/m/Y H:i') : '--') }}</td>
        </tr>
        @endif

        @if($leave->level2Approver || $leave->directorUser)
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Cấp 2 - Người duyệt:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $leave->level2Approver ? $leave->level2Approver->name : ($leave->directorUser ? $leave->directorUser->name : 'N/A') }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Cấp 2 - Thời gian:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $leave->workflow_level2_at ? \Carbon\Carbon::parse($leave->workflow_level2_at)->format('d/m/Y H:i') : ($leave->approved_at_director ? \Carbon\Carbon::parse($leave->approved_at_director)->format('d/m/Y H:i') : '--') }}</td>
        </tr>
        @endif
    </table>

    <div style="margin-top: 20px;">
        <table border="0" cellpadding="5" cellspacing="0" style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                    <p style="font-weight: bold; font-size: 10px; margin: 0;">NHÂN VIÊN</p>
                    <p style="font-style: italic; font-size: 8px; color: #666; margin: 3px 0 35px 0;">(Ký và ghi rõ họ tên)</p>
                    <p style="font-weight: bold; font-size: 10px; margin: 0;">{{ $employee ? $employee->name : '' }}</p>
                </td>
                <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                    <p style="font-weight: bold; font-size: 10px; margin: 0;">{{ $approver && $approver->department ? strtoupper($approver->department->name) : 'THỦ TRƯỞNG ĐƠN VỊ' }}</p>
                    <p style="font-style: italic; font-size: 8px; color: #666; margin: 3px 0;">
                        (Đã ký số)
                        @if($leave->workflow_level2_at)
                        <br>Ngày {{ \Carbon\Carbon::parse($leave->workflow_level2_at)->format('d/m/Y') }}
                        @elseif($leave->approved_at_director)
                        <br>Ngày {{ \Carbon\Carbon::parse($leave->approved_at_director)->format('d/m/Y') }}
                        @endif
                    </p>
                    <p style="font-weight: bold; font-size: 10px; margin: 35px 0 0 0;">{{ $approver ? $approver->name : '' }}</p>
                </td>
            </tr>
        </table>
    </div>
    @endif

    @if($leave->isRejected() && $leave->rejection_reason)
    <h2>III. TỪ CHỐI</h2>
    <div style="background-color: #ffe6e6; border-left: 4px solid #cc0000; padding: 8px; margin: 8px 0;">
        <strong>Lý do:</strong> {{ $leave->rejection_reason }}
    </div>
    @endif

    <div style="margin-top: 15px; text-align: center; font-size: 7px; color: #888; border-top: 1px solid #ddd; padding-top: 8px;">
        <p style="margin: 2px 0;">Tài liệu được tạo tự động - {{ $generated_at }}</p>
        @if($leave->isApproved() || $leave->workflow_status === 'approved_by_director')
        <p style="font-style: italic; margin: 2px 0;">Chữ ký số hợp lệ - Xác thực bởi Nhà máy A31</p>
        @endif
    </div>
</body>
</html>


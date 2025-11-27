<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Đăng ký xe #{{ $registration->id }}</title>
    <style>
        body {
            font-family: 'Inter', 'DejaVu Sans', sans-serif;
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
            padding: 8px;
            margin: 8px 0;
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

    <h1>Phiếu đăng ký sử dụng xe</h1>

    <h2>I. THÔNG TIN ĐĂNG KÝ</h2>
    <table border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse; margin: 6px 0 10px 0;">
        <tr>
            <td style="width: 30%; background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Người đăng ký:</td>
            <td style="width: 70%; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $requester ? $requester->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Phòng ban:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $requester && $requester->department ? $requester->department->name : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Ngày đi:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $registration->departure_datetime ? \Carbon\Carbon::parse($registration->departure_datetime)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Ngày về:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $registration->return_datetime ? \Carbon\Carbon::parse($registration->return_datetime)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Tuyến đường:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $registration->route ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Mục đích:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $registration->purpose ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Số người:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $registration->passenger_count }}</td>
        </tr>
    </table>

    <h2>II. THÔNG TIN XE VÀ LÁI XE</h2>
    <table border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse; margin: 6px 0 10px 0;">
        <tr>
            <td style="width: 30%; background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Xe được giao:</td>
            <td style="width: 70%; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $vehicle ? $vehicle->full_name : 'Chưa phân' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Lái xe:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $registration->driver_name ?? 'Chưa phân' }}</td>
        </tr>
        @if($registration->driver_license)
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Bằng lái:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $registration->driver_license }}</td>
        </tr>
        @endif
    </table>

    @php
        $approvalRequest = $registration->approvalRequest;
        $isApproved = $approvalRequest && $approvalRequest->status === 'approved';
        $approvalHistory = $approvalRequest ? ($approvalRequest->approval_history ?? []) : [];
    @endphp
    @if($isApproved)
    <h2>III. PHÊ DUYỆT</h2>
    <table border="1" cellpadding="6" cellspacing="0" style="width: 100%; border-collapse: collapse; margin: 6px 0 10px 0;">
        @php
            $deptApproval = $approvalHistory['department_head_approval'] ?? null;
            $directorApproval = $approvalHistory['director_approval'] ?? null;
            $deptApprover = $deptApproval && isset($deptApproval['approved_by']) ? \App\Models\User::find($deptApproval['approved_by']) : null;
            $directorApprover = $directorApproval && isset($directorApproval['approved_by']) ? \App\Models\User::find($directorApproval['approved_by']) : null;
        @endphp
        @if($deptApprover)
        <tr>
            <td style="width: 30%; background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Phòng ban - Người duyệt:</td>
            <td style="width: 70%; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $deptApprover->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Phòng ban - Thời gian:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ isset($deptApproval['approved_at']) ? \Carbon\Carbon::parse($deptApproval['approved_at'])->format('d/m/Y H:i') : '--' }}</td>
        </tr>
        @endif

        @if($directorApprover)
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Ban giám đốc - Người duyệt:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ $directorApprover->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="background-color: #f5f5f5; border: 1px solid #333; padding: 6px 8px; font-size: 10px;">Ban giám đốc - Thời gian:</td>
            <td style="border: 1px solid #333; padding: 6px 8px; font-size: 10px;">{{ isset($directorApproval['approved_at']) ? \Carbon\Carbon::parse($directorApproval['approved_at'])->format('d/m/Y H:i') : '--' }}</td>
        </tr>
        @endif
    </table>

    <div style="margin-top: 20px;">
        <table border="0" cellpadding="5" cellspacing="0" style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                    <p style="font-weight: bold; font-size: 10px; margin: 0;">NGƯỜI ĐĂNG KÝ</p>
                    <p style="font-style: italic; font-size: 8px; color: #666; margin: 3px 0 35px 0;">(Ký và ghi rõ họ tên)</p>
                    <p style="font-weight: bold; font-size: 10px; margin: 0;">{{ $requester ? $requester->name : '' }}</p>
                </td>
                <td style="width: 50%; border: none; text-align: center; vertical-align: top;">
                    <p style="font-weight: bold; font-size: 10px; margin: 0;">{{ $approver ? $approver->getApproverTitle() : 'BAN GIÁM ĐỐC' }}</p>
                    <p style="font-style: italic; font-size: 8px; color: #666; margin: 3px 0;">
                        @if(isset($directorApproval['approved_at']))
                        Ngày {{ \Carbon\Carbon::parse($directorApproval['approved_at'])->format('d/m/Y') }}
                        @elseif(isset($deptApproval['approved_at']))
                        Ngày {{ \Carbon\Carbon::parse($deptApproval['approved_at'])->format('d/m/Y') }}
                        @endif
                    </p>
                    <p style="font-weight: bold; font-size: 10px; margin: 35px 0 0 0;">{{ $approver ? $approver->name : '' }}</p>
                </td>
            </tr>
        </table>
    </div>
    @endif

    @php
        $isRejected = $approvalRequest && $approvalRequest->status === 'rejected';
        $rejectionReason = $approvalRequest ? $approvalRequest->rejection_reason : null;
    @endphp
    @if($isRejected && $rejectionReason)
    <h2>III. TỪ CHỐI</h2>
    <div class="rejection-box">
        <strong>Lý do:</strong> {{ $rejectionReason }}
    </div>
    @endif

    <div style="margin-top: 15px; margin-bottom: 60px; text-align: center; font-size: 8px; color: #666; border-top: 1px solid #ddd; padding-top: 8px;">
        <p style="margin: 0;">Nhà máy A31</p>
    </div>
</body>
</html>

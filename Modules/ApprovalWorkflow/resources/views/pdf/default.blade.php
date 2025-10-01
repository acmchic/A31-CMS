<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $model->getPdfTitle() ?? 'Document' }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 10px;
        }
        h2 {
            color: #34495e;
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        .info-row td:first-child {
            font-weight: bold;
            width: 40%;
            background-color: #ecf0f1;
        }
        .signature-section {
            margin-top: 30px;
        }
        .signature-box {
            text-align: center;
            margin-top: 50px;
        }
        .company-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .status-approved {
            color: green;
            font-weight: bold;
        }
        .status-rejected {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="company-header">
        <h1>{{ config('app.name', 'A31 FACTORY') }}</h1>
        <p style="font-style: italic;">{{ config('app.url') }}</p>
    </div>

    <h1>{{ strtoupper($model->getPdfTitle() ?? 'DOCUMENT') }}</h1>
    
    <h2>I. THÔNG TIN CHUNG</h2>
    <table>
        <tr class="info-row">
            <td>Mã số:</td>
            <td>#{{ $model->id }}</td>
        </tr>
        <tr class="info-row">
            <td>Trạng thái:</td>
            <td class="{{ $model->isApproved() ? 'status-approved' : ($model->isRejected() ? 'status-rejected' : '') }}">
                {{ $model->workflow_status_display }}
            </td>
        </tr>
        <tr class="info-row">
            <td>Ngày tạo:</td>
            <td>{{ $model->created_at->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    @if($model->isApproved())
    <h2>II. THÔNG TIN PHÊ DUYỆT</h2>
    <table>
        @if($model->level1Approver)
        <tr class="info-row">
            <td>Người phê duyệt cấp 1:</td>
            <td>{{ $model->level1Approver->name }}</td>
        </tr>
        <tr class="info-row">
            <td>Thời gian phê duyệt cấp 1:</td>
            <td>{{ $model->workflow_level1_at ? $model->workflow_level1_at->format('d/m/Y H:i:s') : '--' }}</td>
        </tr>
        @endif

        @if($model->level2Approver)
        <tr class="info-row">
            <td>Người phê duyệt cấp 2:</td>
            <td>{{ $model->level2Approver->name }}</td>
        </tr>
        <tr class="info-row">
            <td>Thời gian phê duyệt cấp 2:</td>
            <td>{{ $model->workflow_level2_at ? $model->workflow_level2_at->format('d/m/Y H:i:s') : '--' }}</td>
        </tr>
        @endif

        @if($model->level3Approver)
        <tr class="info-row">
            <td>Người phê duyệt cấp 3:</td>
            <td>{{ $model->level3Approver->name }}</td>
        </tr>
        <tr class="info-row">
            <td>Thời gian phê duyệt cấp 3:</td>
            <td>{{ $model->workflow_level3_at ? $model->workflow_level3_at->format('d/m/Y H:i:s') : '--' }}</td>
        </tr>
        @endif
    </table>

    <div class="signature-section">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 50%;"></td>
                <td style="border: none; width: 50%; text-align: center;">
                    <p><strong>{{ $approver ? strtoupper($approver->department->name ?? 'NGƯỜI PHÊ DUYỆT') : 'NGƯỜI PHÊ DUYỆT' }}</strong></p>
                    <p style="font-style: italic; font-size: 10px;">(Đã ký số)</p>
                    <br><br><br>
                    <p><strong>{{ $approver ? $approver->name : '' }}</strong></p>
                </td>
            </tr>
        </table>
    </div>
    @endif

    @if($model->isRejected() && $model->rejection_reason)
    <h2>II. LÝ DO TỪ CHỐI</h2>
    <p style="padding: 10px; background-color: #fee; border-left: 4px solid #f00;">
        {{ $model->rejection_reason }}
    </p>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #999;">
        <p>Tài liệu được tạo tự động bởi {{ config('app.name') }}</p>
        <p>Thời gian tạo: {{ $generated_at }}</p>
    </div>
</body>
</html>



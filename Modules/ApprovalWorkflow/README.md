# ApprovalWorkflow Module

Module chung để xử lý **Workflow Phê duyệt** và **Ký số điện tử** cho tất cả các module trong hệ thống.

## 🎯 Tính năng

- ✅ Hỗ trợ workflow 1 cấp, 2 cấp, 3 cấp (có thể mở rộng)
- ✅ Tích hợp ký số điện tử với PIN bảo mật
- ✅ Tạo PDF với chữ ký số (Adobe Reader compatible)
- ✅ Lưu lịch sử phê duyệt (audit trail)
- ✅ Tái sử dụng cho nhiều module khác nhau
- ✅ Cấu hình linh hoạt qua config file

## 📦 Cài đặt

### 1. Chạy migration

```bash
php artisan migrate
```

### 2. Enable module

Module đã được tự động enable khi tạo. Kiểm tra bằng:

```bash
php artisan module:list
```

## 🚀 Sử dụng

### Bước 1: Thêm Traits vào Model

```php
use Modules\ApprovalWorkflow\Traits\HasApprovalWorkflow;
use Modules\ApprovalWorkflow\Traits\HasDigitalSignature;
use Modules\ApprovalWorkflow\Traits\ApprovalButtons;

class YourModel extends Model
{
    use HasApprovalWorkflow, HasDigitalSignature, ApprovalButtons;
    
    // Định nghĩa workflow type (mặc định là two_level)
    protected $workflowType = 'two_level'; // hoặc 'single', 'three_level'
    
    // Định nghĩa PDF view (tùy chọn)
    protected $pdfView = 'yourmodule::pdf.your-template';
    
    // Định nghĩa PDF directory (tùy chọn)
    protected $pdfDirectory = 'your_module_pdfs';
    
    // Override module permission nếu cần
    protected function getModulePermission(): string
    {
        return 'your_module';
    }
}
```

### Bước 2: Thêm các cột vào Database Migration

```php
Schema::table('your_table', function (Blueprint $table) {
    // Workflow status
    $table->string('workflow_status')->default('pending');
    
    // Level 1 approval
    $table->unsignedBigInteger('workflow_level1_by')->nullable();
    $table->timestamp('workflow_level1_at')->nullable();
    $table->string('workflow_level1_signature')->nullable();
    
    // Level 2 approval (nếu dùng 2-level hoặc 3-level)
    $table->unsignedBigInteger('workflow_level2_by')->nullable();
    $table->timestamp('workflow_level2_at')->nullable();
    $table->string('workflow_level2_signature')->nullable();
    
    // Level 3 approval (nếu dùng 3-level)
    $table->unsignedBigInteger('workflow_level3_by')->nullable();
    $table->timestamp('workflow_level3_at')->nullable();
    $table->string('workflow_level3_signature')->nullable();
    
    // Rejection
    $table->text('rejection_reason')->nullable();
    
    // Signed PDF path
    $table->string('signed_pdf_path')->nullable();
    
    // Foreign keys
    $table->foreign('workflow_level1_by')->references('id')->on('users')->onDelete('set null');
    $table->foreign('workflow_level2_by')->references('id')->on('users')->onDelete('set null');
    $table->foreign('workflow_level3_by')->references('id')->on('users')->onDelete('set null');
});
```

### Bước 3: Thêm buttons vào CRUD Controller

```php
protected function setupButtonsBasedOnPermissions()
{
    $user = backpack_user();

    // Thêm approve button
    if (PermissionHelper::can($user, 'your_module.approve')) {
        CRUD::addButtonFromModelFunction('line', 'approve', 'approveButton', 'beginning');
        CRUD::addButtonFromModelFunction('line', 'reject', 'rejectButton', 'beginning');
        CRUD::addButtonFromModelFunction('line', 'download_pdf', 'downloadPdfButton', 'beginning');
    }
}
```

### Bước 4: (Tùy chọn) Tạo Custom PDF Template

Tạo file view cho PDF của bạn:

**resources/views/yourmodule/pdf/your-template.blade.php:**

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Document Title</title>
    <style>
        /* Your custom styles */
    </style>
</head>
<body>
    <h1>{{ $model->title }}</h1>
    
    <!-- Your custom content -->
    <p>Model ID: {{ $model->id }}</p>
    <p>Status: {{ $model->workflow_status_display }}</p>
    
    <!-- Approver signature -->
    @if($approver)
    <div class="signature">
        <p>Approved by: {{ $approver->name }}</p>
        <p>Date: {{ $generated_at }}</p>
    </div>
    @endif
</body>
</html>
```

## ⚙️ Cấu hình

File cấu hình: `Modules/ApprovalWorkflow/config/approval.php`

```php
return [
    // Workflow type mặc định
    'default_workflow_type' => 'two_level',
    
    // Cấu hình các loại workflow
    'workflow_levels' => [
        'single' => [...],
        'two_level' => [...],
        'three_level' => [...]
    ],
    
    // Digital signature settings
    'digital_signature' => [
        'enabled' => true,
        'require_pin' => true,
        'certificate_password' => env('CERTIFICATE_PASSWORD'),
    ],
    
    // PDF settings
    'pdf' => [
        'engine' => 'tcpdf', // hoặc 'dompdf'
        'paper' => 'A4',
    ],
];
```

## 📝 API

### ApprovalService

```php
use Modules\ApprovalWorkflow\Services\ApprovalService;

$approvalService = app(ApprovalService::class);

// Approve
$approvalService->approve($model, $approver, [
    'comment' => 'Approved',
    'metadata' => ['key' => 'value']
]);

// Approve with signature
$approvalService->approveWithSignature($model, $approver, $pin, [
    'comment' => 'Approved with signature'
]);

// Reject
$approvalService->reject($model, $approver, 'Rejection reason', [
    'comment' => 'Additional comment'
]);

// Get history
$history = $approvalService->getHistory($model);
```

### Model Methods

```php
// Check status
$model->canBeApproved();
$model->canBeRejected();
$model->isApproved();
$model->isRejected();

// Get workflow info
$model->getWorkflowType();
$model->getCurrentWorkflowStep();
$model->getNextWorkflowStep();

// Get approvers
$model->level1Approver;
$model->level2Approver;
$model->level3Approver;

// Get approval history
$model->approvalHistory;
```

## 🔄 Migration từ code cũ

Xem file `MIGRATION_GUIDE.md` để biết cách migrate từ code cũ sang ApprovalWorkflow module.

## 📚 Ví dụ

Xem các ví dụ cụ thể trong:
- `VehicleRegistration` module (đã refactor)
- `PersonnelReport` module (đã refactor)

## 🤝 Contributing

Module này được thiết kế để tái sử dụng cho tất cả các module trong hệ thống cần workflow phê duyệt và ký số.

Nếu cần thêm tính năng mới, vui lòng mở rộng trong module này thay vì duplicate code.

## 📄 License

Internal use only - A31 Factory CMS



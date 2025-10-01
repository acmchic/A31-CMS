# Migration Guide - Chuyển đổi sang ApprovalWorkflow Module

Hướng dẫn migrate code từ implementation cũ sang ApprovalWorkflow module.

## 📋 Tóm tắt các bước

1. ✅ Cập nhật Model
2. ✅ Cập nhật Migration (nếu cần)
3. ✅ Cập nhật Controller  
4. ✅ Loại bỏ code duplicate
5. ✅ Test workflow

---

## Ví dụ: VehicleRegistration Module

### 1. Cập nhật Model

**TRƯỚC:**
```php
<?php
namespace Modules\VehicleRegistration\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRegistration extends Model
{
    protected $fillable = [
        'workflow_status',
        'department_approved_by',
        'department_approved_at',
        'director_approved_by',
        'director_approved_at',
        // ...
    ];
    
    // Custom methods
    public function isApproved() {
        return $this->status === 'approved';
    }
    
    // Custom button methods
    public function approveButton() {
        // 100+ lines of duplicate code
    }
}
```

**SAU:**
```php
<?php
namespace Modules\VehicleRegistration\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\ApprovalWorkflow\Traits\HasApprovalWorkflow;
use Modules\ApprovalWorkflow\Traits\HasDigitalSignature;
use Modules\ApprovalWorkflow\Traits\ApprovalButtons;

class VehicleRegistration extends Model
{
    use HasApprovalWorkflow, HasDigitalSignature, ApprovalButtons;
    
    // Workflow configuration
    protected $workflowType = 'two_level';
    
    // PDF configuration
    protected $pdfView = 'vehicleregistration::pdf.registration';
    protected $pdfDirectory = 'vehicle_registrations';
    
    // Override permission module name nếu cần
    protected function getModulePermission(): string
    {
        return 'vehicle_registration';
    }
    
    // Custom method để get PDF title
    public function getPdfTitle(): string
    {
        return 'Đăng ký xe số ' . $this->id;
    }
    
    // ❌ XÓA tất cả methods isApproved(), approveButton(), rejectButton()...
    // ✅ Các methods này đã có sẵn trong traits!
}
```

---

### 2. Cập nhật Database Migration

Nếu cũng đang dùng tên cột `department_approved_by`, `director_approved_by`, cần đổi sang `workflow_level1_by`, `workflow_level2_by`.

**Option A: Đổi tên cột (recommended)**

Tạo migration mới:

```php
php artisan make:migration rename_approval_columns_in_vehicle_registrations_table
```

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            // Rename columns để match với ApprovalWorkflow conventions
            $table->renameColumn('department_approved_by', 'workflow_level1_by');
            $table->renameColumn('department_approved_at', 'workflow_level1_at');
            $table->renameColumn('director_approved_by', 'workflow_level2_by');
            $table->renameColumn('director_approved_at', 'workflow_level2_at');
            
            // Add missing columns
            $table->string('workflow_level1_signature')->nullable()->after('workflow_level1_at');
            $table->string('workflow_level2_signature')->nullable()->after('workflow_level2_at');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_registrations', function (Blueprint $table) {
            $table->renameColumn('workflow_level1_by', 'department_approved_by');
            $table->renameColumn('workflow_level1_at', 'department_approved_at');
            $table->renameColumn('workflow_level2_by', 'director_approved_by');
            $table->renameColumn('workflow_level2_at', 'director_approved_at');
            
            $table->dropColumn(['workflow_level1_signature', 'workflow_level2_signature']);
        });
    }
};
```

**Option B: Override accessor trong Model (nếu không muốn đổi tên cột)**

```php
class VehicleRegistration extends Model
{
    use HasApprovalWorkflow, HasDigitalSignature, ApprovalButtons;
    
    // Map old column names to new ones
    public function getWorkflowLevel1ByAttribute()
    {
        return $this->attributes['department_approved_by'] ?? null;
    }
    
    public function getWorkflowLevel1AtAttribute()
    {
        return $this->attributes['department_approved_at'] ?? null;
    }
    
    public function getWorkflowLevel2ByAttribute()
    {
        return $this->attributes['director_approved_by'] ?? null;
    }
    
    public function getWorkflowLevel2AtAttribute()
    {
        return $this->attributes['director_approved_at'] ?? null;
    }
}
```

---

### 3. Cập nhật Controller

**TRƯỚC:**
```php
class VehicleRegistrationCrudController extends CrudController
{
    public function approveWithPin(Request $request, $id)
    {
        // 100+ lines of duplicate approval logic
        $registration = VehicleRegistration::findOrFail($id);
        
        // Validate PIN
        // Get certificate
        // Generate PDF
        // Sign PDF
        // Update status
        // etc...
    }
    
    public function reject($id)
    {
        // Custom reject logic
    }
}
```

**SAU:**
```php
class VehicleRegistrationCrudController extends CrudController
{
    // ❌ XÓA method approveWithPin() - không cần nữa!
    // ❌ XÓA method reject() - không cần nữa!
    
    // ✅ Chỉ cần setup buttons trong setupButtonsBasedOnPermissions()
    
    private function setupButtonsBasedOnPermissions()
    {
        $user = backpack_user();

        // CRUD buttons
        if (!PermissionHelper::can($user, 'vehicle_registration.create')) {
            CRUD::denyAccess('create');
        }

        if (!PermissionHelper::can($user, 'vehicle_registration.edit')) {
            CRUD::denyAccess('update');
        }

        if (!PermissionHelper::can($user, 'vehicle_registration.delete')) {
            CRUD::denyAccess('delete');
        }

        // ✅ Approval buttons - sử dụng từ ApprovalButtons trait
        if (PermissionHelper::can($user, 'vehicle_registration.approve')) {
            CRUD::addButtonFromModelFunction('line', 'approve', 'approveButton', 'beginning');
            CRUD::addButtonFromModelFunction('line', 'reject', 'rejectButton', 'beginning');
        }
        
        // Download PDF button
        if (PermissionHelper::can($user, 'vehicle_registration.view')) {
            CRUD::addButtonFromModelFunction('line', 'download_pdf', 'downloadPdfButton', 'beginning');
        }
    }
}
```

---

### 4. Loại bỏ Services cũ (Optional)

**Files có thể XÓA:**
- ❌ `app/Services/VehicleRegistrationPdfService.php` (nếu chỉ dùng cho approval PDF)
- ❌ `app/Services/TcpdfPdfSigner.php` (đã tích hợp trong ApprovalWorkflow)

**Files GIỮ LẠI nhưng REFACTOR:**
- ✅ `app/Services/UserCertificateService.php` (vẫn cần cho user certificate management)

---

### 5. Cập nhật Routes

**TRƯỚC:**
```php
// routes/web.php
Route::post('vehicle-registration/{id}/approve-with-pin', [VehicleRegistrationCrudController::class, 'approveWithPin'])
    ->name('vehicle-registration.approve-with-pin');
Route::post('vehicle-registration/{id}/reject', [VehicleRegistrationCrudController::class, 'reject'])
    ->name('vehicle-registration.reject');
```

**SAU:**
```php
// ❌ XÓA các routes này!
// ✅ ApprovalWorkflow module đã có generic routes:
//    - POST /admin/approval/approve/{modelClass}/{id}
//    - POST /admin/approval/reject/{modelClass}/{id}
```

Buttons trong Model sẽ tự động sử dụng routes từ ApprovalWorkflow module.

---

## So sánh Trước/Sau

| Aspect | Trước | Sau |
|--------|-------|-----|
| **Code trong Model** | ~300 lines | ~50 lines |
| **Code trong Controller** | ~200 lines | ~30 lines |
| **PDF Service** | Riêng cho mỗi module | Dùng chung PdfGeneratorService |
| **Button logic** | Duplicate trong mỗi model | Reusable trait |
| **Workflow logic** | Hardcoded | Configurable |
| **Maintainability** | Khó maintain | Dễ maintain |
| **Extensibility** | Khó mở rộng | Dễ mở rộng (thêm 3-level chỉ cần config) |

---

## Test Checklist

Sau khi migrate, test các chức năng sau:

- [ ] Hiển thị approve button khi có quyền
- [ ] Click approve button → Modal PIN hiện ra
- [ ] Nhập PIN đúng → Phê duyệt thành công
- [ ] Nhập PIN sai → Báo lỗi
- [ ] PDF được tạo và ký số thành công
- [ ] Download PDF → Mở bằng Adobe Reader thấy Signature Panel
- [ ] Reject button → Modal lý do từ chối hiện ra
- [ ] Reject thành công → Status chuyển sang rejected
- [ ] Approval history được lưu vào database

---

## Rollback

Nếu cần rollback:

1. Restore code cũ từ git
2. Rollback migration: `php artisan migrate:rollback`
3. Xóa ApprovalWorkflow module: `php artisan module:delete ApprovalWorkflow`

---

## Tips

- ✅ **Migrate từng module một** - Không nên migrate tất cả cùng lúc
- ✅ **Test kỹ trước khi deploy** - Đặc biệt là PDF signing
- ✅ **Backup database** trước khi chạy migration rename columns
- ✅ **Document custom logic** nếu có logic đặc biệt cho module của bạn

---

## FAQ

**Q: Tôi có thể giữ tên cột cũ không?**  
A: Có, sử dụng Option B trong Migration Guide (override accessor)

**Q: Module của tôi cần 3-cấp phê duyệt?**  
A: Thay đổi `protected $workflowType = 'three_level';` trong Model

**Q: Tôi muốn custom PDF template?**  
A: Tạo view riêng và set `protected $pdfView = 'yourmodule::pdf.template';`

**Q: Workflow status values có thay đổi không?**  
A: Có, check config `approvalworkflow::approval.workflow_levels` để thấy các status values mới

---

Cần hỗ trợ thêm? Tham khảo `Modules/ApprovalWorkflow/README.md`



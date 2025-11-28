# Tóm Tắt Triển Khai Hệ Thống Phê Duyệt Tập Trung

## ✅ Đã Hoàn Thành

### 1. Database Schema
- ✅ Migration: `2025_11_27_100000_create_approval_requests_table.php`
  - Status chung: `draft`, `submitted`, `in_review`, `approved`, `rejected`, `returned`, `cancelled`
  - `approval_steps` (JSON): Các bước duyệt đặc thù của từng module
  - `current_step`: Bước hiện tại đang xử lý
  - `selected_approvers` (JSON): Người phê duyệt cho từng step
  - `approval_history` (JSON): Lịch sử phê duyệt

### 2. Model & Service
- ✅ `ApprovalRequest` Model: Quản lý approval requests
- ✅ `ApprovalRequestService`: Service để sync từ model sang ApprovalRequest
- ✅ `ApprovalRequestObserver`: Observer để tự động sync (tùy chọn)

### 3. Mapping Logic
- ✅ `mapLeaveStatus()`: Map EmployeeLeave workflow_status → status + steps
- ✅ `mapVehicleStatus()`: Map VehicleRegistration workflow_status → status + steps
- ✅ `mapMaterialPlanStatus()`: Map MaterialPlan workflow_status → status + steps

### 4. Integration
- ✅ MaterialPlan: Đã thêm sync trong `store()` và `update()` methods

## 📋 Approval Steps Định Nghĩa

### EmployeeLeave (Nghỉ phép)
```php
['department_head_approval', 'review', 'director_approval']
```

### VehicleRegistration (Đăng ký xe)
```php
['vehicle_picked', 'director_approval']
```

### MaterialPlan (Phương án vật tư)
```php
['review', 'director_approval']
```

## 🔄 Mapping Status

| Module | Old Status | New Status | Current Step |
|--------|-----------|------------|--------------|
| **Leave** | `pending` | `submitted` | `department_head_approval` |
| | `approved_by_department_head` | `in_review` | `review` |
| | `approved_by_reviewer` | `in_review` | `director_approval` |
| | `approved_by_director` | `approved` | `null` |
| **Vehicle** | `submitted` | `submitted` | `vehicle_picked` |
| | `dept_review` | `in_review` | `director_approval` |
| | `director_review` | `in_review` | `director_approval` |
| | `approved` | `approved` | `null` |
| **MaterialPlan** | `pending` | `submitted` | `review` |
| | `approved_by_department_head` | `in_review` | `director_approval` |
| | `approved_by_reviewer` | `in_review` | `director_approval` |
| | `approved` | `approved` | `null` |

## 🚀 Cách Sử Dụng

### 1. Chạy Migration
```bash
php artisan migrate --path=Modules/ApprovalWorkflow/database/migrations/2025_11_27_100000_create_approval_requests_table.php
```

### 2. Sync Dữ Liệu Hiện Tại (Tùy chọn)
```php
use Modules\ApprovalWorkflow\Services\ApprovalRequestService;

$service = new ApprovalRequestService();

// Sync MaterialPlan
MaterialPlan::chunk(100, function($plans) use ($service) {
    foreach ($plans as $plan) {
        $service->syncFromModel($plan, 'material_plan');
    }
});

// Sync EmployeeLeave
EmployeeLeave::chunk(100, function($leaves) use ($service) {
    foreach ($leaves as $leave) {
        $service->syncFromModel($leave, 'leave');
    }
});

// Sync VehicleRegistration
VehicleRegistration::chunk(100, function($vehicles) use ($service) {
    foreach ($vehicles as $vehicle) {
        $service->syncFromModel($vehicle, 'vehicle');
    }
});
```

### 3. Tự Động Sync (Trong Controller)
MaterialPlan đã được tích hợp sẵn. Để tích hợp cho Leave và Vehicle, thêm vào controller:

```php
// Trong store() method
$entry = $this->crud->create($requestData);
$service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
$service->syncFromModel($entry, 'leave'); // hoặc 'vehicle'

// Trong update() method (sau khi update)
$entry = $this->crud->update($id, $requestData);
$service = new \Modules\ApprovalWorkflow\Services\ApprovalRequestService();
$service->syncFromModel($entry, 'leave'); // hoặc 'vehicle'
```

### 4. Query từ ApprovalRequest
```php
use Modules\ApprovalWorkflow\Models\ApprovalRequest;

// Lấy tất cả yêu cầu đang chờ BGD phê duyệt
$requests = ApprovalRequest::where('status', 'in_review')
    ->where('current_step', 'director_approval')
    ->whereJsonContains('selected_approvers->director_approval', $userId)
    ->get();

// Lấy theo module
$materialPlans = ApprovalRequest::where('module_type', 'material_plan')
    ->where('status', 'submitted')
    ->get();
```

## 📝 Cần Làm Tiếp

1. **Cập nhật ApprovalCenterService**: Query từ `approval_requests` thay vì query trực tiếp từ các model
2. **Tích hợp EmployeeLeave**: Thêm sync trong LeaveRequestCrudController
3. **Tích hợp VehicleRegistration**: Thêm sync trong VehicleRegistrationCrudController
4. **Observer (Tùy chọn)**: Đăng ký Observer trong AppServiceProvider để tự động sync
5. **Test**: Test toàn bộ flow từ tạo → submit → approve → reject

## 🎯 Lợi Ích

1. ✅ **Thống nhất**: Tất cả module dùng chung status
2. ✅ **Linh hoạt**: Mỗi module có approval_steps riêng
3. ✅ **Dễ mở rộng**: Thêm module mới chỉ cần định nghĩa steps
4. ✅ **Không phá kiến trúc**: Module cũ vẫn hoạt động bình thường




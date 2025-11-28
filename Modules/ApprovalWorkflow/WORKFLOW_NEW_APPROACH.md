# Hệ Thống Phê Duyệt Tập Trung - Cách Tiếp Cận Mới

## ⭐ Ý Tưởng Ngắn Gọn

**Status dùng chung – Bước duyệt tùy module – Không đụng lại kiến trúc sau này**

## Nguyên Tắc

### 1. Status Chung (7 trạng thái)
Tất cả module dùng chung 1 bộ trạng thái:
- `draft` → `submitted` → `in_review` → `approved` / `rejected` / `returned` / `cancelled`

### 2. Approval Steps (Tùy module)
Các nghiệp vụ đặc thù không đưa vào status, mà đưa vào `approval_steps`:
- **Leave**: `department_head_approval` → `review` → `director_approval`
- **Vehicle**: `vehicle_picked` → `director_approval`
- **MaterialPlan**: `review` → `director_approval`

### 3. Current Step
Bước hiện tại đang xử lý trong `approval_steps`

## Mapping với Module Cũ

### EmployeeLeave (Nghỉ phép)
```
Old Status → New Status + Current Step
─────────────────────────────────────────
pending → submitted + department_head_approval
approved_by_department_head → in_review + review
approved_by_reviewer → in_review + director_approval
approved_by_director → approved + null
rejected → rejected + null
```

### VehicleRegistration (Đăng ký xe)
```
Old Status → New Status + Current Step
─────────────────────────────────────────
submitted → submitted + vehicle_picked
dept_review → in_review + director_approval
director_review → in_review + director_approval
approved → approved + null
rejected → rejected + null
```

### MaterialPlan (Phương án vật tư)
```
Old Status → New Status + Current Step
─────────────────────────────────────────
pending → submitted + review
approved_by_department_head → in_review + director_approval
approved_by_reviewer → in_review + director_approval
approved → approved + null
rejected → rejected + null
```

## Lợi Ích

1. **Thống nhất**: Tất cả module dùng chung status → dễ báo cáo, thống kê
2. **Linh hoạt**: Mỗi module có thể thêm step riêng mà không ảnh hưởng status
3. **Dễ mở rộng**: Thêm module mới chỉ cần định nghĩa `approval_steps`
4. **Không phá kiến trúc**: Module cũ vẫn giữ `workflow_status`, chỉ sync với `approval_requests`

## Cách Sử Dụng

### 1. Tạo ApprovalRequest khi model được tạo
```php
use Modules\ApprovalWorkflow\Services\ApprovalRequestService;

$service = new ApprovalRequestService();
$approvalRequest = $service->syncFromModel($materialPlan, 'material_plan', [
    'title' => "Phương án vật tư: {$materialPlan->ten_khi_tai}",
]);
```

### 2. Sync khi workflow_status thay đổi
```php
// Trong MaterialPlanCrudController hoặc Observer
$service = new ApprovalRequestService();
$service->syncFromModel($materialPlan, 'material_plan');
```

### 3. Query từ ApprovalRequest
```php
// Lấy tất cả yêu cầu đang chờ phê duyệt
$requests = ApprovalRequest::where('status', 'in_review')
    ->where('current_step', 'director_approval')
    ->whereJsonContains('selected_approvers->director_approval', $userId)
    ->get();
```

## Migration Path

1. **Giai đoạn 1**: Tạo bảng `approval_requests`, giữ nguyên logic cũ
2. **Giai đoạn 2**: Sync dữ liệu từ module cũ sang `approval_requests`
3. **Giai đoạn 3**: Cập nhật ApprovalCenterService để query từ `approval_requests`
4. **Giai đoạn 4**: (Tùy chọn) Bỏ `workflow_status` ở module cũ, chỉ dùng `approval_requests`



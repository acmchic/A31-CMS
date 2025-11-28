# Hệ Thống Trạng Thái Phê Duyệt Tập Trung

## Tổng Quan

Hệ thống phê duyệt tập trung quản lý tất cả các yêu cầu phê duyệt từ các module khác nhau (Đăng ký nghỉ phép, Đăng ký xe, Quản lý sản xuất, ...) thông qua bảng `approval_requests`.

## Các Trạng Thái Workflow

### 1. **draft** - Nháp
- **Mô tả**: Yêu cầu đang được soạn thảo, chưa submit để phê duyệt
- **Hành động**: User có thể chỉnh sửa, xóa
- **Chuyển tiếp**: `pending` (khi submit)

### 2. **pending** - Chờ phê duyệt
- **Mô tả**: Yêu cầu đã được submit, chờ cấp phê duyệt đầu tiên
- **Người xử lý**: 
  - Trưởng phòng (cho đơn nghỉ phép)
  - Thẩm định (cho đơn nghỉ phép sĩ quan)
  - Đội trưởng xe (cho đăng ký xe)
- **Hành động**: Có thể phê duyệt hoặc từ chối
- **Chuyển tiếp**: 
  - `approved_by_level1` (khi phê duyệt)
  - `rejected` (khi từ chối)

### 3. **approved_by_level1** - Đã phê duyệt cấp 1
- **Mô tả**: Đã được phê duyệt ở cấp đầu tiên
- **Người xử lý**: 
  - Thẩm định (cho đơn nghỉ phép)
  - BGD (cho đăng ký xe, phương án vật tư)
- **Hành động**: Có thể phê duyệt tiếp hoặc từ chối
- **Chuyển tiếp**:
  - `approved_by_level2` (cho workflow 2-3 cấp)
  - `approved` (cho workflow 1 cấp)
  - `rejected` (khi từ chối)

### 4. **approved_by_level2** - Đã phê duyệt cấp 2
- **Mô tả**: Đã được phê duyệt ở cấp thứ hai (thường là BGD)
- **Người xử lý**: 
  - BGD (cho đơn nghỉ phép)
  - Cấp cao hơn (nếu có)
- **Hành động**: Có thể phê duyệt tiếp hoặc từ chối
- **Chuyển tiếp**:
  - `approved_by_level3` (cho workflow 3 cấp)
  - `approved` (cho workflow 2 cấp)
  - `rejected` (khi từ chối)

### 5. **approved_by_level3** - Đã phê duyệt cấp 3
- **Mô tả**: Đã được phê duyệt ở cấp thứ ba (nếu có)
- **Người xử lý**: Cấp cao nhất
- **Hành động**: Chỉ có thể phê duyệt hoàn tất
- **Chuyển tiếp**: `approved`

### 6. **approved** - Đã phê duyệt hoàn tất
- **Mô tả**: Yêu cầu đã được phê duyệt hoàn tất ở tất cả các cấp
- **Hành động**: Không thể thay đổi, chỉ xem
- **Chuyển tiếp**: Không có (trạng thái cuối)

### 7. **rejected** - Đã từ chối
- **Mô tả**: Yêu cầu đã bị từ chối ở một cấp nào đó
- **Lý do**: Lưu trong `rejection_reason`
- **Cấp từ chối**: Lưu trong `rejection_level` (level1, level2, level3)
- **Hành động**: Không thể thay đổi, chỉ xem
- **Chuyển tiếp**: Không có (trạng thái cuối)

### 8. **cancelled** - Đã hủy
- **Mô tả**: Yêu cầu đã bị hủy bởi người tạo
- **Hành động**: Chỉ người tạo mới có thể hủy
- **Chuyển tiếp**: Không có (trạng thái cuối)

## Workflow Types

### **single** - 1 cấp
```
draft -> pending -> approved/rejected
```
- Ví dụ: Đơn giản, chỉ cần 1 người phê duyệt

### **two_level** - 2 cấp
```
draft -> pending -> approved_by_level1 -> approved/rejected
```
- Ví dụ: 
  - Đăng ký xe: Đội trưởng -> BGD
  - Phương án vật tư: Thẩm định -> BGD

### **three_level** - 3 cấp
```
draft -> pending -> approved_by_level1 -> approved_by_level2 -> approved/rejected
```
- Ví dụ: Đơn nghỉ phép: Trưởng phòng -> Thẩm định -> BGD

## Mapping với Module Cũ

### Đăng ký nghỉ phép (EmployeeLeave)
- `pending` = `WORKFLOW_PENDING`
- `approved_by_level1` = `WORKFLOW_APPROVED_BY_DEPARTMENT_HEAD`
- `approved_by_level2` = `WORKFLOW_APPROVED_BY_REVIEWER`
- `approved` = `WORKFLOW_APPROVED_BY_DIRECTOR`
- `rejected` = `WORKFLOW_REJECTED`

### Đăng ký xe (VehicleRegistration)
- `pending` = `submitted`
- `approved_by_level1` = `dept_review`
- `approved_by_level2` = `director_review`
- `approved` = `approved`
- `rejected` = `rejected`

### Phương án vật tư (MaterialPlan)
- `pending` = `pending`
- `approved_by_level1` = `approved_by_department_head`
- `approved_by_level2` = `approved_by_reviewer`
- `approved` = `approved`
- `rejected` = `rejected`

## Lợi Ích của Hệ Thống Tập Trung

1. **Quản lý tập trung**: Tất cả yêu cầu phê duyệt ở một nơi
2. **Dễ mở rộng**: Thêm module mới chỉ cần tạo record trong `approval_requests`
3. **Thống nhất trạng thái**: Tất cả module dùng chung bộ trạng thái
4. **Báo cáo tập trung**: Dễ dàng thống kê, báo cáo
5. **Lịch sử đầy đủ**: Lưu đầy đủ thông tin phê duyệt ở các cấp
6. **Linh hoạt**: Hỗ trợ 1-3 cấp phê duyệt tùy module

## Cách Sử Dụng

### Tạo Approval Request từ Module

```php
use Modules\ApprovalWorkflow\Models\ApprovalRequest;

// Khi tạo MaterialPlan
$approvalRequest = ApprovalRequest::create([
    'module_type' => 'material_plan',
    'model_type' => MaterialPlan::class,
    'model_id' => $materialPlan->id,
    'created_by' => backpack_user()->id,
    'title' => "Phương án vật tư: {$materialPlan->ten_khi_tai}",
    'workflow_type' => ApprovalRequest::WORKFLOW_TWO_LEVEL,
    'status' => ApprovalRequest::STATUS_PENDING,
    'selected_approvers_level2' => $selectedApprovers, // BGD
    'metadata' => [
        'ten_khi_tai' => $materialPlan->ten_khi_tai,
        'ky_hieu_khi_tai' => $materialPlan->ky_hieu_khi_tai,
        // ... other fields
    ],
]);
```

### Sync với bảng gốc

Khi approval request thay đổi status, cần sync lại với bảng gốc:

```php
$approvalRequest->status = ApprovalRequest::STATUS_APPROVED_BY_LEVEL1;
$approvalRequest->save();

// Sync với MaterialPlan
$materialPlan = $approvalRequest->getModel();
$materialPlan->workflow_status = 'approved_by_department_head';
$materialPlan->save();
```



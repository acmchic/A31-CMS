# 🔄 Refactor Approval Module - Tóm tắt

## ✅ Đã hoàn thành

### 1. **Kiến trúc mới - Single Source of Truth**

Hệ thống approval giờ đây hoàn toàn dựa trên bảng `approval_requests` làm nguồn dữ liệu duy nhất:

- **Status chung**: `draft`, `submitted`, `in_review`, `approved`, `rejected`, `returned`, `cancelled`
- **Approval Steps**: Mỗi module có các bước phê duyệt riêng, lưu trong `approval_steps` (JSON array)
- **Current Step**: Bước hiện tại được lưu trong `current_step`
- **Selected Approvers**: Người phê duyệt cho từng bước, lưu trong `selected_approvers` (JSON object)

### 2. **Các Service mới**

#### `ApprovalWorkflowHandler`
- Service chính xử lý approve/reject
- **Chỉ làm việc với `ApprovalRequest`**, không phụ thuộc vào `workflow_status` cũ
- Logic thông minh:
  - Tự động kiểm tra có cần chọn người phê duyệt trước khi approve không
  - Tự động chuyển sang bước tiếp theo hoặc complete
  - Tự động sync lại model sau khi approve/reject

#### `ApprovalService` (Refactored)
- Wrapper service để tương thích ngược
- Method mới: `approveRequest()`, `rejectRequest()` - làm việc trực tiếp với `ApprovalRequest`
- Method cũ: `approve()`, `reject()` - vẫn hoạt động nhưng bên trong dùng `ApprovalRequest`

### 3. **Workflow cho từng Module**

#### **Leave Request (Nghỉ phép)**
```
Steps: ['department_head_approval', 'review', 'director_approval']

1. department_head_approval (Trưởng phòng duyệt)
   - Status: submitted → in_review
   - Cần PIN để ký số

2. review (Thẩm định)
   - Status: in_review
   - Không cần PIN (chỉ forward lên BGD)
   - Phải chọn người phê duyệt (directors) trước

3. director_approval (BGD duyệt)
   - Status: in_review → approved
   - Cần PIN để ký số
```

#### **Vehicle Registration (Đăng ký xe)**
```
Steps: ['vehicle_picked', 'department_head_approval', 'director_approval']

1. vehicle_picked (Phân xe)
   - Status: submitted
   - Đội trưởng xe phân công xe và tài xế

2. department_head_approval (Trưởng phòng KH duyệt)
   - Status: submitted/in_review → in_review
   - Không cần PIN (chỉ forward lên BGD)
   - Phải chọn người phê duyệt (directors) trước

3. director_approval (BGD duyệt)
   - Status: in_review → approved
   - Cần PIN để ký số
```

### 4. **Cách hoạt động**

#### **Khi approve:**
1. `ApprovalWorkflowHandler::approve()` được gọi với `ApprovalRequest`
2. Kiểm tra quyền: `$approvalRequest->canBeApprovedBy($user)`
3. Kiểm tra có cần chọn người phê duyệt không: `needsApproverSelection()`
4. Ghi lại lịch sử: `recordApproval()` → cập nhật `approval_history`
5. Chuyển bước: `moveToNextStep()` → cập nhật `current_step` và `status`
6. Sync lại model: `syncToModel()` → đảm bảo model có data mới nhất

#### **Khi reject:**
1. `ApprovalWorkflowHandler::reject()` được gọi với `ApprovalRequest`
2. Kiểm tra quyền: `$approvalRequest->canBeApprovedBy($user)`
3. Ghi lại lịch sử: `recordRejection()` → cập nhật `approval_history`
4. Cập nhật status: `status = rejected`, `rejection_step = current_step`
5. Sync lại model: `syncToModel()`

### 5. **Lợi ích**

✅ **Single Source of Truth**: Tất cả logic approval đều dựa trên `approval_requests`
✅ **Dễ mở rộng**: Thêm module mới chỉ cần định nghĩa `approval_steps` trong `ApprovalRequestService`
✅ **Dễ maintain**: Logic approval tập trung ở một nơi (`ApprovalWorkflowHandler`)
✅ **Status nhất quán**: Tất cả module dùng chung status (`submitted`, `in_review`, `approved`, `rejected`)
✅ **Tương thích ngược**: Code cũ vẫn hoạt động nhờ wrapper methods

### 6. **Files đã thay đổi**

- ✅ `Modules/ApprovalWorkflow/app/Services/ApprovalWorkflowHandler.php` (NEW)
- ✅ `Modules/ApprovalWorkflow/app/Services/ApprovalService.php` (REFACTORED)
- ✅ `Modules/ApprovalWorkflow/app/Http/Controllers/ApprovalController.php` (UPDATED)
- ✅ `Modules/ApprovalWorkflow/app/Services/ApprovalCenterService.php` (UPDATED)

### 7. **Cách sử dụng**

#### **Approve (Recommended)**
```php
$approvalRequest = ApprovalRequest::where('model_type', get_class($model))
    ->where('model_id', $model->id)
    ->first();

$approvalService = app(ApprovalService::class);
$approvalService->approveRequest($approvalRequest, $user, ['comment' => 'OK']);
```

#### **Reject (Recommended)**
```php
$approvalService->rejectRequest($approvalRequest, $user, 'Lý do từ chối');
```

#### **Approve với PIN (Digital Signature)**
```php
$approvalService->approveWithSignature($model, $user, $pin, ['comment' => 'OK']);
```

### 8. **Lưu ý**

- Model không cần cập nhật `workflow_status` nữa - tất cả đều lấy từ `approvalRequest`
- `ApprovalRequestService::syncFromModel()` vẫn được gọi để đảm bảo đồng bộ
- Badge count trong sidebar tự động cập nhật dựa trên `approval_requests.status`

---

**Ngày refactor**: 2025-11-27
**Version**: 2.0




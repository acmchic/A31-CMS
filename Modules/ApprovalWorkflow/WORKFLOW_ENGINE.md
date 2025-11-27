# 🔥 Workflow Engine - Hệ thống phê duyệt tập trung

## 📋 Tổng quan

Workflow Engine mới được xây dựng để đảm bảo:
1. **TÁCH BIỆT logic từng module** - Mỗi module có block code riêng biệt
2. **KHÔNG dùng logic suy đoán tự động** - Không có auto-next-step
3. **Mọi bước duyệt được xác định dựa trên approval_steps metadata** - Chỉ dùng để hiển thị

## 🗄️ Cấu trúc Database

### 1. Bảng `approval_flows`
Quản lý metadata workflow cho từng module:
- `id`
- `module_type` (string): 'leave', 'vehicle', 'material', ...
- `name` (string)
- `description` (nullable)

### 2. Bảng `approval_steps` ⭐ QUAN TRỌNG NHẤT
Quản lý các bước duyệt cho từng workflow:
- `id`
- `flow_id` (fk to approval_flows)
- `module_type` (string)
- `step` (string): 'department_head_approval', 'review', 'director_approval', ...
- `step_type` (string): 'approval', 'review', 'selection', 'modal', 'special'
- `order` (int): 0, 1, 2, ...
- `is_final` (boolean)
- `needs_modal` (boolean): Cần mở modal chọn người duyệt
- `metadata` (json)

⚠️ **LƯU Ý**: Tất cả step của mọi module đều nằm trong bảng này, phân biệt bằng `module_type` + `order`.

### 3. Bảng `approval_requests`
Bảng tập trung quản lý tất cả yêu cầu phê duyệt:
- `id`
- `module_type`
- `model_type`
- `model_id`
- `flow_id` (fk to approval_flows)
- `approval_steps` (json array) - **CHỈ DÙNG ĐỂ HIỂN THỊ**
- `current_step` (string)
- `current_step_index` (int)
- `selected_approvers` (json)
- `approval_history` (json)
- `status` (enum: draft, submitted, in_review, approved, rejected, returned, cancelled)
- `metadata` (json)

## 🔧 WorkflowEngine Service

### Vị trí
`app/Services/WorkflowEngine.php`

### Phương thức chính

```php
public function processApprovalStep(
    ApprovalRequest $request,
    string $action, // 'approved' | 'rejected' | 'returned' | 'cancelled'
    ?string $comment = null,
    ?array $selectedApprovers = null // [user_id1, user_id2, ...] - Dùng cho modal
): ApprovalRequest
```

### Cấu trúc xử lý

WorkflowEngine sử dụng **switch-case** để tách biệt logic từng module:

```php
switch ($request->module_type) {
    case 'leave':
        return $this->handleLeaveWorkflow($request, $action, $comment, $selectedApprovers);
    
    case 'vehicle':
        return $this->handleVehicleWorkflow($request, $action, $comment, $selectedApprovers);
    
    case 'material':
        return $this->handleMaterialWorkflow($request, $action, $comment, $selectedApprovers);
    
    default:
        throw new \Exception("Unsupported module type: {$request->module_type}");
}
```

## 📝 Logic Workflow cho từng Module

### 🟩 Module LEAVE

**Flow**: `department_head_approval` → `review` → `director_approval` → `approved`

```php
// TP duyệt → sang bước review
if ($currentStep === 'department_head_approval') {
    $request->current_step = 'review';
    $request->current_step_index = 1;
    $request->status = 'in_review';
    return $request;
}

// Review duyệt → sang bước director
if ($currentStep === 'review') {
    $request->current_step = 'director_approval';
    $request->current_step_index = 2;
    $request->status = 'in_review';
    return $request;
}

// Director duyệt → hoàn tất
if ($currentStep === 'director_approval') {
    $request->status = 'approved';
    return $request;
}
```

**Đặc điểm**:
- ✅ KHÔNG có modal
- ✅ KHÔNG được nhảy thẳng sang director
- ✅ KHÔNG được dùng logic generic

### 🟦 Module VEHICLE

**Flow**: `vehicle_picked` → `department_head_approval` → **[MODAL CHỌN BGĐ]** → `director_approval` → `approved`

```php
// vehicle_picked → sang bước trưởng phòng KH
if ($currentStep === 'vehicle_picked') {
    $request->current_step = 'department_head_approval';
    $request->current_step_index = 1;
    $request->status = 'in_review';
    return $request;
}

// TP duyệt → PHẢI mở modal chọn người BGĐ
if ($currentStep === 'department_head_approval') {
    if (empty($selectedApprovers)) {
        throw new \Exception('director selection required');
    }
    
    // Cập nhật BGĐ được chọn
    $request->selected_approvers = [
        'director_approval' => [
            'selected_by' => auth()->id(),
            'selected_at' => now(),
            'users' => $selectedApprovers
        ]
    ];
    
    // Chuyển sang bước giám đốc
    $request->current_step = 'director_approval';
    $request->current_step_index = 2;
    $request->status = 'in_review';
    return $request;
}

// Director duyệt → hoàn tất
if ($currentStep === 'director_approval') {
    $request->status = 'approved';
    return $request;
}
```

**Đặc điểm**:
- ✅ TUYỆT ĐỐI phải mở modal chọn BGĐ sau khi TP duyệt
- ✅ TUYỆT ĐỐI phải đặt `current_step = director_approval` sau khi chọn BGĐ
- ✅ KHÔNG có bước review
- ✅ KHÔNG quay lại bước cũ

## 📊 Lưu lịch sử (Approval History)

Mỗi lần duyệt được append vào `approval_history`:

```json
{
  "department_head_approval": {
    "action": "approved",
    "comment": "...",
    "approved_at": "2025-11-27T10:00:00Z",
    "approved_by": 1,
    "workflow_status_before": "in_review",
    "workflow_status_after": "in_review",
    "step_index": 0
  }
}
```

## 🚫 Những điều CẤM LÀM

Để tránh lỗi nhảy sai step, **TUYỆT ĐỐI CẤM**:

1. ❌ Dùng logic chung như `goToNextStep()`
2. ❌ Duyệt dựa vào index kế tiếp trong mảng `approval_steps`
3. ❌ Sử dụng auto skip steps
4. ❌ Tìm step tiếp theo bằng `array_search + 1`
5. ❌ Gom workflow vào 1 hàm duy nhất
6. ❌ Dùng fallback logic

**Nếu còn 1 dòng auto-next-step → workflow sẽ sai như hiện tại.**

## ✅ Kết quả kỳ vọng

### Leave Module:
1. TP duyệt → `review`
2. Review duyệt → `director_approval`
3. Director duyệt → `approved`

### Vehicle Module:
1. TP duyệt → **MỞ POPUP CHỌN BGĐ**
2. Chọn BGĐ → `director_approval`
3. Director duyệt → `approved`

## 🔄 Sử dụng

### Trong Controller:

```php
use App\Services\WorkflowEngine;
use Modules\ApprovalWorkflow\Models\ApprovalRequest;

public function approve(Request $request, $id)
{
    $approvalRequest = ApprovalRequest::findOrFail($id);
    $workflowEngine = app(WorkflowEngine::class);
    
    try {
        $approvalRequest = $workflowEngine->processApprovalStep(
            $approvalRequest,
            'approved',
            $request->comment,
            $request->selected_approvers // Cho vehicle module
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Đã phê duyệt thành công',
            'data' => $approvalRequest
        ]);
    } catch (\Exception $e) {
        if ($e->getMessage() === 'director selection required') {
            return response()->json([
                'error' => 'director selection required'
            ], 422);
        }
        
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
```

## 📦 Migration & Seeder

### Chạy migrations:

```bash
php artisan migrate --path=Modules/ApprovalWorkflow/database/migrations/2025_11_27_150000_create_approval_flows_table.php
php artisan migrate --path=Modules/ApprovalWorkflow/database/migrations/2025_11_27_150001_create_approval_steps_table.php
php artisan migrate --path=Modules/ApprovalWorkflow/database/migrations/2025_11_27_150002_add_flow_id_and_current_step_index_to_approval_requests.php
```

### Chạy seeder:

```bash
php artisan db:seed --class="Modules\ApprovalWorkflow\Database\Seeders\ApprovalFlowSeeder"
```

## 🎯 Tóm tắt

- ✅ Logic tách biệt hoàn toàn cho từng module
- ✅ Không có auto-next-step
- ✅ Mỗi bước được xử lý cứng theo yêu cầu
- ✅ Approval history được lưu đầy đủ
- ✅ Hỗ trợ modal chọn người duyệt cho vehicle module


# 📄 Signed PDF Path - Giải thích

## 🔍 Hiện trạng

### 1. **Nơi lưu trữ `signed_pdf_path`**

Hiện tại `signed_pdf_path` được lưu ở **2 nơi**:

#### ✅ **Bảng `approval_requests`** (Single Source of Truth)
- Đây là nơi chính lưu trữ `signed_pdf_path`
- Tất cả logic approval mới đều lấy từ đây

#### ⚠️ **Bảng `employee_leave`** (Backward Compatibility)
- Vẫn còn trường `signed_pdf_path` trong database
- Được giữ lại để tương thích ngược với code cũ
- Model có accessor để lấy từ `approvalRequest` trước, fallback về model field

### 2. **Logic hiện tại**

#### **Khi ký số (approveWithSignature):**
```php
// ApprovalService::approveWithSignature()
1. Approve request → ApprovalWorkflowHandler::approve()
2. Generate signed PDF → $pdfPath
3. Update ApprovalRequest:
   $approvalRequest->signed_pdf_path = $pdfPath;
   $approvalRequest->save();
4. Update Model (backward compatibility):
   if (in_array('signed_pdf_path', $model->getFillable())) {
       $model->update(['signed_pdf_path' => $pdfPath]);
   }
```

#### **Khi đọc `signed_pdf_path`:**
```php
// EmployeeLeave::getSignedPdfPathAttribute() (accessor)
1. Lấy từ approvalRequest trước:
   if ($approvalRequest && $approvalRequest->signed_pdf_path) {
       return $approvalRequest->signed_pdf_path;
   }
2. Fallback về model field (backward compatibility):
   if (isset($this->attributes['signed_pdf_path'])) {
       return $this->attributes['signed_pdf_path'];
   }
```

#### **Khi sync (ApprovalRequestService::syncFromModel):**
```php
// Sync từ model sang approvalRequest
if (isset($model->signed_pdf_path)) {
    $approvalRequest->signed_pdf_path = $model->signed_pdf_path;
}
```

## 🎯 Khuyến nghị

### **Option 1: Giữ cả 2 (Hiện tại) - Recommended**
✅ **Ưu điểm:**
- Tương thích ngược với code cũ
- An toàn khi migrate
- Có fallback nếu `approvalRequest` chưa được tạo

❌ **Nhược điểm:**
- Dữ liệu trùng lặp
- Cần sync giữa 2 nơi

### **Option 2: Chỉ dùng `approval_requests`**
✅ **Ưu điểm:**
- Single Source of Truth
- Không trùng lặp dữ liệu
- Dễ maintain

❌ **Nhược điểm:**
- Cần đảm bảo tất cả code đã dùng accessor
- Cần migrate data từ `employee_leave` sang `approval_requests`

## 📝 Cách xử lý

### **Nếu muốn xóa `signed_pdf_path` khỏi `employee_leave`:**

1. **Đảm bảo tất cả code đã dùng accessor:**
   - ✅ `EmployeeLeave::getSignedPdfPathAttribute()` - đã có
   - ✅ `EmployeeLeave::hasSignedPdf()` - đã có
   - ✅ `VehicleRegistration::getSignedPdfPathAttribute()` - đã có

2. **Migrate data cũ:**
   ```sql
   UPDATE approval_requests ar
   INNER JOIN employee_leave el ON ar.model_type = 'Modules\\PersonnelReport\\Models\\EmployeeLeave' 
       AND ar.model_id = el.id
   SET ar.signed_pdf_path = el.signed_pdf_path
   WHERE el.signed_pdf_path IS NOT NULL 
       AND (ar.signed_pdf_path IS NULL OR ar.signed_pdf_path = '');
   ```

3. **Tạo migration xóa cột:**
   ```php
   Schema::table('employee_leave', function (Blueprint $table) {
       if (Schema::hasColumn('employee_leave', 'signed_pdf_path')) {
           $table->dropColumn('signed_pdf_path');
       }
   });
   ```

4. **Xóa khỏi `$fillable` trong model:**
   ```php
   // Xóa 'signed_pdf_path' khỏi $fillable
   ```

## ✅ Kết luận

**Hiện tại:** Giữ cả 2 nơi là an toàn nhất vì:
- Code cũ vẫn hoạt động
- Code mới lấy từ `approvalRequest`
- Có fallback nếu cần

**Tương lai:** Sau khi đảm bảo tất cả code đã dùng accessor, có thể xóa khỏi `employee_leave`.



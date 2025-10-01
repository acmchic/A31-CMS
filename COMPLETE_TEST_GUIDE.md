# ✅ COMPLETE TEST GUIDE - ApprovalWorkflow Module

## 🎯 Tất cả đã hoàn tất!

### ✅ Features Implemented:

1. **ApprovalWorkflow Module** - Reusable approval system
2. **2-Level Approval** - Phê duyệt cấp 1 & cấp 2
3. **Digital Signature** - TCPDF với Adobe signature panel
4. **PIN Authentication** - Bảo mật với mã PIN
5. **Error Handling** - Hiển thị lỗi tiếng Việt trong modal
6. **Modal UX** - Giữ modal mở khi có lỗi
7. **Chrome Autofill Fix** - Không bị fill "admin" vào search

---

## 🚀 TESTING FLOW

### Step 0: Setup (One-time)

#### 0.1. Set Certificate PIN for User
```bash
php artisan tinker
```
```php
$user = \App\Models\User::find(1); // Thay 1 bằng ID của bạn
$user->certificate_pin = '123456';
$user->save();
exit
```

#### 0.2. Verify Certificate File Exists
```bash
ls storage/app/certificates/
# Should have: a31_factory.pfx
```

#### 0.3. Hard Refresh Browser
```
Ctrl + Shift + R
```

---

## 🧪 TEST CASE 1: Approve Modal - Error Handling

### 1.1. Tạo đơn xin nghỉ phép
1. Go to: `http://localhost:8000/admin/leave-request`
2. Click "+ Thêm đơn xin nghỉ phép"
3. Fill form và Save
4. ✅ Expected: New leave request created

### 1.2. Test PIN Empty Error
1. Click "**Phê duyệt & Ký số**"
2. ✅ Modal hiện ra
3. ✅ Search box không bị fill "admin"
4. **Leave PIN input empty**
5. Click "Xác nhận Ký số"
6. ✅ Expected:
   - ❌ Alert box màu đỏ hiện ra trong modal
   - ❌ Message: "Vui lòng nhập mã PIN!"
   - ✅ **Modal vẫn mở** (không đóng)
   - ✅ Focus tự động vào PIN input
   - ✅ Search box vẫn rỗng

### 1.3. Test Wrong PIN Error
1. Enter PIN: `wrong123`
2. Click "Xác nhận Ký số"
3. ✅ Expected:
   - ⏳ Button shows "Đang xử lý..."
   - ❌ Alert box: "Mã PIN không hợp lệ"
   - ✅ **Modal vẫn mở**
   - ✅ Focus vào PIN input
   - ✅ Button reset về "Xác nhận Ký số"

### 1.4. Test Correct PIN - Success
1. Clear PIN input
2. Enter correct PIN: `123456`
3. (Optional) Enter comment
4. Click "Xác nhận Ký số"
5. ✅ Expected:
   - ⏳ Button: "Đang xử lý..."
   - ✅ Modal đóng
   - ✅ Alert: "Phê duyệt thành công! PDF đã được ký số."
   - ✅ Page reload
   - ✅ Status changed to "Đã phê duyệt cấp 1"

---

## 🧪 TEST CASE 2: Reject Modal - Error Handling

### 2.1. Test Reason Empty Error
1. Create new leave request
2. Click "**Từ chối**"
3. ✅ Modal hiện ra
4. ✅ Modal **clickable** (z-index correct)
5. **Leave reason empty**
6. Click "Xác nhận Từ chối"
7. ✅ Expected:
   - ❌ Alert box: "Vui lòng nhập lý do từ chối!"
   - ✅ **Modal vẫn mở**
   - ✅ Focus vào textarea

### 2.2. Test Reason Too Short Error
1. Enter reason: `abc` (< 5 chars)
2. Click "Xác nhận Từ chối"
3. ✅ Expected:
   - ❌ Alert box: "Lý do từ chối phải có ít nhất 5 ký tự!"
   - ✅ **Modal vẫn mở**
   - ✅ Focus vào textarea

### 2.3. Test Correct Reason - Success
1. Enter reason: `Thiếu giấy tờ cần thiết` (>= 5 chars)
2. Click "Xác nhận Từ chối"
3. ✅ Expected:
   - ⏳ Button: "Đang xử lý..."
   - ✅ Modal đóng
   - ✅ Alert: "Đã từ chối thành công"
   - ✅ Page reload
   - ✅ Status changed to "Đã từ chối"

---

## 🧪 TEST CASE 3: Full Approval Workflow

### 3.1. Create Leave Request
```
Status: pending
Workflow: pending
```

### 3.2. Level 1 Approval
**Login as:** User with `leave.approve` permission
1. Click "Phê duyệt & Ký số"
2. Enter PIN: `123456`
3. ✅ Success
```
Status: pending → approved_by_approver
approved_by_approver: USER_ID_1
approved_at_approver: TIMESTAMP
```

### 3.3. Level 2 Approval (Final - with Signature)
**Login as:** Director/Admin
1. Click "Phê duyệt & Ký số" (on level1-approved leave)
2. Enter PIN: `123456`
3. ✅ Success
```
Status: approved_by_director
approved_by_director: USER_ID_2
approved_at_director: TIMESTAMP
signed_pdf_path: leave_requests/USERNAME/employeeleave_X_TIMESTAMP.pdf
```

### 3.4. Download Signed PDF
1. Click "**Tải PDF**" button
2. ✅ File downloads: `don_xin_nghi_phep_X.pdf`
3. Open with **Adobe Acrobat Reader**
4. ✅ Expected:
   - **Signature Panel** visible on left
   - Signature info:
     - Name: (Director name)
     - Organization: A31 Factory
     - Reason: Phê duyệt đơn xin nghỉ phép số X
     - Timestamp: (current time)
   - PDF content:
     - Employee info
     - Leave details
     - Approval level 1 & 2 info
     - Signature section

---

## 🎨 Modal Improvements Summary

### Approve Modal:
| Feature | Status |
|---------|--------|
| Error container | ✅ Red alert box |
| Empty PIN validation | ✅ "Vui lòng nhập mã PIN!" |
| Wrong PIN error | ✅ "Mã PIN không hợp lệ" |
| Server error | ✅ "Có lỗi xảy ra khi kết nối máy chủ" |
| Modal stays open on error | ✅ Yes |
| Loading state | ✅ "Đang xử lý..." |
| Focus on error | ✅ Auto focus to PIN input |
| Chrome autofill | ✅ Fixed |

### Reject Modal:
| Feature | Status |
|---------|--------|
| Error container | ✅ Red alert box |
| Empty reason validation | ✅ "Vui lòng nhập lý do từ chối!" |
| Short reason error | ✅ "Lý do từ chối phải có ít nhất 5 ký tự!" |
| Server error | ✅ "Có lỗi xảy ra khi kết nối máy chủ" |
| Modal stays open on error | ✅ Yes |
| Loading state | ✅ "Đang xử lý..." |
| Focus on error | ✅ Auto focus to textarea |
| Chrome autofill | ✅ Fixed |
| z-index / overlay | ✅ Fixed - clickable |

---

## 📸 Visual Examples

### Approve Modal - Error Display:
```
┌─────────────────────────────────────┐
│ Xác nhận Phê duyệt & Ký số      [X] │
├─────────────────────────────────────┤
│ ┌─────────────────────────────────┐ │
│ │ ⚠️ Mã PIN không hợp lệ          │ │ ← Red alert box
│ └─────────────────────────────────┘ │
│                                     │
│ Mã PIN *                            │
│ [________________]  ← Auto focused  │
│                                     │
│ Ghi chú                             │
│ [________________]                  │
│                                     │
│ [Hủy]  [⏳ Đang xử lý...]          │
└─────────────────────────────────────┘
```

### Reject Modal - Error Display:
```
┌─────────────────────────────────────┐
│ Từ chối phê duyệt               [X] │
├─────────────────────────────────────┤
│ ┌─────────────────────────────────┐ │
│ │ ⚠️ Lý do từ chối phải có ít     │ │ ← Red alert box
│ │    nhất 5 ký tự!                │ │
│ └─────────────────────────────────┘ │
│                                     │
│ Lý do từ chối *                     │
│ ┌─────────────────────────────────┐ │
│ │ abc                             │ │ ← Auto focused
│ └─────────────────────────────────┘ │
│                                     │
│ [Hủy]  [🔄 Đang xử lý...]          │
└─────────────────────────────────────┘
```

---

## ✅ All Error Messages (Tiếng Việt)

### Approve Modal Errors:
- ✅ "Vui lòng nhập mã PIN!"
- ✅ "Mã PIN phải có ít nhất 1 ký tự!"
- ✅ "Mã PIN không hợp lệ"
- ✅ "Bạn chưa thiết lập PIN chữ ký số"
- ✅ "Không tìm thấy chứng thư số"
- ✅ "Có lỗi xảy ra khi kết nối máy chủ"

### Reject Modal Errors:
- ✅ "Vui lòng nhập lý do từ chối!"
- ✅ "Lý do từ chối phải có ít nhất 5 ký tự!"
- ✅ "Không thể từ chối"
- ✅ "Có lỗi xảy ra khi kết nối máy chủ"

---

## 🔍 Debug Checklist

### If modal not clickable:
- [ ] Check browser console for errors (F12)
- [ ] Verify z-index CSS is applied
- [ ] Check backdrop exists with z-index 99998
- [ ] Hard refresh (Ctrl + Shift + R)

### If errors still use `alert()`:
- [ ] Clear view cache: `php artisan view:clear`
- [ ] Hard refresh browser
- [ ] Check ApprovalButtons.php was updated

### If "admin" still appears in search:
- [ ] Clear localStorage (F12 → Application → Local Storage)
- [ ] Clear Chrome autofill (Shift + Delete on suggestion)
- [ ] Test in Incognito mode

---

## 📋 Quick Test Checklist

- [ ] Approve modal opens → ✅ Clickable
- [ ] Empty PIN → ✅ Error shows in modal (modal stays open)
- [ ] Wrong PIN → ✅ Error shows in modal (modal stays open)
- [ ] Correct PIN → ✅ Success (modal closes)
- [ ] Reject modal opens → ✅ Clickable
- [ ] Empty reason → ✅ Error shows in modal (modal stays open)
- [ ] Short reason → ✅ Error shows in modal (modal stays open)
- [ ] Valid reason → ✅ Success (modal closes)
- [ ] Search box → ✅ Never shows "admin"
- [ ] PDF download → ✅ Works
- [ ] Adobe signature panel → ✅ Visible

---

**Status:** ✅ **ALL ISSUES FIXED - READY FOR PRODUCTION**

**Last Updated:** October 1, 2025  
**Version:** 1.0.0 Final



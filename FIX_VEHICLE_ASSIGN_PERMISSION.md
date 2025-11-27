# 🔧 Fix lỗi không có quyền truy cập trang phân xe

## Vấn đề
Truy cập `http://a31.local/vehicle-registration/2/assign-vehicle` bị báo không có quyền dù đã set role.

## Nguyên nhân
Route yêu cầu permission `vehicle_registration.assign` nhưng:
1. Permission chưa được tạo trong database
2. User chưa được gán permission này (trực tiếp hoặc qua role)

## Giải pháp

### Bước 1: Chạy seeder để tạo permissions

```bash
php artisan db:seed --class=Database\\Seeders\\VehicleRegistrationPermissionSeeder
```

Seeder này sẽ:
- Tạo permission `vehicle_registration.assign`
- Gán permission cho role `doi_truong_xe` (Đội trưởng xe)
- Gán tất cả permissions cho role `admin`

### Bước 2: Kiểm tra permission của user

```bash
php artisan user:check-permission {email} vehicle_registration.assign
```

Ví dụ:
```bash
php artisan user:check-permission doixe@example.com vehicle_registration.assign
```

Command này sẽ:
- Hiển thị thông tin user (name, email, roles)
- Kiểm tra xem user có permission `vehicle_registration.assign` không
- Hiển thị tất cả permissions của user
- Nếu chưa có, hỏi có muốn gán không

### Bước 3: Gán permission trực tiếp cho user (nếu cần)

```bash
php artisan vehicle:grant-assign-permission {email}
```

Ví dụ:
```bash
php artisan vehicle:grant-assign-permission doixe@example.com
```

Command này sẽ:
- Tạo permission nếu chưa có
- Gán permission `vehicle_registration.assign` cho user
- Clear permission cache
- Hiển thị thông tin user và permission

### Bước 4: Gán permission qua role (khuyến nghị)

Nếu user thuộc role `doi_truong_xe`, permission sẽ tự động có sau khi chạy seeder.

Để gán role cho user:
1. Vào admin panel: `/admin/user/{id}/edit`
2. Chọn role `doi_truong_xe` (Đội trưởng xe)
3. Save

Hoặc dùng command:
```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'doixe@example.com')->first();
$role = \Spatie\Permission\Models\Role::where('name', 'doi_truong_xe')->first();
$user->assignRole($role);
```

## Kiểm tra nhanh

Sau khi gán permission, clear cache và kiểm tra:

```bash
php artisan permission:cache-reset
php artisan user:check-permission {email} vehicle_registration.assign
```

## Các permissions liên quan

- `vehicle_registration.view` - Xem danh sách
- `vehicle_registration.create` - Tạo mới
- `vehicle_registration.edit` - Sửa
- `vehicle_registration.delete` - Xóa
- `vehicle_registration.assign` - **Phân công xe (Đội trưởng xe)** ⭐
- `vehicle_registration.approve` - Phê duyệt (Ban Giám Đốc)
- `vehicle_registration.reject` - Từ chối
- `vehicle_registration.download_pdf` - Tải PDF

## Roles và permissions mặc định

Sau khi chạy seeder:

- **Admin**: Tất cả permissions
- **Ban Giám Đốc**: view, approve, reject, download_pdf
- **Đội trưởng xe**: view, assign, edit ⭐
- **Nhân viên**: view, create, edit (own records)


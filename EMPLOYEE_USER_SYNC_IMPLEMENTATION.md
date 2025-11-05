# Triển khai Employee-User Sync - Single Source of Truth

## Tổng quan

Đã triển khai giải pháp để **Employee làm Single Source of Truth** cho thông tin nhân viên, trong khi **Users quản lý thông tin tài khoản** (signature, photo, certificate).

## Cấu trúc Database

### Bảng `employees` (Single Source of Truth)
- Chứa thông tin nhân viên: `name`, `department_id`, `position_id`, `phone`, `CCCD`, ...
- Khi update → tự động sync sang `users`

### Bảng `users` (Account Information)
- Thông tin đăng nhập: `username`, `password`, `email`
- Thông tin tài khoản: `profile_photo_path`, `signature_path`, `certificate_path`, `certificate_pin`
- Link đến Employee: `employee_id` (foreign key)
- Cache fields (được sync): `name`, `department_id` (nullable)

## Các thay đổi đã thực hiện

### 1. Migrations

#### `2025_11_04_080734_ensure_employee_user_relationship_structure.php`
- Đảm bảo `employee_id` có foreign key constraint
- Đảm bảo `name` và `department_id` có thể nullable
- Thêm index cho `employee_id` để tối ưu performance

#### `2025_11_04_080759_sync_employee_data_to_users.php`
- Sync dữ liệu hiện tại từ Employee sang User
- Chạy một lần để đồng bộ dữ liệu cũ

### 2. Employee Model (`Modules/OrganizationStructure/app/Models/Employee.php`)
- Thêm `boot()` method với Model Events:
  - `updated` event → tự động sync sang User
  - `created` event → sync nếu có user liên kết
- Thêm method `syncToUser()` để sync `name` và `department_id`

### 3. User Model (`app/Models/User.php`)
- Thêm `boot()` method với Model Events:
  - `created` event → tự động sync từ Employee nếu có `employee_id`
  - `updated` event → sync khi `employee_id` thay đổi
- Thêm method `syncFromEmployee()` để sync dữ liệu
- Cập nhật `getDepartment()` để ưu tiên lấy từ Employee
- Thêm `getDisplayNameAttribute()` để lấy tên từ Employee (fallback về User)

### 4. UserCrudController (`app/Http/Controllers/Admin/UserCrudController.php`)
- Thêm sync sau khi tạo/cập nhật User
- Đảm bảo dữ liệu được sync ngay sau khi tạo/cập nhật

## Cách hoạt động

### Khi tạo User mới:
1. User được tạo với `employee_id`
2. Model Event `created` trigger → gọi `syncFromEmployee()`
3. `name` và `department_id` được sync từ Employee sang User

### Khi update Employee:
1. Employee được update (ví dụ: `name`, `department_id`)
2. Model Event `updated` trigger → gọi `syncToUser()`
3. `name` và `department_id` được sync sang User tương ứng
4. Các thông tin account (signature, photo, certificate) không bị ảnh hưởng

### Khi update User:
- Thông tin account (signature, photo, certificate) được update bình thường
- Nếu `employee_id` thay đổi → tự động sync từ Employee mới

## Lợi ích

1. ✅ **Single Source of Truth**: Employee là nguồn dữ liệu chính
2. ✅ **Tự động sync**: Không cần thao tác thủ công
3. ✅ **Tối ưu performance**: Cache fields trong User để tránh join
4. ✅ **Backward compatible**: Code hiện tại vẫn hoạt động
5. ✅ **An toàn**: Sử dụng `updateQuietly()` để tránh event loop

## Cách sử dụng

### Tạo User mới:
```php
$user = User::create([
    'employee_id' => 1,
    'username' => 'user123',
    'password' => Hash::make('password'),
    // name và department_id sẽ tự động sync từ Employee
]);
```

### Update Employee:
```php
$employee = Employee::find(1);
$employee->update([
    'name' => 'Tên mới',
    'department_id' => 2,
]);
// User tương ứng sẽ tự động được sync
```

### Lấy thông tin:
```php
$user = User::find(1);

// Lấy name (tự động từ Employee nếu có)
$name = $user->name; // Hoặc $user->display_name

// Lấy department (ưu tiên từ Employee)
$department = $user->getDepartment();
```

## Lưu ý quan trọng

1. **employee_id**: Nên được set khi tạo User (khuyến nghị bắt buộc)
2. **Sync tự động**: Không cần gọi thủ công, Model Events sẽ tự động xử lý
3. **Thông tin account**: Signature, photo, certificate không bị ảnh hưởng khi sync
4. **Migration**: Chạy migrations theo thứ tự để đảm bảo cấu trúc DB đúng

## Chạy migrations

```bash
php artisan migrate
```

Migration sẽ:
1. Đảm bảo cấu trúc DB đúng (foreign key, nullable fields)
2. Sync dữ liệu hiện tại từ Employee sang User


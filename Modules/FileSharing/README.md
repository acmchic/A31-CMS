# Module: FileSharing

Module chia sẻ file với hệ thống phân quyền cho hệ thống A31 CMS.

## 🎯 Tính năng

- ✅ Upload file với kích thước tối đa 50MB
- ✅ Hệ thống phân quyền chi tiết (public/private, roles, users)
- ✅ Quản lý danh mục file
- ✅ Tags cho file
- ✅ Thời gian hết hạn file
- ✅ Theo dõi số lần download
- ✅ Giao diện thân thiện với Backpack CRUD
- ✅ Download file với kiểm tra quyền
- ✅ Tổ chức thư mục, tạo và upload file vào thư mục

## 📦 Cài đặt

### 1. Chạy migration

```bash
php artisan migrate
```

### 2. Chạy seeder permissions

```bash
php artisan db:seed --class="Modules\FileSharing\Database\Seeders\FileSharingPermissionSeeder"
```

### 3. Enable module

Module đã được tự động enable khi tạo. Kiểm tra bằng:

```bash
php artisan module:list
```

## 🚀 Sử dụng

### 1. Truy cập module

- **Trang chính**: `/admin/file-sharing`
- **Upload file**: `/admin/file-sharing/create`
- **CRUD Management**: `/admin/shared-file`

### 2. Permissions

Module sử dụng các permissions sau:

```php
// Module permissions
'file_sharing.view'           // Xem trang chính
'file_sharing.create'         // Upload file
'file_sharing.update'         // Sửa file
'file_sharing.delete'         // Xóa file

// CRUD permissions
'shared_file.view'           // Xem danh sách file
'shared_file.create'         // Tạo file mới
'shared_file.update'         // Sửa file
'shared_file.delete'         // Xóa file
```

### 3. Quyền truy cập file

- **Công khai**: Tất cả người dùng đều có thể download
- **Riêng tư**: Chỉ người được chỉ định mới có thể download
  - Theo role
  - Theo user cụ thể
  - Người upload luôn có quyền

### 4. Định dạng file hỗ trợ

- **Tài liệu**: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT
- **Hình ảnh**: JPG, JPEG, PNG, GIF
- **Video**: MP4, AVI
- **Âm thanh**: MP3
- **Nén**: ZIP, RAR

## 📁 Cấu trúc Module

```
Modules/FileSharing/
├── app/
│   ├── Http/Controllers/
│   │   ├── FileSharingController.php           # Controller chính
│   │   └── Admin/
│   │       └── SharedFileCrudController.php   # CRUD Controller
│   ├── Models/
│   │   └── SharedFile.php                      # Model file
│   └── Providers/
│       ├── FileSharingServiceProvider.php       # Service Provider
│       └── RouteServiceProvider.php           # Route Provider
├── database/
│   ├── migrations/
│   │   └── 2025_01_27_000001_create_shared_files_table.php
│   └── seeders/
│       └── FileSharingPermissionSeeder.php    # Permission seeder
├── resources/views/
│   ├── index.blade.php                         # Trang chính
│   ├── create.blade.php                        # Upload form
│   ├── show.blade.php                          # Chi tiết file
│   └── crud/buttons/
│       └── download.blade.php                  # Download button
├── routes/
│   ├── web.php                                 # Web routes
│   └── backpack/custom.php                     # Backpack routes
├── config/
│   └── config.php                              # Module config
├── composer.json
├── module.json
└── README.md
```

## 🔧 Cấu hình

File cấu hình: `Modules/FileSharing/config/config.php`

```php
return [
    'name' => 'FileSharing',
    'max_file_size' => 51200, // 50MB in KB
    'allowed_extensions' => [...],
    'storage_disk' => 'local',
    'storage_path' => 'shared_files',
    'categories' => [...],
];
```

## 📊 Database Schema

### Bảng: shared_files

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | bigint | Primary key |
| original_name | string | Tên file gốc |
| file_name | string | Tên file đã lưu |
| file_path | string | Đường dẫn file |
| file_extension | string | Phần mở rộng |
| file_size | bigint | Kích thước (bytes) |
| mime_type | string | Loại MIME |
| description | text | Mô tả file |
| category | string | Danh mục |
| tags | json | Tags |
| is_public | boolean | File công khai |
| allowed_roles | json | Roles được phép |
| allowed_users | json | Users được phép |
| download_count | integer | Số lần download |
| expires_at | timestamp | Thời gian hết hạn |
| uploaded_by | bigint | Người upload |
| created_at | timestamp | Ngày tạo |
| updated_at | timestamp | Ngày cập nhật |

## 🛡️ Bảo mật

- Kiểm tra quyền trước khi download
- File được lưu với tên unique
- Kiểm tra file tồn tại trước khi download
- Hỗ trợ thời gian hết hạn
- Phân quyền chi tiết theo role và user

## 📈 Thống kê

Module cung cấp các thống kê:
- Tổng số file
- File của user hiện tại
- File công khai
- Dung lượng đã sử dụng
- Số lần download

## 🔄 Tích hợp

Module tích hợp với:
- **Backpack CRUD**: Giao diện quản lý
- **Spatie Permission**: Hệ thống phân quyền
- **Laravel Storage**: Lưu trữ file
- **Carbon**: Xử lý thời gian

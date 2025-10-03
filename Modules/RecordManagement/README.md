# Module RecordManagement

Quản lý các loại sổ sách trong đơn vị quân đội.

## Kiến trúc

**Mỗi loại sổ = 1 Controller + 1 Model + 1 Table riêng**

```
Modules/RecordManagement/
├── app/
│   ├── Models/
│   │   ├── SalaryUpRecord.php         // Sổ nâng lương
│   │   ├── PersonnelRecord.php        // Sổ quân nhân (TODO)
│   │   └── DisciplineRecord.php       // Sổ vi phạm (TODO)
│   └── Http/Controllers/Admin/
│       ├── SalaryUpRecordCrudController.php
│       ├── PersonnelRecordCrudController.php (TODO)
│       └── DisciplineRecordCrudController.php (TODO)
├── database/migrations/
│   ├── xxxx_create_salary_up_records_table.php
│   ├── xxxx_create_personnel_records_table.php (TODO)
│   └── xxxx_create_discipline_records_table.php (TODO)
└── routes/backpack/custom.php
```

## Đã hoàn thành

### ✅ Sổ nâng lương (SalaryUpRecord)

**Table**: `salary_up_records`

**Fields**: 19 cột dữ liệu
- Thông tin cơ bản: employee_id, department_id, year
- Thông tin cá nhân: ho_ten, nhap_ngu, chuc_vu
- Lương hiện hưởng: 7 fields
- Xếp lương mới: 7 fields  
- Thông tin khác: don_vi, ghi_chu

**URL**: `/admin/salary-up-record`

**Permissions**:
- `salary_up_record.view` - Xem danh sách
- `salary_up_record.create` - Tạo mới
- `salary_up_record.edit` - Chỉnh sửa
- `salary_up_record.delete` - Xóa

## Cách sử dụng

### 1. Truy cập

```
http://localhost:8000/admin/salary-up-record
```

### 2. Thêm bản ghi mới

- Click "Add Sổ nâng lương"
- Điền thông tin:
  - **Họ và tên** (bắt buộc)
  - Nhân sự liên kết (tùy chọn)
  - Phòng ban (tùy chọn)
  - Năm
  - Thông tin lương hiện hưởng
  - Thông tin lương mới
  - Đơn vị, Ghi chú
- Click "Save"

### 3. Xem danh sách

Bảng hiển thị:
- STT
- Họ và tên
- Nhập ngũ
- Lương hiện (Loại nhóm, Bậc, Hệ số)
- Lương mới (Loại nhóm, Bậc, Hệ số)
- Đơn vị
- Năm
- Ngày tạo

## Thêm loại sổ mới

Để thêm loại sổ mới (ví dụ: Sổ quân nhân):

### 1. Tạo Model

```php
// Modules/RecordManagement/app/Models/PersonnelRecord.php
namespace Modules\RecordManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class PersonnelRecord extends Model
{
    use CrudTrait;
    
    protected $fillable = [...]; // Các fields của sổ
}
```

### 2. Tạo Migration

```bash
php artisan make:migration create_personnel_records_table --path=Modules/RecordManagement/database/migrations
```

### 3. Tạo Controller

```php
// Modules/RecordManagement/app/Http/Controllers/Admin/PersonnelRecordCrudController.php
namespace Modules\RecordManagement\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

class PersonnelRecordCrudController extends CrudController
{
    // Tương tự SalaryUpRecordCrudController
}
```

### 4. Thêm Route

```php
// Modules/RecordManagement/routes/backpack/custom.php
Route::crud('personnel-record', 'PersonnelRecordCrudController');
```

### 5. Chạy Migration

```bash
php artisan migrate --path=Modules/RecordManagement/database/migrations
```

### 6. Thêm Permissions

```php
// database/seeders/RecordManagementPermissionSeeder.php
'personnel_record.view',
'personnel_record.create',
'personnel_record.edit',
'personnel_record.delete',
```

## Ưu điểm của kiến trúc này

✅ **Đơn giản, dễ hiểu**
- Mỗi sổ là 1 bảng riêng
- Code rõ ràng, không phức tạp

✅ **Performance tốt**
- Query nhanh
- Index hiệu quả
- Sort, filter dễ dàng

✅ **Type safety**
- Validate ở database level
- Rõ ràng về kiểu dữ liệu

✅ **Dễ mở rộng**
- Thêm sổ mới không ảnh hưởng sổ cũ
- Mỗi controller độc lập

✅ **Dễ bảo trì**
- Code không lặp nhiều nhờ extend CrudController
- Mỗi loại sổ có logic riêng

## Các loại sổ TODO

- [ ] Sổ quân nhân (PersonnelRecord)
- [ ] Sổ vi phạm kỷ luật (DisciplineRecord)
- [ ] Sổ điều động (TransferRecord)
- [ ] Sổ khen thưởng (AwardRecord)
- [ ] Sổ đào tạo (TrainingRecord)

## API Endpoints

```
GET    /admin/salary-up-record           - Danh sách
GET    /admin/salary-up-record/create    - Form tạo mới
POST   /admin/salary-up-record           - Lưu
GET    /admin/salary-up-record/{id}      - Xem chi tiết
GET    /admin/salary-up-record/{id}/edit - Form sửa
PUT    /admin/salary-up-record/{id}      - Cập nhật
DELETE /admin/salary-up-record/{id}      - Xóa
```

## Version

- **v1.0** - Sổ nâng lương (2025-10-02)

## Author

AI Assistant + Human Developer


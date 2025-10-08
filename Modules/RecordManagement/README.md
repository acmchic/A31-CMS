# Module: RecordManagement

Module quản lý các loại sổ sách trong hệ thống, sử dụng Backpack CRUD.

## 1. Cấu trúc Module

```
Modules/RecordManagement/
├── app/
│   ├── Http/Controllers/Admin/
│   │   ├── QuanNhanRecordCrudController.php    # CRUD Sổ Danh sách Quân nhân
│   │   ├── SalaryUpRecordCrudController.php    # CRUD Sổ nâng lương
│   │   └── RecordManagementController.php      # Controller trang chính /record-management
│   └── Models/
│       ├── QuanNhanRecord.php                  # Model records_quan_nhan
│       └── SalaryUpRecord.php                  # Model salary_up_records
├── resources/views/
│   └── index.blade.php                         # Trang chính hiển thị cards
└── routes/backpack/custom.php                  # Định nghĩa routes CRUD
```

## 2. Sổ sách hiện có

### 2.1. Sổ Danh sách Quân nhân
- **Table**: `records_quan_nhan`
- **Route**: `/quan-nhan-record`
- **Controller**: `QuanNhanRecordCrudController`
- **Mục đích**: Lưu thông tin quân sự, chính trị, học vấn, gia đình của quân nhân
- **Liên kết**: `employee_id` → `employees`, `department_id` → `departments`

### 2.2. Sổ nâng lương
- **Table**: `salary_up_records`
- **Route**: `/salary-up-record`
- **Controller**: `SalaryUpRecordCrudController`
- **Mục đích**: Quản lý nâng lương, thăng cấp, chuyển nhóm

## 3. Phân quyền

### 3.1. Permissions cần có:
```php
// Module chung
'record_management.view'           // Xem trang /record-management

// Sổ Danh sách Quân nhân
'quan_nhan_record.view'           // Xem danh sách
'quan_nhan_record.create'         // Tạo mới
'quan_nhan_record.update'         // Sửa
'quan_nhan_record.delete'         // Xóa

// Sổ nâng lương
'salary_up_record.view'           // Xem danh sách
'salary_up_record.create'         // Tạo mới
'salary_up_record.update'         // Sửa
'salary_up_record.delete'         // Xóa
```

### 3.2. Kiểm tra quyền trong Controller:
```php
protected function setupListOperation()
{
    if (!PermissionHelper::userCan('record_management.view')) {
        abort(403, 'Không có quyền truy cập');
    }
    // ... setup columns
}
```

## 4. CRUD Operations

### 4.1. List Operation (setupListOperation)
```php
// Hiển thị columns từ bảng chính và related tables
CRUD::column('employee.name')->label('Họ tên')->priority(1);
CRUD::column('department_name')
    ->label('Phòng ban')
    ->type('closure')
    ->function(function($entry) {
        return $entry->department ? $entry->department->name : 'Chưa có';
    });
```

### 4.2. Create/Update Operations
```php
// Hiển thị thông tin employee (readonly)
CRUD::addField([
    'name' => 'display_ho_ten',
    'label' => 'Họ đệm khai sinh',
    'type' => 'text',
    'fake' => true,
    'store_in' => false,
    'readonly' => true,
]);

// Fields mới của record book
CRUD::addField([
    'name' => 'cap_bac',
    'label' => 'Cấp bậc',
    'type' => 'text',
]);
```

### 4.3. Show Operation
```php
// Mirror layout của edit form, tất cả fields readonly
CRUD::column('emp_ho_ten')->label('Họ tên đệm khai sinh')->type('text')->value($employee->name);
```

## 5. Search Functionality

### 5.1. Search Logic cho từng column:
```php
CRUD::column('employee.name')->searchLogic(function ($query, $column, $searchTerm) {
    $query->orWhereHas('employee', function ($q) use ($searchTerm) {
        $q->where('name', 'like', '%'.$searchTerm.'%');
    });
});
```

### 5.2. Columns có search:
- `employee.name` (Họ tên)
- `department.name` (Phòng ban)
- `employee.position.name` (Chức vụ)
- `employee.rank_code` (Cấp bậc)

## 6. Thêm Sổ sách mới

### 6.1. Tạo Migration:
```bash
php artisan make:migration create_records_new_type_table
```

### 6.2. Tạo Model:
```php
// Modules/RecordManagement/app/Models/NewTypeRecord.php
class NewTypeRecord extends Model
{
    protected $table = 'records_new_type';
    protected $fillable = ['employee_id', 'department_id', 'field1', 'field2'];
    
    public function employee() {
        return $this->belongsTo(Employee::class);
    }
    
    public function department() {
        return $this->belongsTo(Department::class);
    }
}
```

### 6.3. Tạo CRUD Controller:
```php
// Modules/RecordManagement/app/Http/Controllers/Admin/NewTypeRecordCrudController.php
class NewTypeRecordCrudController extends CrudController
{
    use ListOperation, CreateOperation, UpdateOperation, DeleteOperation, ShowOperation;
    
    public function setup()
    {
        CRUD::setModel(NewTypeRecord::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/new-type-record');
        CRUD::setEntityNameStrings('sổ mới', 'sổ mới');
    }
    
    protected function setupListOperation()
    {
        if (!PermissionHelper::userCan('new_type_record.view')) {
            abort(403, 'Không có quyền truy cập');
        }
        
        CRUD::column('employee.name')->label('Họ tên')->priority(1);
        // ... thêm columns khác
    }
}
```

### 6.4. Thêm Routes:
```php
// Modules/RecordManagement/routes/backpack/custom.php
Route::crud('new-type-record', 'NewTypeRecordCrudController');
```

### 6.5. Thêm vào trang chính:
```php
// Modules/RecordManagement/app/Http/Controllers/RecordManagementController.php
$recordTypes = [
    [
        'label' => 'Sổ mới',
        'icon' => 'la la-book',
        'color' => 'primary',
        'route' => backpack_url('new-type-record'),
        'count' => NewTypeRecord::count(),
    ],
    // ... existing records
];
```

### 6.6. Tạo Permissions:
```php
// Thêm vào seeder hoặc tạo thủ công
Permission::create(['name' => 'new_type_record.view']);
Permission::create(['name' => 'new_type_record.create']);
Permission::create(['name' => 'new_type_record.update']);
Permission::create(['name' => 'new_type_record.delete']);
```

## 7. Patterns chung

### 7.1. Auto-fill employee data:
```php
// Trong Model boot()
protected static function boot()
{
    parent::boot();
    
    static::creating(function ($model) {
        if ($model->employee_id && !$model->department_id) {
            $employee = Employee::find($model->employee_id);
            if ($employee) {
                $model->department_id = $employee->department_id;
            }
        }
    });
}
```

### 7.2. API endpoints cho auto-fill:
```php
// Trong Controller
public function getEmployeesByDepartment($departmentId)
{
    $employees = Employee::where('department_id', $departmentId)
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name']);
    
    return response()->json($employees);
}

public function getEmployeeInfo($employeeId)
{
    $employee = Employee::with('department', 'position')->find($employeeId);
    return response()->json($employee);
}
```

### 7.3. JavaScript auto-fill:
```javascript
// Trong view create/edit
function loadEmployeeInfo(employeeId) {
    fetch(`/api/employee-info/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            document.querySelector('[name="display_ho_ten"]').value = data.name;
            // ... fill other fields
        });
}
```

## 8. Lưu ý quan trọng

1. **Luôn kiểm tra quyền** ở đầu mỗi operation
2. **Sử dụng relationships** để hiển thị dữ liệu từ bảng khác
3. **Search logic** phải handle relationships đúng cách
4. **Auto-fill** employee data để tránh duplicate
5. **Show operation** phải mirror edit form layout
6. **Permissions** phải được tạo và assign cho roles
7. **Routes** phải được define trong custom.php
8. **Seeder** để populate dữ liệu ban đầu nếu cần

## 9. ⚠️ QUAN TRỌNG: KHI THÊM SỔ MỚI

### 9.1. TUYỆT ĐỐI KHÔNG ĐƯỢC:
- ❌ **Sửa đổi bảng cũ**: Không được ALTER TABLE của sổ cũ
- ❌ **Xóa dữ liệu cũ**: Không được DROP hoặc TRUNCATE bảng cũ
- ❌ **Thay đổi cấu trúc**: Không được sửa migration cũ
- ❌ **Động chạm relationships**: Không được sửa foreign keys cũ

### 9.2. CHỈ ĐƯỢC LÀM:
- ✅ **Tạo bảng mới**: Chỉ tạo migration cho bảng mới
- ✅ **Tạo model mới**: Model riêng biệt cho sổ mới
- ✅ **Tạo controller mới**: CRUD controller riêng
- ✅ **Thêm routes mới**: Không sửa routes cũ
- ✅ **Thêm permissions mới**: Không sửa permissions cũ

### 9.3. PATTERN AN TOÀN:
```php
// ✅ ĐÚNG: Tạo bảng mới hoàn toàn
Schema::create('records_new_type', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('employee_id');
    $table->unsignedBigInteger('department_id');
    $table->string('new_field1');
    $table->string('new_field2');
    $table->timestamps();
    
    $table->foreign('employee_id')->references('id')->on('employees');
    $table->foreign('department_id')->references('id')->on('departments');
});

// ❌ SAI: Sửa bảng cũ
// Schema::table('records_quan_nhan', function (Blueprint $table) {
//     $table->string('new_field'); // KHÔNG ĐƯỢC LÀM
// });
```

### 9.4. NGUYÊN TẮC VÀNG:
> **"MỖI SỔ SÁCH LÀ MỘT HỆ THỐNG RIÊNG BIỆT"**
> 
> - Mỗi sổ có bảng riêng
> - Mỗi sổ có model riêng  
> - Mỗi sổ có controller riêng
> - Mỗi sổ có permissions riêng
> - Mỗi sổ có routes riêng
> 
> **KHÔNG BAO GIỜ** động chạm đến code/data của sổ khác!

### 9.5. CHECKLIST KHI THÊM SỔ MỚI:
- [ ] Tạo migration mới (không sửa cũ)
- [ ] Tạo model mới (không sửa cũ)
- [ ] Tạo controller mới (không sửa cũ)
- [ ] Thêm routes mới (không sửa cũ)
- [ ] Tạo permissions mới (không sửa cũ)
- [ ] Thêm vào RecordManagementController (chỉ thêm, không sửa)
- [ ] Test sổ mới hoạt động độc lập
- [ ] Verify sổ cũ vẫn hoạt động bình thường
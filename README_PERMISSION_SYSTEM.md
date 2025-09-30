# 🎯 A31 CMS - Clean Permission System Guide

## 📋 System Overview

Hệ thống phân quyền **CLEAN, SIMPLE, SCALABLE** cho Laravel Backpack - không hardcode, dễ mở rộng.

## 🏗️ Permission Structure

### Pattern: `module.action[.scope]`
```
Examples:
- user.view, user.create, user.edit, user.delete
- department.view, department.create, department.edit
- vehicle.view, vehicle.create, vehicle.approve
```

### Data Scopes:
- `own`: Chỉ dữ liệu cá nhân
- `department`: Dữ liệu phòng ban
- `company`: Dữ liệu toàn công ty  
- `all`: Tất cả dữ liệu (admin)

## 👥 4 Roles Chuẩn

```
Admin (Quản trị viên): ALL permissions
Ban Giam Doc (Ban Giám Đốc): company scope + approve
Truong Phong (Trưởng Phòng): department scope + manage
Nhan Vien (Nhân Viên): own scope + basic view
```

## 🛠️ Implementation Guide

### 1. PermissionHelper Usage:
```php
// Simple permission check
PermissionHelper::can($user, 'module.action')
PermissionHelper::userCan('module.action')

// Data scope check  
PermissionHelper::getUserScope($user) // 'own'|'department'|'company'|'all'

// Module access
PermissionHelper::canAccessModule($user, 'module_name')
```

### 2. Controller Pattern:
```php
use App\Helpers\PermissionHelper;

public function setup() {
    // Apply data filtering based on scope
    $scope = PermissionHelper::getUserScope(backpack_user());
    // Apply filtering logic
}

private function setupButtons() {
    if (!PermissionHelper::userCan('module.create')) {
        CRUD::removeButton('create');
    }
}
```

### 3. Route Pattern:
```php
Route::group(['middleware' => 'permission:module.view'], function () {
    Route::crud('module', ModuleController::class);
});
```

### 4. Menu Pattern:
```blade
@if(\App\Helpers\PermissionHelper::userCan('module.view'))
<a class="dropdown-item" href="{{ backpack_url('module') }}">
    <i class="la la-icon"></i> Module Name
</a>
@endif
```

## 📦 Adding New Module

### Step 1: Generate Module
```bash
php artisan module:make ModuleName
```

### Step 2: Add to Permission Seeder
```php
// In CleanPermissionSeeder.php
'module_key' => 'Module Display Name'
```

### Step 3: Update Routes
```php
// In Module/routes/web.php
Route::group(['middleware' => 'permission:module.view'], function () {
    Route::crud('module', ModuleController::class);
});
```

### Step 4: Controller Setup
```php
use App\Helpers\PermissionHelper;

public function setup() {
    $scope = PermissionHelper::getUserScope(backpack_user());
    // Apply data filtering
}
```

### Step 5: Add to Menu
```php
// In menu_items.blade.php
@if(\App\Helpers\PermissionHelper::userCan('module.view'))
// Module menu item
@endif
```

## ⚠️ Important Rules

1. **NEVER hardcode role names** in controllers/views
2. **ALWAYS use PermissionHelper** for checks
3. **Follow module.action pattern** for permissions
4. **Use data scopes** instead of manual filtering
5. **Vietnamese display names** in UI, English in code

## 🚀 Benefits

- ✅ Zero hardcode
- ✅ Scalable for new modules  
- ✅ Consistent pattern
- ✅ Easy maintenance
- ✅ Vietnamese UI

---
**Follow this guide để đảm bảo consistency across all modules!** 🎯


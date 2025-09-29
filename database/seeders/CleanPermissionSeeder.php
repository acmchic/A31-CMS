<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;

class CleanPermissionSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('🧹 Cleaning database and creating simple permission system...');
        
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Clean slate - truncate all permission/role tables
        $this->command->warn('Truncating existing roles and permissions...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define modules and their CRUD permissions
        $modules = [
            'dashboard' => 'Bảng điều khiển',
            'user' => 'Người dùng', 
            'role' => 'Vai trò',
            'permission' => 'Quyền hạn',
            'department' => 'Phòng ban',
            'employee' => 'Nhân viên',
            'report' => 'Báo cáo quân số',
            'leave' => 'Đơn nghỉ phép',
            'profile' => 'Thông tin cá nhân'
        ];

        // CRUD actions
        $actions = [
            'view' => 'Xem',
            'create' => 'Tạo',
            'edit' => 'Sửa', 
            'delete' => 'Xóa',
            'approve' => 'Phê duyệt'
        ];

        // Data scopes
        $scopes = [
            'own' => 'cá nhân',
            'department' => 'phòng ban', 
            'company' => 'công ty',
            'all' => 'tất cả'
        ];

        $this->command->info('📦 Creating module-based permissions...');
        
        $permissionId = 1;
        $allPermissions = [];

        foreach ($modules as $moduleKey => $moduleName) {
            $this->command->line("Module: {$moduleName}");
            
            foreach ($actions as $actionKey => $actionName) {
                // Skip approve for non-approvable modules
                if ($actionKey === 'approve' && !in_array($moduleKey, ['leave', 'report', 'employee', 'department'])) {
                    continue;
                }
                
                // Skip create/edit/delete for dashboard and profile
                if (in_array($moduleKey, ['dashboard']) && in_array($actionKey, ['create', 'edit', 'delete', 'approve'])) {
                    continue;
                }
                
                // Basic permission: module.action
                $permissionName = "{$moduleKey}.{$actionKey}";
                $displayName = "{$actionName} {$moduleName}";
                
                Permission::create([
                    'id' => $permissionId,
                    'name' => $permissionName,
                    'guard_name' => 'web'
                ]);
                
                $allPermissions[] = $permissionName;
                $this->command->line("  {$permissionId}: {$permissionName} ({$displayName})");
                $permissionId++;
                
                // Add scoped permissions for view action
                if ($actionKey === 'view' && in_array($moduleKey, ['user', 'department', 'employee', 'report', 'leave'])) {
                    foreach ($scopes as $scopeKey => $scopeName) {
                        $scopedPermission = "{$moduleKey}.{$actionKey}.{$scopeKey}";
                        $scopedDisplayName = "{$actionName} {$moduleName} {$scopeName}";
                        
                        Permission::create([
                            'id' => $permissionId,
                            'name' => $scopedPermission,
                            'guard_name' => 'web'
                        ]);
                        
                        $allPermissions[] = $scopedPermission;
                        $this->command->line("  {$permissionId}: {$scopedPermission} ({$scopedDisplayName})");
                        $permissionId++;
                    }
                }
            }
        }

        // Create 4 standard roles
        $roles = [
            'Admin' => [
                'display' => 'Quản trị viên',
                'permissions' => $allPermissions // All permissions
            ],
            
            'Ban Giam Doc' => [
                'display' => 'Ban Giám Đốc', 
                'permissions' => [
                    'dashboard.view',
                    'user.view.company', 'user.edit', 
                    'department.view.company', 'department.create', 'department.edit', 'department.approve',
                    'employee.view.company', 'employee.create', 'employee.edit', 'employee.approve',
                    'report.view.company', 'report.approve',
                    'leave.view.company', 'leave.approve',
                    'profile.view', 'profile.edit'
                ]
            ],
            
            'Truong Phong' => [
                'display' => 'Trưởng Phòng',
                'permissions' => [
                    'dashboard.view',
                    'department.view.department', 
                    'employee.view.department', 'employee.edit',
                    'report.view.department', 'report.create', 'report.edit',
                    'leave.view.department', 'leave.approve',
                    'profile.view', 'profile.edit'
                ]
            ],
            
            'Nhan Vien' => [
                'display' => 'Nhân Viên',
                'permissions' => [
                    'dashboard.view',
                    'employee.view.own',
                    'report.view.own',
                    'leave.view.own', 'leave.create',
                    'profile.view'
                ]
            ]
        ];

        $this->command->info('👥 Creating roles...');
        foreach ($roles as $roleKey => $roleData) {
            $role = Role::create(['name' => $roleKey]);
            
            $rolePermissions = Permission::whereIn('name', $roleData['permissions'])->get();
            $role->syncPermissions($rolePermissions);
            
            $this->command->line("- {$roleKey} ({$roleData['display']}): {$rolePermissions->count()} permissions");
        }

        // Clear cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->command->info('✅ Clean permission system created!');
        $this->command->line('');
        $this->command->info('📋 Structure:');
        $this->command->line('- Permission pattern: module.action[.scope]');
        $this->command->line('- 4 roles: Admin, Ban Giam Doc, Truong Phong, Nhan Vien');
        $this->command->line('- ' . count($allPermissions) . ' total permissions');
        $this->command->line('- Ready for new modules!');
    }
}

<?php

namespace App\Helpers;

use App\Models\User;

class PermissionHelper
{
    /**
     * Check if user can perform action on module
     *
     * @param User $user
     * @param string $permission Format: "module.action" or "module.action.scope"
     * @return bool
     */
    public static function can($user, $permission)
    {
        if (!$user) return false;

        // Admin has everything
        if ($user->hasRole('Admin')) {
            return true;
        }

        return $user->hasPermissionTo($permission);
    }

    /**
     * Check current user permission
     */
    public static function userCan($permission)
    {
        return self::can(backpack_user(), $permission);
    }

    /**
     * Check if user can access module
     */
    public static function canAccessModule($user, $module)
    {
        if (!$user) return false;

        // Check if user has any permission for this module
        $modulePermissions = $user->getAllPermissions()
            ->filter(function($permission) use ($module) {
                return str_starts_with($permission->name, $module . '.');
            });

        return $modulePermissions->count() > 0;
    }

    /**
     * Get user's data scope
     */
    public static function getUserScope($user)
    {
        if (!$user) return 'none';

        if ($user->hasRole('Admin')) return 'all';

        // ✅ Check multiple variations of role names
        if ($user->hasRole(['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc'])) return 'company';
        if ($user->hasRole(['Trưởng phòng', 'Truong Phong', 'Trưởng Phòng'])) return 'department';
        if ($user->hasRole(['Nhân sự', 'Nhan Vien', 'Nhân sự'])) return 'own';

        return 'none';
    }

    /**
     * Get modules user can access
     */
    public static function getUserModules($user)
    {
        if (!$user) return [];

        $modules = ['dashboard', 'user', 'role', 'permission', 'department', 'employee', 'report', 'leave', 'profile'];
        $accessibleModules = [];

        foreach ($modules as $module) {
            if (self::canAccessModule($user, $module)) {
                $accessibleModules[] = $module;
            }
        }

        return $accessibleModules;
    }

    /**
     * Permission display names (Vietnamese)
     */
    public static function getDisplayName($permission)
    {
        $translations = [
            // Modules
            'dashboard' => 'Bảng điều khiển',
            'user' => 'Người dùng',
            'role' => 'Vai trò',
            'permission' => 'Quyền hạn',
            'department' => 'Phòng ban',
            'employee' => 'Nhân sự',
            'report' => 'Báo cáo quân số',
            'leave' => 'Đơn nghỉ phép',
            'profile' => 'Thông tin cá nhân',

            // Actions
            'view' => 'Xem',
            'create' => 'Tạo',
            'edit' => 'Sửa',
            'delete' => 'Xóa',
            'approve' => 'Phê duyệt',

            // Scopes
            'own' => 'cá nhân',
            'department' => 'phòng ban',
            'company' => 'công ty',
            'all' => 'tất cả'
        ];

        $parts = explode('.', $permission);
        $display = [];

        foreach ($parts as $part) {
            $display[] = $translations[$part] ?? $part;
        }

        return implode(' ', $display);
    }

    /**
     * Role display names (Vietnamese)
     */
    public static function getRoleDisplayName($role)
    {
        $roleTranslations = [
            'Admin' => 'Quản trị viên',
            'Ban Giam Doc' => 'Ban Giám Đốc',
            'Truong Phong' => 'Trưởng Phòng',
            'Nhan Vien' => 'Nhân sự'
        ];

        return $roleTranslations[$role] ?? $role;
    }
}

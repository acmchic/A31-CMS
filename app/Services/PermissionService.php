<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * Check if user can perform action on resource
     */
    public static function can(User $user, string $action, string $resource): bool
    {
        // Build permission name dynamically
        $permissionName = self::buildPermissionName($action, $resource);

        return $user->hasPermissionTo($permissionName);
    }

    /**
     * Check if current user can perform action on resource
     */
    public static function userCan(string $action, string $resource): bool
    {
        $user = backpack_user();
        if (!$user) return false;

        return self::can($user, $action, $resource);
    }

    /**
     * Build permission name from action and resource
     */
    public static function buildPermissionName(string $action, string $resource): string
    {
        $actionMap = [
            'view' => 'xem',
            'create' => 'tạo',
            'edit' => 'sửa',
            'delete' => 'xóa',
            'approve' => 'phê duyệt',
            'manage' => 'quản lý',
            'upload' => 'upload',
            'change' => 'đổi'
        ];

        $resourceMap = [
            'dashboard' => 'bảng điều khiển',
            'users' => 'người dùng',
            'roles' => 'vai trò',
            'permissions' => 'quyền hạn',
            'departments' => 'phòng ban',
            'employees' => 'Nhân sự',
            'reports' => 'báo cáo quân số',
            'leave_requests' => 'đơn nghỉ phép',
            'profile' => 'thông tin cá nhân',
            'password' => 'mật khẩu',
            'avatar' => 'ảnh đại diện',
            'signature' => 'chữ ký',
            'own_data' => 'dữ liệu cá nhân',
            'department_data' => 'dữ liệu phòng ban',
            'company_data' => 'dữ liệu công ty',
            'all_data' => 'tất cả dữ liệu',
            'system_settings' => 'hệ thống'
        ];

        $actionText = $actionMap[$action] ?? $action;
        $resourceText = $resourceMap[$resource] ?? $resource;

        return "{$actionText} {$resourceText}";
    }

    /**
     * Get user's accessible modules
     */
    public static function getUserAccessibleModules(User $user): array
    {
        $modules = [];

        // System Module
        if (self::can($user, 'manage', 'users') ||
            self::can($user, 'manage', 'roles') ||
            self::can($user, 'manage', 'permissions')) {
            $modules[] = 'system';
        }

        // Organization Module
        if (self::can($user, 'view', 'departments') ||
            self::can($user, 'view', 'employees')) {
            $modules[] = 'organization';
        }

        // Personnel Report Module
        if (self::can($user, 'view', 'reports') ||
            self::can($user, 'view', 'leave_requests')) {
            $modules[] = 'personnel_report';
        }

        return $modules;
    }

    /**
     * Check if user can access module
     */
    public static function canAccessModule(User $user, string $module): bool
    {
        return in_array($module, self::getUserAccessibleModules($user));
    }

    /**
     * Get permission level for user on resource
     */
    public static function getPermissionLevel(User $user, string $resource): string
    {
        if (self::can($user, 'manage', $resource) || self::can($user, 'delete', $resource)) {
            return 'full';
        }

        if (self::can($user, 'edit', $resource) || self::can($user, 'create', $resource)) {
            return 'edit';
        }

        if (self::can($user, 'view', $resource)) {
            return 'view';
        }

        return 'none';
    }

    /**
     * Batch check multiple permissions
     */
    public static function canAny(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (is_array($permission)) {
                // ['action' => 'view', 'resource' => 'users']
                if (self::can($user, $permission['action'], $permission['resource'])) {
                    return true;
                }
            } else {
                // Direct permission name
                if ($user->hasPermissionTo($permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get data scope for user
     */
    public static function getUserDataScope(User $user): string
    {
        if (self::can($user, 'view', 'all_data')) {
            return 'all';
        }

        if (self::can($user, 'view', 'company_data')) {
            return 'company';
        }

        if (self::can($user, 'view', 'department_data')) {
            return 'department';
        }

        return 'own';
    }
}

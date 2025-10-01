<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\RoleCrudController as BaseRoleCrudController;
use Backpack\PermissionManager\app\Http\Requests\RoleStoreCrudRequest as StoreRequest;
use Backpack\PermissionManager\app\Http\Requests\RoleUpdateCrudRequest as UpdateRequest;
use Spatie\Permission\PermissionRegistrar;

class RoleCrudController extends BaseRoleCrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    public function setupListOperation()
    {
        /**
         * Show a column for the name of the role.
         */
        $this->crud->addColumn([
            'name'  => 'name',
            'label' => trans('backpack::permissionmanager.name'),
            'type'  => 'text',
        ]);

        /**
         * Show a column with the number of users that have that particular role.
         */
        $this->crud->query->withCount('users');
        $this->crud->addColumn([
            'label'     => trans('backpack::permissionmanager.users'),
            'type'      => 'text',
            'name'      => 'users_count',
            'wrapper'   => [
                'href' => function ($crud, $column, $entry, $related_key) {
                    return backpack_url('user?role='.$entry->getKey());
                },
            ],
            'suffix'    => ' '.strtolower(trans('backpack::permissionmanager.users')),
        ]);

        /**
         * In case multiple guards are used, show a column for the guard.
         */
        if (config('backpack.permissionmanager.multiple_guards')) {
            $this->crud->addColumn([
                'name'  => 'guard_name',
                'label' => trans('backpack::permissionmanager.guard_type'),
                'type'  => 'text',
            ]);
        }

        /**
         * Show the exact permissions that role has.
         */
        $this->crud->addColumn([
            // n-n relationship (with pivot table)
            'label'     => mb_ucfirst(trans('backpack::permissionmanager.permission_plural')),
            'type'      => 'select_multiple',
            'name'      => 'permissions', // the method that defines the relationship in your Model
            'entity'    => 'permissions', // the method that defines the relationship in your Model
            'attribute' => 'name', // foreign key attribute that is shown to user
            'model'     => $this->permission_model, // foreign key model
            'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
        ]);
    }

    public function setupCreateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(StoreRequest::class);

        //otherwise, changes won't have effect
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        // Get the permissions from request
        $permissionIds = request()->get('permissions', []);

        \Log::info('Role Store Request Data:', [
            'permissions' => $permissionIds,
            'permissions_count' => count($permissionIds)
        ]);

        $this->crud->unsetValidation();

        // Create the role first
        $response = $this->crud->performSaveAction();

        // After successful creation, sync permissions
        if ($response instanceof \Illuminate\Http\RedirectResponse && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $role = $this->crud->entry;

            if ($role && is_array($permissionIds) && count($permissionIds) > 0) {
                // Convert permission IDs to integers and filter out invalid ones
                $validPermissionIds = array_filter(array_map('intval', $permissionIds), function($id) {
                    return $id > 0;
                });

                // Sync permissions with the role
                $role->permissions()->sync($validPermissionIds);

                // Clear permission cache
                app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

                \Log::info('Role permissions synced on create:', [
                    'role_id' => $role->id,
                    'permission_count' => count($validPermissionIds)
                ]);
            }
        }

        return $response;
    }

    public function setupUpdateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(UpdateRequest::class);

        //otherwise, changes won't have effect
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());

        // Get the permissions from request
        $permissionIds = request()->get('permissions', []);

        \Log::info('Role Update Request Data:', [
            'all' => request()->all(),
            'permissions' => $permissionIds,
            'permissions_count' => count($permissionIds)
        ]);

        $this->crud->unsetValidation(); // validation has already been run

        // Update the role first
        $response = $this->traitUpdate();

        // After successful update, sync permissions
        if ($response instanceof \Illuminate\Http\RedirectResponse && $response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $role = $this->crud->getCurrentEntry();

            if ($role && is_array($permissionIds) && count($permissionIds) > 0) {
                // Convert permission IDs to integers and filter out invalid ones
                $validPermissionIds = array_filter(array_map('intval', $permissionIds), function($id) {
                    return $id > 0;
                });

                // Sync permissions with the role
                $role->permissions()->sync($validPermissionIds);

                // Clear permission cache
                app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

                \Log::info('Role permissions synced successfully:', [
                    'role_id' => $role->id,
                    'permission_count' => count($validPermissionIds)
                ]);
            }
        }

        return $response;
    }

    private function addFields()
    {
        $this->crud->addField([
            'name'  => 'name',
            'label' => trans('backpack::permissionmanager.name'),
            'type'  => 'text',
        ]);

        if (config('backpack.permissionmanager.multiple_guards')) {
            $this->crud->addField([
                'name'    => 'guard_name',
                'label'   => trans('backpack::permissionmanager.guard_type'),
                'type'    => 'select_from_array',
                'options' => $this->getGuardTypes(),
            ]);
        }

        $this->crud->addField([
            'label'     => mb_ucfirst(trans('backpack::permissionmanager.permission_plural')),
            'type'      => 'permission_groups',
            'name'      => 'permissions',
            'entity'    => 'permissions',
            'attribute' => 'name',
            'model'     => $this->permission_model,
            'pivot'     => true,
            'wrapper'   => [
                'class'   => 'form-group col-sm-12',
            ],
            'attributes' => [
                'data-init' => 'permission_groups_field'
            ],
        ]);
    }


    private function getPermissionModules()
    {
        return [
            'dashboard' => 'Bảng Điều Khiển',
            'data_management' => 'Quản Lý Dữ Liệu',
            'user_management' => 'Quản Lý Người Dùng',
            'department_management' => 'Quản Lý Phòng Ban',
            'leave_management' => 'Quản Lý Nghỉ Phép',
            'vehicle_management' => 'Quản Lý Phương Tiện',
            'reports' => 'Báo Cáo',
            'employee_management' => 'Quản Lý Nhân Viên',
            'system_settings' => 'Cài Đặt Hệ Thống',
            'pdf_signatures' => 'Ký Số PDF',
        ];
    }

    private function getGroupedPermissionOptions()
    {
        $permissionModel = config('backpack.permissionmanager.models.permission');
        $permissions = $permissionModel::all();
        $options = [];

        // Define permission groups
        $groups = [
            'dashboard' => '📊 Bảng Điều Khiển',
            'data_management' => '💾 Quản Lý Dữ Liệu',
            'user_management' => '👥 Quản Lý Người Dùng',
            'department_management' => '🏢 Quản Lý Phòng Ban',
            'leave_management' => '🏖️ Quản Lý Nghỉ Phép',
            'vehicle_management' => '🚗 Quản Lý Phương Tiện',
            'reports' => '📈 Báo Cáo',
            'employee_management' => '👤 Quản Lý Nhân Viên',
            'system_settings' => '⚙️ Cài Đặt Hệ Thống',
            'pdf_signatures' => '📝 Ký Số PDF',
        ];

        // Group permissions by category
        $groupedPermissions = [
            'dashboard' => ['view dashboard'],
            'data_management' => ['view own data', 'edit own data', 'delete own data', 'view all data', 'edit all data', 'delete all data'],
            'user_management' => ['manage users', 'manage roles', 'manage permissions', 'view_user_profile'],
            'department_management' => ['manage departments', 'view all departments', 'edit all departments', 'manage department users', 'view_department'],
            'leave_management' => ['approve leaves', 'approve-leave-request', 'reject-leave-request', 'view-leave-request', 'view_leave_request'],
            'vehicle_management' => ['manage vehicles', 'assign vehicles', 'view vehicle requests', 'approve vehicle step2'],
            'reports' => ['view reports', 'view_daily_report'],
            'employee_management' => ['view_employee'],
            'system_settings' => ['system settings'],
            'pdf_signatures' => ['sign-pdf'],
        ];

        // Build options with groups
        foreach ($groups as $groupKey => $groupTitle) {
            $groupPermissions = $groupedPermissions[$groupKey] ?? [];
            $hasPermissions = false;

            foreach ($permissions as $permission) {
                if (in_array($permission->name, $groupPermissions)) {
                    $translatedName = $this->translatePermission($permission->name);
                    $options[$permission->id] = $groupTitle . ' - ' . $translatedName;
                    $hasPermissions = true;
                }
            }

            // Add separator if group has permissions
            if ($hasPermissions) {
                $options['separator_' . $groupKey] = '─────────────────────────';
            }
        }

        return $options;
    }

    private function translatePermission($permissionName)
    {
        $translations = [
            'view_user_profile' => 'Xem hồ sơ người dùng',
            'view_department' => 'Xem phòng ban',
            'view_daily_report' => 'Xem báo cáo hàng ngày',
            'view_leave_request' => 'Xem đơn xin nghỉ phép',
            'view_employee' => 'Xem nhân viên',
            'sign-pdf' => 'Ký số PDF',
            'view-leave-request' => 'Xem đơn xin nghỉ phép',
            'reject-leave-request' => 'Từ chối đơn xin nghỉ phép',
            'approve-leave-request' => 'Phê duyệt đơn xin nghỉ phép',
            'approve vehicle step2' => 'Phê duyệt phương tiện cấp 2',
            'view vehicle requests' => 'Xem yêu cầu phương tiện',
            'assign vehicles' => 'Phân công phương tiện',
            'view dashboard' => 'Xem bảng điều khiển',
            'view own data' => 'Xem dữ liệu cá nhân',
            'view all data' => 'Xem tất cả dữ liệu',
            'edit own data' => 'Chỉnh sửa dữ liệu cá nhân',
            'edit all data' => 'Chỉnh sửa tất cả dữ liệu',
            'delete own data' => 'Xóa dữ liệu cá nhân',
            'delete all data' => 'Xóa tất cả dữ liệu',
            'manage users' => 'Quản lý người dùng',
            'manage roles' => 'Quản lý vai trò',
            'manage permissions' => 'Quản lý quyền hạn',
            'manage departments' => 'Quản lý phòng ban',
            'edit all departments' => 'Chỉnh sửa tất cả phòng ban',
            'view all departments' => 'Xem tất cả phòng ban',
            'manage department users' => 'Quản lý người dùng phòng ban',
            'system settings' => 'Cài đặt hệ thống',
            'approve leaves' => 'Phê duyệt nghỉ phép',
            'manage vehicles' => 'Quản lý phương tiện',
            'view reports' => 'Xem báo cáo',
        ];

        return $translations[$permissionName] ?? $permissionName;
    }

    /*
     * Get an array list of all available guard types
     * that have been defined in app/config/auth.php
     *
     * @return array
     **/
    private function getGuardTypes()
    {
        $guards = config('auth.guards');

        $returnable = [];
        foreach ($guards as $key => $details) {
            $returnable[$key] = $key;
        }

        return $returnable;
    }
}

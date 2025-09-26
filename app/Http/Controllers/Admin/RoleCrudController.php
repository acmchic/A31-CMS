<?php

namespace App\Http\Controllers\Admin;

use Backpack\PermissionManager\app\Http\Controllers\RoleCrudController as BaseRoleCrudController;
use Backpack\PermissionManager\app\Http\Requests\RoleStoreCrudRequest as StoreRequest;
use Backpack\PermissionManager\app\Http\Requests\RoleUpdateCrudRequest as UpdateRequest;
use Spatie\Permission\PermissionRegistrar;

class RoleCrudController extends BaseRoleCrudController
{
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

    public function setupUpdateOperation()
    {
        $this->addFields();
        $this->crud->setValidation(UpdateRequest::class);

        //otherwise, changes won't have effect
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
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
            'type'      => 'checklist',
            'name'      => 'permissions',
            'entity'    => 'permissions',
            'attribute' => 'name',
            'model'     => $this->permission_model,
            'pivot'     => true,
            'options'   => function() {
                $permissionModel = config('backpack.permissionmanager.models.permission');
                $permissions = $permissionModel::all();
                $options = [];
                
                foreach ($permissions as $permission) {
                    $translatedName = $this->translatePermission($permission->name);
                    $options[$permission->id] = $translatedName;
                }
                
                return $options;
            },
        ]);
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

<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\PermissionManager\app\Http\Requests\UserStoreCrudRequest as StoreRequest;
use Backpack\PermissionManager\app\Http\Requests\UserUpdateCrudRequest as UpdateRequest;
use Illuminate\Support\Facades\Hash;

class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation { update as traitUpdate; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        $this->crud->setModel(config('backpack.permissionmanager.models.user'));
        $this->crud->setEntityNameStrings(trans('backpack::permissionmanager.user'), trans('backpack::permissionmanager.users'));
        $this->crud->setRoute(backpack_url('user'));
    }

    public function setupListOperation()
    {
        $this->crud->addColumns([
            [
                'name'  => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type'  => 'text',
            ],
            [ // n-n relationship (with pivot table)
                'label'     => trans('backpack::permissionmanager.roles'), // Table column heading
                'type'      => 'select_multiple',
                'name'      => 'roles', // the method that defines the relationship in your Model
                'entity'    => 'roles', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => config('permission.models.role'), // foreign key model
            ],
            [ // n-n relationship (with pivot table)
                'label'     => trans('backpack::permissionmanager.extra_permissions'), // Table column heading
                'type'      => 'select_multiple',
                'name'      => 'permissions', // the method that defines the relationship in your Model
                'entity'    => 'permissions', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => config('permission.models.permission'), // foreign key model
            ],
        ]);

        // Xử lý filter role từ URL parameter
        if (request()->has('role') && request()->get('role')) {
            $roleId = request()->get('role');
            $this->crud->addClause('whereHas', 'roles', function ($query) use ($roleId) {
                $query->where('role_id', '=', $roleId);
            });
        }

        // Xử lý filter permissions từ URL parameter
        if (request()->has('permissions') && request()->get('permissions')) {
            $permissionId = request()->get('permissions');
            $this->crud->addClause('whereHas', 'permissions', function ($query) use ($permissionId) {
                $query->where('permission_id', '=', $permissionId);
            });
        }
    }

    public function setupCreateOperation()
    {
        $this->addUserFields();
        $this->crud->setValidation(StoreRequest::class);
    }

    public function setupUpdateOperation()
    {
        $this->addUserFields();
        $this->crud->setValidation(UpdateRequest::class);
    }

    public function setupShowOperation()
    {
        $this->crud->set('show.setFromDb', false);
        
        $this->crud->addColumns([
            [
                'name'  => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type'  => 'text',
            ],
            [ // n-n relationship (with pivot table)
                'label'     => trans('backpack::permissionmanager.roles'),
                'type'      => 'select_multiple',
                'name'      => 'roles',
                'entity'    => 'roles',
                'attribute' => 'name',
                'model'     => config('permission.models.role'),
            ],
            [ // Show permissions in Vietnamese
                'label'     => trans('backpack::permissionmanager.extra_permissions'),
                'name'      => 'permissions_vietnamese',
                'type'      => 'closure',
                'function'  => function($entry) {
                    $translations = [
                        'vehicle_registration.view' => 'Xem đăng ký xe',
                        'vehicle_registration.create' => 'Tạo đăng ký xe',
                        'vehicle_registration.edit' => 'Sửa đăng ký xe',
                        'vehicle_registration.delete' => 'Xóa đăng ký xe',
                        'vehicle_registration.assign' => 'Phân công xe',
                        'vehicle_registration.approve' => 'Phê duyệt đăng ký xe',
                        'vehicle_registration.reject' => 'Từ chối đăng ký xe',
                        'vehicle_registration.download_pdf' => 'Tải về PDF đã ký',
                        'leave.view' => 'Xem nghỉ phép',
                        'leave.create' => 'Tạo nghỉ phép',
                        'leave.edit' => 'Sửa nghỉ phép',
                        'leave.delete' => 'Xóa nghỉ phép',
                        'leave.approve' => 'Phê duyệt nghỉ phép',
                        'report.view' => 'Xem báo cáo',
                        'report.create' => 'Tạo báo cáo',
                        'report.edit' => 'Sửa báo cáo',
                        'report.delete' => 'Xóa báo cáo',
                        'report.approve' => 'Phê duyệt báo cáo',
                        'user.view' => 'Xem người dùng',
                        'user.create' => 'Tạo người dùng',
                        'user.edit' => 'Sửa người dùng',
                        'user.delete' => 'Xóa người dùng',
                        'role.view' => 'Xem vai trò',
                        'role.create' => 'Tạo vai trò',
                        'role.edit' => 'Sửa vai trò',
                        'role.delete' => 'Xóa vai trò',
                    ];
                    
                    $permissions = $entry->permissions;
                    if ($permissions->isEmpty()) {
                        return '-';
                    }
                    
                    return $permissions->map(function($permission) use ($translations) {
                        return $translations[$permission->name] ?? $permission->name;
                    })->implode(', ');
                },
            ],
        ]);
    }

    private function addUserFields()
    {
        $this->crud->addFields([
            [
                'name'  => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type'  => 'text',
            ],
            [
                'name'  => 'email',
                'label' => trans('backpack::permissionmanager.email'),
                'type'  => 'email',
                'default' => 'user@a31factory.com',
                'wrapper' => [
                    'class' => 'form-group col-md-12 d-none', // Ẩn field
                ],
            ],
            [
                'name'  => 'password',
                'label' => trans('backpack::permissionmanager.password'),
                'type'  => 'password',
            ],
            [
                'name'  => 'password_confirmation',
                'label' => trans('backpack::permissionmanager.password_confirmation'),
                'type'  => 'password',
            ],
            [ // n-n relationship (with pivot table)
                'label'     => trans('backpack::permissionmanager.roles'), // Table column heading
                'type'      => 'checklist',
                'name'      => 'roles', // the method that defines the relationship in your Model
                'entity'    => 'roles', // the method that defines the relationship in your Model
                'attribute' => 'name', // foreign key attribute that is shown to user
                'model'     => config('permission.models.role'), // foreign key model
                'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
            ],
            [ // n-n relationship (with pivot table)
                'label'     => trans('backpack::permissionmanager.extra_permissions'),
                'type'      => 'permission_groups',
                'name'      => 'permissions',
                'entity'    => 'permissions',
                'attribute' => 'name',
                'model'     => config('permission.models.permission'),
                'pivot'     => true,
            ],
            [
                'name' => 'auto_fill_email',
                'type' => 'custom_html',
                'value' => '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        // Auto fill email nếu rỗng
                        var emailInput = document.querySelector("input[name=email]");
                        if (emailInput && !emailInput.value) {
                            emailInput.value = "user@a31factory.com";
                        }
                    });
                </script>',
            ],
        ]);
    }

    public function store()
    {
        $request = $this->crud->getRequest();
        
        // Ensure permissions field exists (empty array if not sent)
        if (!$request->has('permissions')) {
            $request->merge(['permissions' => []]);
        }
        
        $this->crud->setRequest($request);
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitStore();
    }

    public function update()
    {
        $request = $this->crud->getRequest();
        
        // Ensure permissions field exists (empty array if not sent)
        if (!$request->has('permissions')) {
            $request->merge(['permissions' => []]);
        }
        
        $this->crud->setRequest($request);
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }

    /**
     * Handle password input fields.
     */
    protected function handlePasswordInput($request)
    {
        // Remove fields not present on the user.
        $request->request->remove('password_confirmation');
        $request->request->remove('roles_show');
        $request->request->remove('permissions_show');

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', Hash::make($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        return $request;
    }
}

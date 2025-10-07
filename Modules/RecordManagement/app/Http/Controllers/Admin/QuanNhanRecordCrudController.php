<?php

namespace Modules\RecordManagement\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\RecordManagement\Models\QuanNhanRecord;
use App\Helpers\PermissionHelper;

class QuanNhanRecordCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(QuanNhanRecord::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/quan-nhan-record');
        CRUD::setEntityNameStrings('Sổ danh sách quân nhân', 'Sổ danh sách quân nhân');

        $scope = PermissionHelper::getUserScope(backpack_user());
        $this->applyDataScope($scope);
    }

    protected function applyDataScope($scope)
    {
        if ($scope === 'department') {
            CRUD::addClause('where', 'department_id', backpack_user()->department_id);
        }
    }

    protected function setupListOperation()
    {
        if (!PermissionHelper::userCan('record_management.view')) {
            abort(403, 'Không có quyền truy cập');
        }

        CRUD::orderBy('id', 'asc');

        // Setup searchable columns
        // CRUD::enableDetailsRow(); // Requires Backpack Pro
        // CRUD::enableExportButtons(); // Requires Backpack Pro

        // Add search functionality - Commented out as it requires Backpack Pro
        // CRUD::addFilter([
        //     'name' => 'employee_name',
        //     'type' => 'text',
        //     'label' => 'Tìm theo tên'
        // ], false, function($value) {
        //     CRUD::addClause('whereHas', 'employee', function($query) use ($value) {
        //         $query->where('name', 'like', '%'.$value.'%');
        //     });
        // });

        // CRUD::addFilter([
        //     'name' => 'department_id',
        //     'type' => 'dropdown',
        //     'label' => 'Phòng ban'
        // ], \Modules\OrganizationStructure\Models\Department::pluck('name', 'id')->toArray(), function($value) {
        //     CRUD::addClause('where', 'department_id', $value);
        // });

        // CRUD::addFilter([
        //     'name' => 'rank_code',
        //     'type' => 'text',
        //     'label' => 'Quân hàm'
        // ], false, function($value) {
        //     CRUD::addClause('whereHas', 'employee', function($query) use ($value) {
        //         $query->where('rank_code', 'like', '%'.$value.'%');
        //     });
        // });

        CRUD::column('id')->label('STT')->priority(1);
        CRUD::column('employee.name')->label('Họ và tên')->priority(2)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhereHas('employee', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%'.$searchTerm.'%');
            });
        });
        CRUD::column('employee.date_of_birth')->label('Ngày sinh')->type('date')->priority(3);
        CRUD::column('employee.rank_code')->label('Quân hàm')->priority(4)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhereHas('employee', function ($q) use ($searchTerm) {
                $q->where('rank_code', 'like', '%'.$searchTerm.'%');
            });
        });
        CRUD::column('department.name')->label('Phòng ban')->priority(5)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhereHas('department', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%'.$searchTerm.'%');
            });
        });
        CRUD::column('cap_bac')->label('Cấp bậc')->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhere('cap_bac', 'like', '%'.$searchTerm.'%');
        });
        CRUD::column('ngay_vao_dang')->label('Ngày vào Đảng')->type('date');
        CRUD::column('created_at')->label('Ngày tạo')->type('datetime')->format('DD/MM/YYYY HH:mm');
    }



    protected function setupCreateOperation()
    {
        if (!PermissionHelper::userCan('record_management.create')) {
            abort(403, 'Không có quyền tạo mới');
        }

        $this->crud->setValidation([
            'employee_id' => 'required|integer|exists:employees,id',
            'department_id' => 'required|integer|exists:departments,id',
        ]);

        // ========== THÔNG TIN CƠ BẢN ==========
        $this->addSectionHeader('Thông tin cơ bản');

        $this->addDepartmentField();
        $this->addEmployeeField();

        // ========== THÔNG TIN CÁ NHÂN ==========
        $this->addSectionHeader('Thông tin cá nhân');



        CRUD::addField([
            'name' => 'display_ho_ten',
            'label' => 'Họ đệm tên khai sinh',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'ho-ten-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        CRUD::addField([
            'name' => 'display_ngay_sinh',
            'label' => 'Ngày tháng năm sinh',
            'type' => 'date',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'ngay-sinh-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        CRUD::addField([
            'name' => 'display_gioi_tinh',
            'label' => 'Giới tính',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'gioi-tinh-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        CRUD::addField([
            'name' => 'display_quan_ham',
            'label' => 'Quân hàm',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'quan-ham-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        CRUD::addField([
            'name' => 'display_chuc_vu',
            'label' => 'Chức vụ',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'chuc-vu-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        CRUD::addField([
            'name' => 'display_nhap_ngu',
            'label' => 'Nhập ngũ',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'nhap-ngu-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        CRUD::addField([
            'name' => 'display_cccd',
            'label' => 'Số CCCD',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-3'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'cccd-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        CRUD::addField([
            'name' => 'display_dia_chi',
            'label' => 'Địa chỉ',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-8'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'dia-chi-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        CRUD::addField([
            'name' => 'display_dien_thoai',
            'label' => 'Điện thoại',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'dien-thoai-field',
                'class' => 'form-control bg-light',
                'tabindex' => '-1',
            ],
            'fake' => true,
            'store_in' => false,
        ]);

        // Separator
        CRUD::field('separator_personal')->type('custom_html')->value('<hr class="my-3">');

        // Thông tin cá nhân bổ sung (LƯU VÀO DB)
        CRUD::field('ho_ten_thuong_dung')->label('Họ tên thường dùng')->type('text')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('so_hieu_quan_nhan')->label('Số hiệu quân nhân')->type('text')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('so_the_QN')->label('Số thẻ quân nhân')->type('text')->wrapper(['class' => 'form-group col-md-4']);

        // ========== THÔNG TIN QUÂN ĐỘI (ĐIỀN THÊM) ==========
        $this->addSectionHeader('Thông tin quân đội');

        CRUD::field('cap_bac')->label('Cấp bậc')->type('text')->wrapper(['class' => 'form-group col-md-3']);
        CRUD::field('ngay_nhan_cap')->label('Ngày cấp')->type('date')->wrapper(['class' => 'form-group col-md-3']);
        CRUD::field('ngay_cap_cc')->label('Ngày cấp CM, thẻ, CC')->type('date')->wrapper(['class' => 'form-group col-md-3']);
        CRUD::field('cnqs')->label('CNQS')->type('text')->wrapper(['class' => 'form-group col-md-3']);

        CRUD::field('bac_ky_thuat')->label('Bậc kỹ thuật')->type('text')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('tai_ngu')->label('Tái ngũ')->type('text')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('luong_nhom_ngach_bac')->label('Lương: nhóm ngạch bậc')->type('text')->wrapper(['class' => 'form-group col-md-4']);

        CRUD::field('ngay_chuyen_qncn')->label('Ngày chuyển QNCN')->type('date')->wrapper(['class' => 'form-group col-md-6']);
        CRUD::field('ngay_chuyen_cnv')->label('Ngày chuyển CNV')->type('date')->wrapper(['class' => 'form-group col-md-6']);

        // ========== THÔNG TIN CHÍNH TRỊ ==========
        $this->addSectionHeader('Thông tin chính trị');

        CRUD::field('ngay_vao_doan')->label('Ngày vào Đoàn')->type('date')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('ngay_vao_dang')->label('Ngày vào Đảng')->type('date')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('ngay_chinh_thuc')->label('Ngày chính thức')->type('date')->wrapper(['class' => 'form-group col-md-4']);

        // ========== THÀNH PHẦN ==========
        $this->addSectionHeader('Thành phần');

        CRUD::field('tp_gia_dinh')->label('Thành phần gia đình')->type('text')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('tp_ban_than')->label('Thành phần bản thân')->type('text')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('dan_toc')->label('Dân tộc')->type('text')->wrapper(['class' => 'form-group col-md-2']);
        CRUD::field('ton_giao')->label('Tôn giáo')->type('text')->wrapper(['class' => 'form-group col-md-2']);

        // ========== TRÌNH ĐỘ ==========
        $this->addSectionHeader('Trình độ');

        CRUD::field('van_hoa')->label('Văn hóa')->type('text')->wrapper(['class' => 'form-group col-md-3']);
        CRUD::field('ngoai_ngu')->label('Ngoại ngữ')->type('text')->wrapper(['class' => 'form-group col-md-3']);
        CRUD::field('suc_khoe')->label('Sức khỏe')->type('text')->wrapper(['class' => 'form-group col-md-3']);
        CRUD::field('hang_thuong_tru')->label('Hạng thường trú')->type('text')->wrapper(['class' => 'form-group col-md-3']);

        CRUD::field('khu_vuc')->label('Khu vực')->type('text')->wrapper(['class' => 'form-group col-md-6']);
        CRUD::field('nguon_quan')->label('Nguồn quân')->type('text')->wrapper(['class' => 'form-group col-md-6'])
            ->hint('Sinh quân/Trở quân');

        // ========== ĐÀO TẠO ==========
        $this->addSectionHeader('Đào tạo');

        CRUD::field('ten_truong')->label('Tên trường')->type('text')->wrapper(['class' => 'form-group col-md-6']);
        CRUD::field('cap_hoc')->label('Cấp học')->type('text')->wrapper(['class' => 'form-group col-md-3'])
            ->hint('VD: ĐH, CĐ, TC');
        CRUD::field('thoi_gian_hoc')->label('Thời gian')->type('text')->wrapper(['class' => 'form-group col-md-3'])
            ->hint('VD: 2018-2022');

        CRUD::field('nganh_hoc')->label('Ngành học')->type('text')->wrapper(['class' => 'form-group col-md-12']);

        // ========== KHEN THƯỞNG - KỶ LUẬT ==========
        $this->addSectionHeader('Khen thưởng - Kỷ luật');

        CRUD::field('khen_thuong')->label('Khen thưởng')->type('textarea')->wrapper(['class' => 'form-group col-md-6'])
            ->attributes(['rows' => 3]);
        CRUD::field('ky_luat')->label('Kỷ luật')->type('textarea')->wrapper(['class' => 'form-group col-md-6'])
            ->attributes(['rows' => 3]);

        // ========== THÔNG TIN GIA ĐÌNH ==========
        $this->addSectionHeader('Thông tin gia đình');

        CRUD::field('ho_ten_cha')->label('Họ đệm tên cha')->type('text')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('ho_ten_me')->label('Họ đệm tên mẹ')->type('text')->wrapper(['class' => 'form-group col-md-4']);
        CRUD::field('ho_ten_vo_chong')->label('Họ đệm tên vợ (chồng)')->type('text')->wrapper(['class' => 'form-group col-md-4']);

        CRUD::field('may_con')->label('Mấy con')->type('text')->wrapper(['class' => 'form-group col-md-12'])
            ->hint('VD: 2 con');

        // ========== LIÊN HỆ KHẨN CẤP ==========
        $this->addSectionHeader('Liên hệ khẩn cấp');

        CRUD::field('bao_tin')->label('Khi cần báo tin cho ai? ở đâu?')->type('textarea')->wrapper(['class' => 'form-group col-md-12'])
            ->attributes(['rows' => 3]);

        // ========== GHI CHÚ ==========
        $this->addSectionHeader('Ghi chú');

        CRUD::field('ghi_chu')->label('Ghi chú')->type('textarea')->wrapper(['class' => 'form-group col-md-12'])
            ->attributes(['rows' => 4]);

        // JavaScript auto-fill
        CRUD::addField([
            'name' => 'auto_fill_script',
            'type' => 'custom_html',
            'value' => $this->getAutoFillScript(),
        ]);
    }

    private function addSectionHeader($title)
    {
        CRUD::addField([
            'name' => 'separator_' . str_replace(' ', '_', strtolower($title)),
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mb-3 text-primary text-uppercase"><i class="la la-folder"></i> ' . $title . '</h4>',
        ]);
    }

    private function addDepartmentField()
    {
        $departments = \Modules\OrganizationStructure\Models\Department::orderBy('id', 'asc')
            ->pluck('name', 'id')
            ->toArray();

        // Add empty option at the beginning
        $departments = ['' => '- Chọn phòng ban -'] + $departments;

        CRUD::addField([
            'name' => 'department_id',
            'label' => 'Phòng ban',
            'type' => 'select_from_array',
            'options' => $departments,
            'wrapper' => ['class' => 'form-group col-md-6'],
            'allows_null' => false,
            'attributes' => [
                'required' => 'required',
                'id' => 'department-select',
            ],
        ]);
    }

    private function addEmployeeField()
    {
        CRUD::addField([
            'name' => 'employee_id',
            'label' => 'Nhân sự',
            'type' => 'select_from_array',
            'options' => [],
            'wrapper' => ['class' => 'form-group col-md-6'],
            'allows_null' => false,
            'attributes' => [
                'required' => 'required',
                'id' => 'employee-select',
                'disabled' => 'disabled',
            ],
        ]);
    }


    private function getAutoFillScript()
    {
        return <<<'HTML'
<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department-select');
    const employeeSelect = document.getElementById('employee-select');

    // Load employees by department
    function loadEmployeesByDepartment(departmentId, selectedEmployeeId = null) {
        if (!departmentId) {
            employeeSelect.disabled = true;
            employeeSelect.innerHTML = '<option value="">- Chọn phòng ban trước -</option>';
            return;
        }

        fetch(`/quan-nhan-record/api/employees-by-department/${departmentId}`)
            .then(response => response.json())
            .then(data => {
                employeeSelect.innerHTML = '<option value="">- Chọn nhân sự -</option>';

                if (data && data.length > 0) {
                    data.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.id;
                        option.textContent = emp.name;
                        if (selectedEmployeeId && emp.id == selectedEmployeeId) {
                            option.selected = true;
                        }
                        employeeSelect.appendChild(option);
                    });
                }

                employeeSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi khi tải danh sách nhân viên');
            });
    }

    // When department changes
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            loadEmployeesByDepartment(this.value);
        });

        const currentDepartmentId = departmentSelect.value;
        const currentEmployeeId = employeeSelect.value;

        if (currentDepartmentId && currentEmployeeId) {
            loadEmployeesByDepartment(currentDepartmentId, currentEmployeeId);
            // Trigger employee change to load info after loading department employees
            setTimeout(function() {
                if (employeeSelect.value) {
                    employeeSelect.dispatchEvent(new Event('change'));
                }
            }, 500);
        }
    }

    // When employee changes - auto-fill readonly fields from employees table
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
            const employeeId = this.value;

            if (!employeeId) {
                clearEmployeeFields();
                return;
            }

            fetch(`/quan-nhan-record/api/employee-info/${employeeId}`)
                .then(response => response.json())
                .then(emp => {
                    // Auto-fill thông tin vào các input fields
                    document.getElementById('ho-ten-field').value = emp.name || '';
                    document.getElementById('ngay-sinh-field').value = emp.date_of_birth || '';
                    document.getElementById('gioi-tinh-field').value = emp.gender_text || '';
                    document.getElementById('quan-ham-field').value = emp.rank_code || '';
                    document.getElementById('chuc-vu-field').value = emp.position_name || '';
                    document.getElementById('nhap-ngu-field').value = emp.enlist_date || '';
                    document.getElementById('cccd-field').value = emp.CCCD || '';
                    document.getElementById('dia-chi-field').value = emp.address || '';
                    document.getElementById('dien-thoai-field').value = emp.phone || '';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Lỗi khi tải thông tin nhân viên');
                });
        });
    }

    function clearEmployeeFields() {
        // Clear all display fields
        document.getElementById('ho-ten-field').value = '';
        document.getElementById('ngay-sinh-field').value = '';
        document.getElementById('gioi-tinh-field').value = '';
        document.getElementById('quan-ham-field').value = '';
        document.getElementById('chuc-vu-field').value = '';
        document.getElementById('nhap-ngu-field').value = '';
        document.getElementById('cccd-field').value = '';
        document.getElementById('dia-chi-field').value = '';
        document.getElementById('dien-thoai-field').value = '';
    }
});
</script>
HTML;
    }


    protected function setupUpdateOperation()
    {
        if (!PermissionHelper::userCan('record_management.edit')) {
            abort(403, 'Không có quyền chỉnh sửa');
        }

        $this->setupCreateOperation();

        $entry = $this->crud->getCurrentEntry();
        if ($entry && $entry->employee_id) {
            $employee = \Modules\OrganizationStructure\Models\Employee::find($entry->employee_id);
            if ($employee) {
                CRUD::modifyField('employee_id', [
                    'options' => [$employee->id => $employee->name],
                    'attributes' => [
                        'required' => 'required',
                        'id' => 'employee-select',
                        'data-current-value' => $employee->id,
                    ],
                ]);

                // Auto-trigger employee info load when editing (do nothing here, already handled by main script)
            }
        }
    }

    protected function setupShowOperation()
    {
        if (!PermissionHelper::userCan('record_management.view')) {
            abort(403, 'Không có quyền xem');
        }

        $entry = $this->crud->getCurrentEntry();
        $employee = $entry && $entry->employee_id ? \Modules\OrganizationStructure\Models\Employee::with('position', 'department')->find($entry->employee_id) : null;

        // THÔNG TIN CƠ BẢN
        CRUD::column('department.name')->label('Phòng ban')->type('text');
        CRUD::column('employee.name')->label('Nhân sự')->type('text');

        // THÔNG TIN CÁ NHÂN (từ employees)
        if ($employee) {
            CRUD::column('emp_ho_ten')->label('Họ đệm tên khai sinh')->type('text')->value($employee->name);
            CRUD::column('emp_ngay_sinh')->label('Ngày sinh')->type('date')->value($employee->date_of_birth);
            CRUD::column('emp_gioi_tinh')->label('Giới tính')->type('text')->value($employee->gender == 1 ? 'Nam' : ($employee->gender == 0 ? 'Nữ' : ''));
            CRUD::column('emp_quan_ham')->label('Quân hàm')->type('text')->value($employee->rank_code);
            CRUD::column('emp_chuc_vu')->label('Chức vụ')->type('text')->value($employee->position ? $employee->position->name : '');
            CRUD::column('emp_nhap_ngu')->label('Nhập ngũ')->type('text')->value($employee->enlist_date);
            CRUD::column('emp_cccd')->label('Số CCCD')->type('text')->value($employee->CCCD);
            CRUD::column('emp_dia_chi')->label('Địa chỉ')->type('text')->value($employee->address);
            CRUD::column('emp_dien_thoai')->label('Điện thoại')->type('text')->value($employee->phone);
        }

        // THÔNG TIN CÁ NHÂN BỔ SUNG
        CRUD::column('ho_ten_thuong_dung')->label('Họ tên thường dùng');
        CRUD::column('so_hieu_quan_nhan')->label('Số hiệu quân nhân');
        CRUD::column('so_the_QN')->label('Số thẻ quân nhân');

        // THÔNG TIN QUÂN ĐỘI
        CRUD::column('cap_bac')->label('Cấp bậc');
        CRUD::column('ngay_nhan_cap')->label('Ngày cấp')->type('date');
        CRUD::column('ngay_cap_cc')->label('Ngày cấp CM, thẻ, CC')->type('date');
        CRUD::column('cnqs')->label('Chứng minh quân sự');
        CRUD::column('bac_ky_thuat')->label('Bậc kỹ thuật');
        CRUD::column('tai_ngu')->label('Tái ngũ');
        CRUD::column('ngay_chuyen_qncn')->label('Ngày chuyển QNCN')->type('date');
        CRUD::column('ngay_chuyen_cnv')->label('Ngày chuyển CNV')->type('date');
        CRUD::column('luong_nhom_ngach_bac')->label('Lương: nhóm ngạch bậc');

        // THÔNG TIN CHÍNH TRỊ
        CRUD::column('ngay_vao_doan')->label('Ngày vào Đoàn')->type('date');
        CRUD::column('ngay_vao_dang')->label('Ngày vào Đảng')->type('date');
        CRUD::column('ngay_chinh_thuc')->label('Ngày chính thức Đảng')->type('date');

        // THÀNH PHẦN
        CRUD::column('tp_gia_dinh')->label('Thành phần gia đình');
        CRUD::column('tp_ban_than')->label('Thành phần bản thân');
        CRUD::column('dan_toc')->label('Dân tộc');
        CRUD::column('ton_giao')->label('Tôn giáo');

        // TRÌNH ĐỘ
        CRUD::column('van_hoa')->label('Trình độ văn hóa');
        CRUD::column('ngoai_ngu')->label('Ngoại ngữ');
        CRUD::column('suc_khoe')->label('Sức khỏe');
        CRUD::column('hang_thuong_tru')->label('Hạng thường trú');
        CRUD::column('khu_vuc')->label('Khu vực');
        CRUD::column('khen_thuong')->label('Khen thưởng');
        CRUD::column('ky_luat')->label('Kỷ luật');

        // ĐÀO TẠO
        CRUD::column('ten_truong')->label('Tên trường');
        CRUD::column('cap_hoc')->label('Cấp học');
        CRUD::column('nganh_hoc')->label('Ngành học');
        CRUD::column('thoi_gian_hoc')->label('Thời gian học');
        CRUD::column('nguon_quan')->label('Nguồn quân');
        CRUD::column('bao_tin')->label('Báo tin');

        // THÔNG TIN GIA ĐÌNH
        CRUD::column('ho_ten_cha')->label('Họ tên cha');
        CRUD::column('ho_ten_me')->label('Họ tên mẹ');
        CRUD::column('ho_ten_vo_chong')->label('Họ tên vợ/chồng');
        CRUD::column('may_con')->label('Mấy con');

        // GHI CHÚ
        CRUD::column('ghi_chu')->label('Ghi chú')->type('textarea');
    }

    /**
     * API: Lấy danh sách nhân viên theo phòng ban
     */
    public function getEmployeesByDepartment($departmentId)
    {
        try {
            $employees = \Modules\OrganizationStructure\Models\Employee::where('department_id', $departmentId)
                ->where('is_active', true)
                ->select('id', 'name')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json($employees);
        } catch (\Exception $e) {
            \Log::error('Error loading employees: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy thông tin nhân viên
     */
    public function getEmployeeInfo($employeeId)
    {
        $employee = \Modules\OrganizationStructure\Models\Employee::with('position', 'department')->find($employeeId);

        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        return response()->json([
            'id' => $employee->id,
            'name' => $employee->name,
            'date_of_birth' => $employee->date_of_birth ? $employee->date_of_birth->format('Y-m-d') : null,
            'rank_code' => $employee->rank_code ?? '--',
            'position_name' => $employee->position ? $employee->position->name : '--',
            'enlist_date' => $employee->enlist_date ?? '--',
            'gender_text' => $employee->gender ? 'Nam' : 'Nữ',
            'address' => $employee->address,
            'phone' => $employee->phone,
            'CCCD' => $employee->CCCD,
        ]);
    }
}


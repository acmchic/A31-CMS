<?php

namespace Modules\RecordManagement\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\RecordManagement\Models\SalaryUpRecord;
use App\Helpers\PermissionHelper;

class SalaryUpRecordCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(SalaryUpRecord::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/salary-up-record');
        CRUD::setEntityNameStrings('Sổ nâng lương', 'Sổ nâng lương');

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

        CRUD::column('id')->label('STT')->priority(1);
        CRUD::column('ho_ten')->label('Họ và tên')->priority(2);
        CRUD::column('nhap_ngu')->label('Nhập ngũ')->priority(3);
        CRUD::column('chuc_vu')->label('Chức vụ');

        CRUD::column('luong_hien_loai_nhom')->label('LH-Loại nhóm');
        CRUD::column('luong_hien_bac')->label('LH-Bậc');
        CRUD::column('luong_hien_he_so')->label('LH-Hệ số');

        CRUD::column('luong_moi_loai_nhom')->label('LM-Loại nhóm');
        CRUD::column('luong_moi_bac')->label('LM-Bậc');
        CRUD::column('luong_moi_he_so')->label('LM-Hệ số');

        CRUD::column('don_vi')->label('Đơn vị');
        CRUD::column('year')->label('Năm')->priority(4);
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
            'year' => 'required|integer',
        ]);

        // THÔNG TIN CƠ BẢN
        CRUD::addField([
            'name' => 'separator1',
            'type' => 'custom_html',
            'value' => '<h4 class="mb-3 text-uppercase">Thông tin cơ bản</h4>',
        ]);

        CRUD::addField([
            'name' => 'department_id',
            'label' => 'Phòng ban',
            'type' => 'select_from_array',
            'options' => \Modules\OrganizationStructure\Models\Department::orderBy('id', 'asc')
                ->pluck('name', 'id')
                ->toArray(),
            'wrapper' => ['class' => 'form-group col-md-6'],
            'allows_null' => true,
            'allows_multiple' => false,
            'attributes' => [
                'required' => 'required',
                'id' => 'department-select',
            ],
        ]);

        CRUD::addField([
            'name' => 'employee_id',
            'label' => 'Nhân sự',
            'type' => 'select_from_array',
            'options' => [],
            'wrapper' => ['class' => 'form-group col-md-6'],
            'allows_null' => true,
            'allows_multiple' => false,
            'attributes' => [
                'required' => 'required',
                'id' => 'employee-select',
                'disabled' => 'disabled',
            ],
        ]);

        CRUD::field('year')->label('Năm')->type('number')->default(date('Y'))
            ->wrapper(['class' => 'form-group col-md-4']);

        // THÔNG TIN CÁ NHÂN
        CRUD::addField([
            'name' => 'separator2',
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mb-3 text-uppercase">Thông tin cá nhân</h4>',
        ]);

        CRUD::addField([
            'name' => 'ho_ten',
            'label' => 'Họ và tên',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'ho-ten-field',
                'placeholder' => 'Chọn nhân sự để tự động điền',
            ],
        ]);

        CRUD::addField([
            'name' => 'nhap_ngu',
            'label' => 'Nhập ngũ (TĐ)',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'nhap-ngu-field',
                'placeholder' => 'mm/yyyy',
            ],
        ]);

        CRUD::addField([
            'name' => 'chuc_vu',
            'label' => 'Chức vụ (CNQS)',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'chuc-vu-field',
                'placeholder' => '--',
            ],
        ]);

        // LƯƠNG HIỆN HƯỞNG
        CRUD::addField([
            'name' => 'separator3',
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mb-3 text-uppercase">Lương hiện hưởng</h4>',
        ]);

        CRUD::field('luong_hien_loai_nhom')->label('Loại nhóm (MS)')->type('text')
            ->wrapper(['class' => 'form-group col-md-3'])
            ->hint('Ví dụ: TC, SC, CC1, CC2');

        CRUD::field('luong_hien_bac')->label('Bậc L')->type('number')
            ->wrapper(['class' => 'form-group col-md-3']);

        CRUD::field('luong_hien_he_so')->label('Hệ số')->type('number')
            ->wrapper(['class' => 'form-group col-md-3'])
            ->attributes(['step' => '0.01']);

        CRUD::field('luong_hien_phan_tram_tn_vk')->label('% TN VK')->type('number')
            ->wrapper(['class' => 'form-group col-md-3'])
            ->attributes(['step' => '0.01']);

        CRUD::field('luong_hien_he_so_bl')->label('Hệ số BL')->type('number')
            ->wrapper(['class' => 'form-group col-md-4'])
            ->attributes(['step' => '0.01']);

        CRUD::addField([
            'name' => 'luong_hien_quan_ham',
            'label' => 'Quân hàm QNCN',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'quan-ham-field',
                'placeholder' => 'Tự động điền khi chọn nhân sự',
            ],
        ]);

        CRUD::field('luong_hien_thang_nhan')->label('Tháng năm nhận')->type('text')
            ->wrapper(['class' => 'form-group col-md-4'])
            ->hint('Ví dụ: 7/20');

        // XẾP LƯƠNG MỚI
        CRUD::addField([
            'name' => 'separator4',
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mb-3 text-uppercase">Xếp lương mới</h4>',
        ]);

        CRUD::field('luong_moi_loai_nhom')->label('Loại nhóm (MS)')->type('text')
            ->wrapper(['class' => 'form-group col-md-3']);

        CRUD::field('luong_moi_bac')->label('Bậc L')->type('number')
            ->wrapper(['class' => 'form-group col-md-3']);

        CRUD::field('luong_moi_he_so')->label('Hệ số')->type('number')
            ->wrapper(['class' => 'form-group col-md-3'])
            ->attributes(['step' => '0.01']);

        CRUD::field('luong_moi_phan_tram_tn_vk')->label('% TN VK')->type('number')
            ->wrapper(['class' => 'form-group col-md-3'])
            ->attributes(['step' => '0.01']);

        CRUD::field('luong_moi_he_so_bl')->label('Hệ số BL')->type('number')
            ->wrapper(['class' => 'form-group col-md-4'])
            ->attributes(['step' => '0.01']);

        CRUD::field('luong_moi_thang_qd_huong')->label('Thăng Quân hàm QNCN')->type('text')
            ->wrapper(['class' => 'form-group col-md-4'])
            ->hint('Ví dụ: 9/23');

        CRUD::field('luong_moi_thang_nam_nhan')->label('Tháng năm nhận QNCN')->type('text')
            ->wrapper(['class' => 'form-group col-md-4'])
            ->hint('Ví dụ: 10/24');

        // THÔNG TIN KHÁC
        CRUD::addField([
            'name' => 'separator5',
            'type' => 'custom_html',
            'value' => '<hr><h4 class="mb-3 text-uppercase">Thông tin khác</h4>',
        ]);

        CRUD::field('ghi_chu')->label('Ghi chú')->type('textarea')
            ->wrapper(['class' => 'form-group col-md-12'])
            ->attributes(['rows' => 3]);

        // JavaScript để auto-fill
        CRUD::addField([
            'name' => 'auto_fill_script',
            'type' => 'custom_html',
            'value' => $this->getAutoFillScript(),
        ]);
    }

    private function getAutoFillScript()
    {
        return <<<'HTML'
<script>
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department-select');
    const employeeSelect = document.getElementById('employee-select');
    
    // Function to load employees by department
    function loadEmployeesByDepartment(departmentId, selectedEmployeeId = null) {
        if (!departmentId) {
            employeeSelect.disabled = true;
            employeeSelect.innerHTML = '<option value="">- Chọn phòng ban trước -</option>';
            return;
        }
        
        // Fetch employees by department
        fetch(`/salary-up-record/api/employees-by-department/${departmentId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network error');
                }
                return response.json();
            })
            .then(data => {
                employeeSelect.innerHTML = '<option value="">- Chọn nhân sự -</option>';
                
                if (data && data.length > 0) {
                    data.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.id;
                        option.textContent = emp.name;
                        // Set selected nếu đang edit
                        if (selectedEmployeeId && emp.id == selectedEmployeeId) {
                            option.selected = true;
                        }
                        employeeSelect.appendChild(option);
                    });
                } else {
                    employeeSelect.innerHTML = '<option value="">- Không có nhân viên -</option>';
                }
                
                employeeSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi khi tải danh sách nhân viên');
                employeeSelect.innerHTML = '<option value="">- Lỗi -</option>';
            });
    }
    
    // Khi chọn Phòng ban → Load nhân viên trong phòng đó
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            loadEmployeesByDepartment(this.value);
        });
        
        // Nếu đang edit, load nhân viên từ department đã chọn
        const currentDepartmentId = departmentSelect.value;
        const currentEmployeeId = employeeSelect.value;
        
        if (currentDepartmentId && currentEmployeeId) {
            // Đang edit - load employees và set selected
            loadEmployeesByDepartment(currentDepartmentId, currentEmployeeId);
        }
    }

    // Khi chọn Nhân viên → Auto-fill thông tin
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
            const employeeId = this.value;

            if (!employeeId) {
                // Clear fields
                document.getElementById('ho-ten-field').value = '';
                document.getElementById('nhap-ngu-field').value = '';
                document.getElementById('chuc-vu-field').value = '';
                document.getElementById('quan-ham-field').value = '';
                return;
            }

            // Fetch employee info
            fetch(`/salary-up-record/api/employee-info/${employeeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error');
                    }
                    return response.json();
                })
                .then(emp => {
                    // Auto-fill thông tin
                    document.getElementById('ho-ten-field').value = emp.name || '';
                    document.getElementById('nhap-ngu-field').value = emp.enlist_date || '';
                    document.getElementById('chuc-vu-field').value = emp.position_name || '--';
                    document.getElementById('quan-ham-field').value = emp.rank_code || '--';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Lỗi khi tải thông tin nhân viên');
                });
        });
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
        
        // Khi edit, cần load employee vào dropdown
        $entry = $this->crud->getCurrentEntry();
        if ($entry && $entry->employee_id) {
            $employee = \Modules\OrganizationStructure\Models\Employee::find($entry->employee_id);
            if ($employee) {
                // Update employee field options với employee hiện tại
                CRUD::modifyField('employee_id', [
                    'options' => [$employee->id => $employee->name],
                    'attributes' => [
                        'required' => 'required',
                        'id' => 'employee-select',
                        'data-current-value' => $employee->id,
                    ],
                ]);
            }
        }
    }

    /**
     * API: Lấy danh sách nhân viên theo phòng ban
     */
    public function getEmployeesByDepartment($departmentId)
    {
        try {
            $employees = \Modules\OrganizationStructure\Models\Employee::where('department_id', $departmentId)
                ->select('id', 'name')
                ->orderBy('id', 'asc')
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
        $employee = \Modules\OrganizationStructure\Models\Employee::with('position')->find($employeeId);
        
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        
        return response()->json([
            'id' => $employee->id,
            'name' => $employee->name,
            'enlist_date' => $employee->enlist_date ?? '--',
            'position_name' => $employee->position ? $employee->position->name : '--',
            'rank_code' => $employee->rank_code ?? '--',
        ]);
    }

    protected function setupShowOperation()
    {
        if (!PermissionHelper::userCan('record_management.view')) {
            abort(403, 'Không có quyền xem');
        }

        $this->crud->set('show.setFromDb', false);

        // Show all fields
        CRUD::column('id')->label('ID');
        CRUD::column('ho_ten')->label('Họ và tên');
        CRUD::column('employee.name')->label('Nhân viên liên kết');
        CRUD::column('department.name')->label('Phòng ban');
        CRUD::column('year')->label('Năm');

        CRUD::column('nhap_ngu')->label('Nhập ngũ (TĐ)');
        CRUD::column('chuc_vu')->label('Chức vụ');

        CRUD::column('luong_hien_loai_nhom')->label('LH - Loại nhóm');
        CRUD::column('luong_hien_bac')->label('LH - Bậc');
        CRUD::column('luong_hien_he_so')->label('LH - Hệ số');
        CRUD::column('luong_hien_phan_tram_tn_vk')->label('LH - % TN VK');
        CRUD::column('luong_hien_he_so_bl')->label('LH - Hệ số BL');
        CRUD::column('luong_hien_quan_ham')->label('LH - Quân hàm');
        CRUD::column('luong_hien_thang_nhan')->label('LH - Tháng nhận');

        CRUD::column('luong_moi_loai_nhom')->label('LM - Loại nhóm');
        CRUD::column('luong_moi_bac')->label('LM - Bậc');
        CRUD::column('luong_moi_he_so')->label('LM - Hệ số');
        CRUD::column('luong_moi_phan_tram_tn_vk')->label('LM - % TN VK');
        CRUD::column('luong_moi_he_so_bl')->label('LM - Hệ số BL');
        CRUD::column('luong_moi_thang_qd_huong')->label('LM - Tháng QĐ hưởng');
        CRUD::column('luong_moi_thang_nam_nhan')->label('LM - Tháng năm nhận');

        CRUD::column('don_vi')->label('Đơn vị');
        CRUD::column('ghi_chu')->label('Ghi chú')->type('textarea');

        CRUD::column('created_at')->label('Ngày tạo')->type('datetime')->format('DD/MM/YYYY HH:mm');
        CRUD::column('updated_at')->label('Cập nhật lần cuối')->type('datetime')->format('DD/MM/YYYY HH:mm');
    }
}

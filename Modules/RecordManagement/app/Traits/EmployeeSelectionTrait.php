<?php

namespace Modules\RecordManagement\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

trait EmployeeSelectionTrait
{
    /**
     * Add department selection field
     */
    protected function addDepartmentField()
    {
        $departments = \Modules\OrganizationStructure\Models\Department::pluck('name', 'id')->toArray();
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

    /**
     * Add employee selection field
     */
    protected function addEmployeeField()
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

    /**
     * Get common auto-fill script for department and employee selection
     */
    protected function getEmployeeSelectionScript()
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

        // Get route prefix from URL - handle both /so-dieu-dong-record/create and /admin/so-dieu-dong-record/create
        const pathParts = window.location.pathname.split('/').filter(part => part !== '');
        let routePrefix = '';
        
        // Find the route prefix (first part that's not 'admin')
        for (let i = 0; i < pathParts.length; i++) {
            if (pathParts[i] !== 'admin' && pathParts[i] !== 'create' && pathParts[i] !== 'edit') {
                routePrefix = pathParts[i];
                break;
            }
        }
        
        console.log('Full path:', window.location.pathname);
        console.log('Path parts:', pathParts);
        console.log('Route prefix:', routePrefix);
        const url = '/' + routePrefix + '/api/employees-by-department/' + departmentId;
        console.log('Fetching URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }
                });
            })
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
                console.error('Error loading employees:', error);
                console.error('URL attempted:', url);
                alert('Lỗi khi tải danh sách nhân viên: ' + error.message);
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
});
</script>
HTML;
    }

    /**
     * Get employee info auto-fill script
     */
    protected function getEmployeeInfoScript($fields = [])
    {
        $fieldMappings = '';
        foreach ($fields as $field => $employeeField) {
            $fieldMappings .= "document.getElementById('{$field}').value = emp.{$employeeField} || '';";
        }

        return <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function() {
    const employeeSelect = document.getElementById('employee-select');

    // When employee changes - auto-fill readonly fields from employees table
    if (employeeSelect) {
        employeeSelect.addEventListener('change', function() {
            const employeeId = this.value;

            if (!employeeId) {
                clearEmployeeFields();
                return;
            }

            // Get route prefix from URL - handle both /so-dieu-dong-record/create and /admin/so-dieu-dong-record/create
            const pathParts = window.location.pathname.split('/').filter(part => part !== '');
            let routePrefix = '';
            
            // Find the route prefix (first part that's not 'admin')
            for (let i = 0; i < pathParts.length; i++) {
                if (pathParts[i] !== 'admin' && pathParts[i] !== 'create' && pathParts[i] !== 'edit') {
                    routePrefix = pathParts[i];
                    break;
                }
            }
            const url = '/' + routePrefix + '/api/employee-info/' + employeeId;
            
            fetch(url)
                .then(response => response.json())
                .then(emp => {
                    // Auto-fill thông tin vào các input fields
                    {$fieldMappings}
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Lỗi khi tải thông tin nhân viên');
                });
        });
    }

    function clearEmployeeFields() {
        // Clear all employee-related fields
        const fieldsToClear = ['ho-ten-field', 'ngay-sinh-field', 'gioi-tinh-field', 'quan-ham-field', 'chuc-vu-field', 'nhap-ngu-field', 'cccd-field', 'dia-chi-field', 'dien-thoai-field'];
        fieldsToClear.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) field.value = '';
        });
    }
});
</script>
HTML;
    }

    /**
     * Add employee info display fields
     */
    protected function addEmployeeInfoFields($fields = [])
    {
        $defaultFields = [
            'ho_ten' => ['label' => 'Họ đệm khai sinh', 'field' => 'name'],
            'ngay_sinh' => ['label' => 'Ngày tháng năm sinh', 'field' => 'date_of_birth'],
            'gioi_tinh' => ['label' => 'Giới tính', 'field' => 'gender_text'],
            'quan_ham' => ['label' => 'Quân hàm', 'field' => 'rank_code'],
            'chuc_vu' => ['label' => 'Chức vụ', 'field' => 'position_name'],
            'nhap_ngu' => ['label' => 'Nhập ngũ', 'field' => 'enlist_date'],
            'cccd' => ['label' => 'Số CCCD', 'field' => 'CCCD'],
            'dia_chi' => ['label' => 'Địa chỉ', 'field' => 'address'],
            'dien_thoai' => ['label' => 'Điện thoại', 'field' => 'phone'],
        ];

        $fieldsToShow = empty($fields) ? $defaultFields : array_intersect_key($defaultFields, array_flip($fields));

        foreach ($fieldsToShow as $fieldName => $config) {
            CRUD::addField([
                'name' => "display_{$fieldName}",
                'label' => $config['label'],
                'type' => 'text',
                'wrapper' => ['class' => 'form-group col-md-4'],
                'attributes' => [
                    'readonly' => 'readonly',
                    'id' => str_replace('_', '-', $fieldName) . '-field',
                    'class' => 'form-control bg-light',
                    'tabindex' => '-1',
                ],
                'fake' => true,
                'store_in' => false,
            ]);
        }
    }
}

<?php

namespace Modules\RecordManagement\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\RecordManagement\Models\AnDuongRecord;
use Modules\RecordManagement\Traits\EmployeeSelectionTrait;
use App\Helpers\DateHelper;
use App\Helpers\PermissionHelper;

class AnDuongRecordCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use EmployeeSelectionTrait;

    public function setup()
    {
        CRUD::setModel(AnDuongRecord::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/an-duong-record');
        CRUD::setEntityNameStrings('Sổ đăng ký an dưỡng, bồi dưỡng', 'Thêm mới');
        
        // Override button text
        CRUD::setOperationSetting('buttons.create', [
            'content' => '<i class="la la-plus"></i> Thêm mới',
            'class' => 'btn btn-primary',
        ]);

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

        CRUD::orderBy('stt', 'asc');

        // Columns
        CRUD::column('stt')
            ->label('STT')
            ->type('text')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('stt', 'like', '%' . $searchTerm . '%');
            })
            ->format(function ($value, $entry) {
                return $entry->stt ?: $entry->id;
            });

        CRUD::column('year')
            ->label('Năm')
            ->type('text')
            ->priority(1)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('year', 'like', '%' . $searchTerm . '%');
            });

        CRUD::column('ho_va_ten')
            ->label('Họ và tên')
            ->type('text')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('ho_va_ten', 'like', '%' . $searchTerm . '%');
            });

        CRUD::column('cap_bac')
            ->label('Cấp bậc')
            ->type('text')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('cap_bac', 'like', '%' . $searchTerm . '%');
            });

        CRUD::column('chuc_vu')
            ->label('Chức vụ')
            ->type('text')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('chuc_vu', 'like', '%' . $searchTerm . '%');
            });

        CRUD::column('tieu_chuan_duoc_huong')
            ->label('Tiêu chuẩn được hưởng')
            ->type('text')
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('tieu_chuan_duoc_huong', 'like', '%' . $searchTerm . '%');
            });

        CRUD::column('ghi_chu')
            ->label('Ghi chú')
            ->type('text')
            ->limit(50)
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhere('ghi_chu', 'like', '%' . $searchTerm . '%');
            });

        // Department column (only show if user has global scope)
        if (PermissionHelper::getUserScope(backpack_user()) === 'global') {
            CRUD::column('department.name')
                ->label('Phòng ban')
                ->type('text')
                ->searchLogic(function ($query, $column, $searchTerm) {
                    $query->orWhereHas('department', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    });
                });
        }
    }

    protected function setupCreateOperation()
    {
        if (!PermissionHelper::userCan('record_management.create')) {
            abort(403, 'Không có quyền tạo mới');
        }

        CRUD::setValidation([
            'year' => 'required|integer|min:2020|max:2030',
            'tieu_chuan_duoc_huong' => 'nullable|string',
            'ghi_chu' => 'nullable|string',
        ]);

        // YEAR SELECTION BUTTONS
        CRUD::addField([
            'name' => 'year_buttons',
            'type' => 'custom_html',
            'value' => $this->getYearButtonsScript(),
        ]);

        // Employee selection
        $this->addDepartmentField();
        $this->addEmployeeField();

        // Auto-filled fields (readonly)
        CRUD::addField([
            'name' => 'ho_va_ten',
            'label' => 'Họ và tên',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'ho-ten-field',
                'placeholder' => '--',
            ],
        ]);

        CRUD::addField([
            'name' => 'cap_bac',
            'label' => 'Cấp bậc',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'cap-bac-field',
                'placeholder' => '--',
            ],
        ]);

        CRUD::addField([
            'name' => 'chuc_vu',
            'label' => 'Chức vụ',
            'type' => 'text',
            'wrapper' => ['class' => 'form-group col-md-4'],
            'attributes' => [
                'readonly' => 'readonly',
                'id' => 'chuc-vu-field',
                'placeholder' => '--',
            ],
        ]);

        // Manual input fields
        CRUD::field('tieu_chuan_duoc_huong')
            ->label('Tiêu chuẩn được hưởng')
            ->type('textarea');

        CRUD::field('ghi_chu')
            ->label('Ghi chú')
            ->type('textarea');

        // Year field (hidden)
        CRUD::addField([
            'name' => 'year',
            'type' => 'hidden',
            'value' => date('Y'),
            'attributes' => [
                'id' => 'year-input',
            ],
        ]);

        // JavaScript để auto-fill
        CRUD::addField([
            'name' => 'auto_fill_script',
            'type' => 'custom_html',
            'value' => $this->getAutoFillScript(),
        ]);
    }

    protected function setupUpdateOperation()
    {
        if (!PermissionHelper::userCan('record_management.update')) {
            abort(403, 'Không có quyền chỉnh sửa');
        }

        $this->setupCreateOperation();
    }

    protected function setupShowOperation()
    {
        if (!PermissionHelper::userCan('record_management.view')) {
            abort(403, 'Không có quyền xem');
        }

        CRUD::column('stt')->label('STT');
        CRUD::column('year')->label('Năm')->priority(1);
        CRUD::column('ho_va_ten')->label('Họ và tên');
        CRUD::column('cap_bac')->label('Cấp bậc');
        CRUD::column('chuc_vu')->label('Chức vụ');
        CRUD::column('tieu_chuan_duoc_huong')->label('Tiêu chuẩn được hưởng');
        CRUD::column('ghi_chu')->label('Ghi chú');
        
        if (PermissionHelper::getUserScope(backpack_user()) === 'global') {
            CRUD::column('department.name')->label('Phòng ban');
        }
        
        CRUD::column('created_at')->label('Ngày tạo');
        CRUD::column('updated_at')->label('Ngày cập nhật');
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
            'rank_code' => $employee->rank_code ?? '--',
            'position_name' => $employee->position ? $employee->position->name : '--',
        ]);
    }

    private function getYearButtonsScript()
    {
        $currentYear = date('Y');
        $years = [];
        for ($i = 2021; $i <= 2026; $i++) {
            $years[] = $i;
        }
        
        $buttonsHtml = '<div class="form-group mb-3">';
        $buttonsHtml .= '<label class="form-label">Năm:</label><br>';
        $buttonsHtml .= '<div class="btn-group" role="group">';
        
        foreach ($years as $year) {
            $activeClass = $year == $currentYear ? 'btn-danger' : 'btn-outline-secondary';
            $buttonsHtml .= "<button type='button' class='btn {$activeClass} year-btn' data-year='{$year}'>{$year}</button>";
        }
        
        $buttonsHtml .= '</div>';
        $buttonsHtml .= '</div>';
        
        $buttonsHtml .= <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearButtons = document.querySelectorAll('.year-btn');
    const yearInput = document.querySelector('input[name="year"]');
    
    yearButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            yearButtons.forEach(b => {
                b.classList.remove('btn-danger');
                b.classList.add('btn-outline-secondary');
            });
            
            // Add active class to clicked button
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-danger');
            
            // Update hidden input
            if (yearInput) {
                yearInput.value = this.dataset.year;
            }
        });
    });
});
</script>
HTML;
        
        return $buttonsHtml;
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
        fetch(`/an-duong-record/api/employees-by-department/${departmentId}`)
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
                document.getElementById('cap-bac-field').value = '';
                document.getElementById('chuc-vu-field').value = '';
                return;
            }

            // Fetch employee info
            fetch(`/an-duong-record/api/employee-info/${employeeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network error');
                    }
                    return response.json();
                })
                .then(emp => {
                    // Auto-fill thông tin
                    document.getElementById('ho-ten-field').value = emp.name || '';
                    document.getElementById('cap-bac-field').value = emp.rank_code || '--';
                    document.getElementById('chuc-vu-field').value = emp.position_name || '--';
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

    /**
     * Store method to auto-generate STT
     */
    public function store()
    {
        // Auto-generate STT if not provided
        if (empty(request('stt'))) {
            $maxStt = AnDuongRecord::max('stt') ?? 0;
            request()->merge(['stt' => $maxStt + 1]);
        }

        return $this->traitStore();
    }

    /**
     * Update method to maintain STT
     */
    public function update()
    {
        return parent::update();
    }
}

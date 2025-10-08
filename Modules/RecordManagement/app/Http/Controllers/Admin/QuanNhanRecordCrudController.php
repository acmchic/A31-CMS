<?php

namespace Modules\RecordManagement\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Modules\RecordManagement\Models\QuanNhanRecord;
use Modules\RecordManagement\Traits\EmployeeSelectionTrait;
use App\Helpers\DateHelper;
use App\Helpers\PermissionHelper;

class QuanNhanRecordCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use EmployeeSelectionTrait;

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

        CRUD::column('employee.name')->label('Họ tên')->priority(1)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhereHas('employee', function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%'.$searchTerm.'%');
            });
        });

        CRUD::column('department_name')
            ->label('Phòng ban')
            ->type('closure')
            ->function(function($entry) {
                return $entry->department ? $entry->department->name : 'Chưa có';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas('department', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            })
            ->priority(2);

        CRUD::column('position_name')
            ->label('Chức vụ')
            ->type('closure')
            ->function(function($entry) {
                return $entry->employee && $entry->employee->position ? $entry->employee->position->name : 'Chưa có';
            })
            ->searchLogic(function ($query, $column, $searchTerm) {
                $query->orWhereHas('employee.position', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%');
                });
            })
            ->priority(3);

        CRUD::column('employee.rank_code')->label('Cấp bậc')->priority(4)->searchLogic(function ($query, $column, $searchTerm) {
            $query->orWhereHas('employee', function ($q) use ($searchTerm) {
                $q->where('rank_code', 'like', '%'.$searchTerm.'%');
            });
        });
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

        // Hiển thị tất cả thông tin từ employees
        $this->addEmployeeInfoFields();





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
            'value' => $this->getEmployeeSelectionScript() . $this->getEmployeeInfoScript([
                'ho-ten-field' => 'name',
                'ngay-sinh-field' => 'date_of_birth',
                'gioi-tinh-field' => 'gender_text',
                'quan-ham-field' => 'rank_code',
                'chuc-vu-field' => 'position_name',
                'nhap-ngu-field' => 'enlist_date',
                'cccd-field' => 'CCCD',
                'dia-chi-field' => 'address',
                'dien-thoai-field' => 'phone'
            ]),
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
            CRUD::column('emp_ho_ten')->label('Họ tên đệm khai sinh')->type('text')->value($employee->name);
            CRUD::column('emp_ngay_sinh')
                ->label('Ngày sinh')
                ->type('closure')
                ->function(function($entry) use ($employee) {
                    return DateHelper::formatDate($employee->date_of_birth);
                });
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
        CRUD::column('ngay_nhan_cap')
            ->label('Ngày cấp')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->ngay_nhan_cap);
            });
        CRUD::column('ngay_cap_cc')
            ->label('Ngày cấp CM, thẻ, CC')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->ngay_cap_cc);
            });
        CRUD::column('cnqs')->label('Chứng minh quân sự');
        CRUD::column('bac_ky_thuat')->label('Bậc kỹ thuật');
        CRUD::column('tai_ngu')->label('Tái ngũ');
        CRUD::column('ngay_chuyen_qncn')
            ->label('Ngày chuyển QNCN')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->ngay_chuyen_qncn);
            });
        CRUD::column('ngay_chuyen_cnv')
            ->label('Ngày chuyển CNV')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->ngay_chuyen_cnv);
            });
        CRUD::column('luong_nhom_ngach_bac')->label('Lương: nhóm ngạch bậc');

        // THÔNG TIN CHÍNH TRỊ
        CRUD::column('ngay_vao_doan')
            ->label('Ngày vào Đoàn')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->ngay_vao_doan);
            });
        CRUD::column('ngay_vao_dang')
            ->label('Ngày vào Đảng')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->ngay_vao_dang);
            });
        CRUD::column('ngay_chinh_thuc')
            ->label('Ngày chính thức Đảng')
            ->type('closure')
            ->function(function($entry) {
                return DateHelper::formatDate($entry->ngay_chinh_thuc);
            });

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


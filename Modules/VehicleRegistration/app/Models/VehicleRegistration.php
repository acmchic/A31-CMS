<?php

namespace Modules\VehicleRegistration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class VehicleRegistration extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;

    protected $fillable = [
        'user_id',
        'vehicle_id', 
        'driver_id',
        'departure_date',
        'return_date',
        'departure_time',
        'return_time',
        'departure_datetime',
        'return_datetime',
        'route',
        'purpose',
        'passenger_count',
        'cargo_description',
        'driver_name',
        'driver_license',
        'status',
        'workflow_status',
        'department_approved_by',
        'department_approved_at',
        'digital_signature_dept',
        'director_approved_by', 
        'director_approved_at',
        'digital_signature_director',
        'rejection_reason',
        'rejection_level',
        'signed_pdf_path',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'departure_time' => 'datetime:H:i',
        'return_time' => 'datetime:H:i',
        'departure_datetime' => 'datetime',
        'return_datetime' => 'datetime',
        'department_approved_at' => 'datetime',
        'director_approved_at' => 'datetime',
        'passenger_count' => 'integer'
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(\Modules\VehicleRegistration\Models\Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(\Modules\OrganizationStructure\Models\Employee::class, 'driver_id');
    }

    public function departmentApprover()
    {
        return $this->belongsTo(\App\Models\User::class, 'department_approved_by');
    }

    public function directorApprover()
    {
        return $this->belongsTo(\App\Models\User::class, 'director_approved_by');
    }

    // Accessors
    public function getStatusDisplayAttribute()
    {
        $statuses = [
            'pending' => 'Chờ duyệt',
            'dept_approved' => 'Phòng ban đã duyệt',
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Đã từ chối'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    // Backward compatibility - combine date and time into datetime
    public function getDepartureDatetimeAttribute()
    {
        if ($this->attributes['departure_datetime']) {
            return $this->attributes['departure_datetime'];
        }
        
        // Fallback to combining date and time
        if ($this->departure_date && $this->departure_time) {
            return $this->departure_date->format('Y-m-d') . ' ' . $this->departure_time->format('H:i:s');
        }
        
        return null;
    }

    public function getReturnDatetimeAttribute()
    {
        if ($this->attributes['return_datetime']) {
            return $this->attributes['return_datetime'];
        }
        
        // Fallback to combining date and time
        if ($this->return_date && $this->return_time) {
            return $this->return_date->format('Y-m-d') . ' ' . $this->return_time->format('H:i:s');
        }
        
        return null;
    }

    public function getWorkflowStatusDisplayAttribute()
    {
        $workflows = [
            'submitted' => 'Đã gửi',
            'dept_review' => 'Phòng ban xem xét',
            'director_review' => 'Ban giám đốc xem xét',
            'approved' => 'Đã duyệt',
            'rejected' => 'Đã từ chối'
        ];
        
        return $workflows[$this->workflow_status] ?? $this->workflow_status;
    }

    // Helper methods
    public function canBeApprovedByDepartment()
    {
        return $this->workflow_status === 'submitted';
    }

    public function canBeApprovedByDirector() 
    {
        return $this->workflow_status === 'dept_review';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // 3-Step Workflow Buttons
    public function assignVehicleButton()
    {
        // Check permission first
        if (!\App\Helpers\PermissionHelper::userCan('vehicle_registration.assign')) {
            return '';
        }
        
        // Step 2: Đội trưởng xe phân công
        if ($this->workflow_status === 'submitted' && !$this->vehicle_id) {
            return '<a class="btn btn-sm btn-warning" href="' . backpack_url('vehicle-registration/' . $this->id . '/assign-vehicle') . '">
                <i class="la la-car"></i> Phân xe & lái xe
            </a>';
        }
        return '';
    }

    public function approveButton()
    {
        // Step 3: Ban Giám Đốc phê duyệt - show button for testing
        if ($this->workflow_status === 'dept_review' && $this->vehicle_id) {
            return '<button class="btn btn-sm btn-success approve-btn" 
                        onclick="
                            var pin = prompt(\'Nhập PIN chứng thư số A1:\', \'\');
                            if (pin) {
                                if (confirm(\'Xác nhận phê duyệt với PIN: \' + pin + \'?\')) {
                                    var form = document.createElement(\'form\');
                                    form.method = \'POST\';
                                    form.action = \'' . route('vehicle-registration.approve-with-pin', $this->id) . '\';
                                    
                                    var csrfToken = document.createElement(\'input\');
                                    csrfToken.type = \'hidden\';
                                    csrfToken.name = \'_token\';
                                    csrfToken.value = document.querySelector(\'meta[name=csrf-token]\').getAttribute(\'content\');
                                    form.appendChild(csrfToken);
                                    
                                    var pinInput = document.createElement(\'input\');
                                    pinInput.type = \'hidden\';
                                    pinInput.name = \'certificate_pin\';
                                    pinInput.value = pin;
                                    form.appendChild(pinInput);
                                    
                                    var regInput = document.createElement(\'input\');
                                    regInput.type = \'hidden\';
                                    regInput.name = \'registration_id\';
                                    regInput.value = \'' . $this->id . '\';
                                    form.appendChild(regInput);
                                    
                                    document.body.appendChild(form);
                                    
                                    // Submit with AJAX
                                    var formData = new FormData(form);
                                    fetch(form.action, {
                                        method: \'POST\',
                                        body: formData,
                                        headers: {
                                            \'X-Requested-With\': \'XMLHttpRequest\'
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert(\'✅ Phê duyệt thành công! PDF đã được ký số.\');
                                            window.location.reload();
                                        } else {
                                            alert(\'❌ Lỗi: \' + (data.message || \'Không thể phê duyệt\'));
                                        }
                                    })
                                    .catch(error => {
                                        alert(\'❌ Có lỗi xảy ra: \' + error);
                                    });
                                    
                                    document.body.removeChild(form);
                                }
                            }
                        ">
                <i class="la la-check"></i> Phê duyệt với PIN
            </button>';
        }
        return '';
    }

    public function rejectButton()
    {
        // Check permission first
        if (!\App\Helpers\PermissionHelper::userCan('vehicle_registration.approve')) {
            return '';
        }
        
        // Có thể từ chối ở bất kỳ step nào
        if (in_array($this->workflow_status, ['submitted', 'dept_review'])) {
            return '<a class="btn btn-sm btn-danger" href="' . backpack_url('vehicle-registration/' . $this->id . '/reject') . '" 
                onclick="return confirm(\'Bạn có chắc chắn muốn từ chối đăng ký này?\')">
                <i class="la la-times"></i> Từ chối
            </a>';
        }
        return '';
    }

    public function downloadPdfButton()
    {
        // Check permission first - anyone who can view can download
        if (!\App\Helpers\PermissionHelper::userCan('vehicle_registration.view')) {
            return '';
        }
        
        // Tải PDF khi đã approved
        if ($this->status === 'approved') {
            return '<a class="btn btn-sm btn-info" href="' . backpack_url('vehicle-registration/' . $this->id . '/download-pdf') . '" target="_blank">
                <i class="la la-download"></i> Tải PDF
            </a>';
        }
        return '';
    }
    
    public function checkSignatureButton()
    {
        // Check permission first
        if (!\App\Helpers\PermissionHelper::userCan('vehicle_registration.approve')) {
            return '';
        }
        
        // Kiểm tra chữ ký khi đã có PDF
        if ($this->status === 'approved' && $this->signed_pdf_path) {
            return '<a class="btn btn-sm btn-warning" href="' . backpack_url('vehicle-registration/' . $this->id . '/check-signature') . '" target="_blank">
                <i class="la la-certificate"></i> Kiểm tra chữ ký
            </a>';
        }
        return '';
    }
}

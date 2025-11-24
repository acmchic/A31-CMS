<?php

namespace Modules\VehicleRegistration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Modules\ApprovalWorkflow\Traits\HasApprovalWorkflow;
use Modules\ApprovalWorkflow\Traits\HasDigitalSignature;
use Modules\ApprovalWorkflow\Traits\ApprovalButtons;

class VehicleRegistration extends Model
{
    use HasFactory, SoftDeletes, CrudTrait;
    use HasApprovalWorkflow, HasDigitalSignature, ApprovalButtons;

    // ✅ Configure ApprovalWorkflow
    protected $workflowType = 'two_level';
    protected $pdfView = 'vehicleregistration::pdf.registration';
    protected $pdfDirectory = 'vehicle_registrations';

    // ✅ Set default workflow_status for VehicleRegistration
    protected $attributes = [
        'workflow_status' => 'submitted', // VehicleRegistration starts with 'submitted', not 'pending'
        'status' => 'pending',
    ];

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
        'selected_approvers',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    protected $casts = [
        'departure_date' => 'date',
        'selected_approvers' => 'array',
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

    // ✅ Map old column names to ApprovalWorkflow convention
    public function getWorkflowLevel1ByAttribute()
    {
        return $this->attributes['department_approved_by'] ?? null;
    }

    public function getWorkflowLevel1AtAttribute()
    {
        return isset($this->attributes['department_approved_at']) ? $this->attributes['department_approved_at'] : null;
    }

    public function getWorkflowLevel2ByAttribute()
    {
        return $this->attributes['director_approved_by'] ?? null;
    }

    public function getWorkflowLevel2AtAttribute()
    {
        return isset($this->attributes['director_approved_at']) ? $this->attributes['director_approved_at'] : null;
    }

    // ✅ Override relationships để dùng cột cũ
    public function level1Approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'department_approved_by');
    }

    public function level2Approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'director_approved_by');
    }

    // ✅ Override module permission
    protected function getModulePermission(): string
    {
        return 'vehicle_registration';
    }

    // ✅ Custom PDF title
    public function getPdfTitle(): string
    {
        return 'Đăng ký xe số ' . $this->id;
    }

    // ✅ Custom PDF filename for download
    public function getPdfFilename(): string
    {
        return 'dang_ky_xe_' . $this->id . '.pdf';
    }

    // ✅ Custom PDF filename pattern for saving
    public function getCustomPdfFilename(): string
    {
        return 'dang_ky_xe.pdf';
    }

    // ✅ Override PDF owner username - use requester's username
    public function getCustomPdfOwnerUsername(): string
    {
        // Use username of person who requested (user_id)
        if ($this->user) {
            return $this->user->username ?? 'user_' . $this->user->id;
        }

        return 'unknown';
    }

    // ✅ Custom PDF data
    public function getPdfData(): array
    {
        $baseData = [
            'model' => $this,
            'approver' => $this->getCurrentLevelApprover(),
            'generated_at' => \Carbon\Carbon::now()->format('d/m/Y H:i:s'),
        ];

        return array_merge($baseData, [
            'registration' => $this,
            'requester' => $this->user,
            'vehicle' => $this->vehicle,
            'driver' => $this->driver,
        ]);
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

    // ✅ Override workflow status mapping for VehicleRegistration
    public function getWorkflowStatusDisplayAttribute(): string
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

    public function getNextWorkflowStep(): ?string
    {
        $currentStep = $this->getCurrentWorkflowStep();

        if ($currentStep === 'approved' && !$this->director_approved_by) {
            return 'approved';
        }

        $workflowMap = [
            'submitted' => 'dept_review',
            'dept_review' => 'director_review',
            'director_review' => 'approved',
            'approved' => null,
            'rejected' => null,
        ];

        return $workflowMap[$currentStep] ?? null;
    }

    public function canBeApproved(): bool
    {
        if ($this->workflow_status === 'approved' && $this->director_approved_by) {
            return false;
        }

        if ($this->workflow_status === 'approved' && !$this->director_approved_by) {
            return true;
        }

        if ($this->signed_pdf_path) {
            return false;
        }

        return in_array($this->workflow_status, ['dept_review', 'director_review']);
    }

    // ✅ Override canBeRejected
    public function canBeRejected(): bool
    {
        // Cannot reject if already has PDF
        if ($this->signed_pdf_path) {
            return false;
        }

        if ($this->workflow_status === 'approved') {
            return false;
        }

        // Can reject at submitted or dept_review
        return in_array($this->workflow_status, ['submitted', 'dept_review', 'director_review']);
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
        return $this->status === 'approved' || $this->workflow_status === 'approved';
    }

    public static function getDirectors()
    {
        return \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Ban Giám đốc', 'Ban Giam Doc', 'Ban Giám Đốc', 'Giám đốc']);
        })->get();
    }

    public function isUserSelectedApprover($userId)
    {
        if (!$this->selected_approvers) {
            return false;
        }

        $approverIds = is_array($this->selected_approvers)
            ? $this->selected_approvers
            : json_decode($this->selected_approvers, true);

        if (!is_array($approverIds)) {
            return false;
        }

        return in_array((int)$userId, array_map('intval', $approverIds));
    }

    public function isRejected()
    {
        return $this->status === 'rejected' || $this->workflow_status === 'rejected';
    }

    // ✅ Keep assignVehicleButton - specific to VehicleRegistration
    public function assignVehicleButton()
    {
        // Check permission first
        if (!\App\Helpers\PermissionHelper::userCan('vehicle_registration.assign')) {
            return '';
        }

        if ($this->workflow_status === 'submitted' && !$this->vehicle_id) {
            return '<a class="btn btn-sm btn-warning" href="' . backpack_url('vehicle-registration/' . $this->id . '/assign-vehicle') . '">
                <i class="la la-car"></i> lái xe
            </a>';
        }
        return '';
    }

    // ❌ REMOVED: approveButton(), rejectButton(), downloadPdfButton()
    // ✅ These are now provided by ApprovalButtons trait from ApprovalWorkflow module!
}

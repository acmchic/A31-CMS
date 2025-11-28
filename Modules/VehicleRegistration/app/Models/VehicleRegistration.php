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

    // ✅ Business fields only - workflow is now managed in approval_requests table
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

    /**
     * Get ApprovalRequest for this registration
     */
    public function approvalRequest()
    {
        return $this->morphOne(\Modules\ApprovalWorkflow\Models\ApprovalRequest::class, 'model', 'model_type', 'model_id');
    }

    /**
     * Get workflow status from approval_requests (accessor)
     */
    public function getWorkflowStatusAttribute()
    {
        // Check if column exists (for backward compatibility during migration)
        if (isset($this->attributes['workflow_status'])) {
            return $this->attributes['workflow_status'];
        }
        
        $approvalRequest = $this->approvalRequest;
        if ($approvalRequest) {
            return $approvalRequest->status;
        }
        return 'draft';
    }

    /**
     * Get status (backward compatibility accessor)
     */
    public function getStatusAttribute()
    {
        // Check if column exists (for backward compatibility during migration)
        if (isset($this->attributes['status'])) {
            return $this->attributes['status'];
        }
        
        return $this->getWorkflowStatusAttribute();
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

    /**
     * Override hasSignedPdf to get from approval_requests
     */
    public function hasSignedPdf(): bool
    {
        $approvalRequest = $this->approvalRequest;
        if ($approvalRequest && $approvalRequest->signed_pdf_path) {
            return \Storage::disk('public')->exists($approvalRequest->signed_pdf_path);
        }
        
        // Backward compatibility: check model field if exists
        if (isset($this->attributes['signed_pdf_path']) && !empty($this->attributes['signed_pdf_path'])) {
            return \Storage::disk('public')->exists($this->attributes['signed_pdf_path']);
        }
        
        return false;
    }

    /**
     * Get signed PDF path from approval_requests
     */
    public function getSignedPdfPathAttribute()
    {
        $approvalRequest = $this->approvalRequest;
        if ($approvalRequest && $approvalRequest->signed_pdf_path) {
            return $approvalRequest->signed_pdf_path;
        }
        
        // Backward compatibility
        if (isset($this->attributes['signed_pdf_path'])) {
            return $this->attributes['signed_pdf_path'];
        }
        
        return null;
    }

    // Accessors - get from approval_requests
    public function getStatusDisplayAttribute()
    {
        $approvalRequest = $this->approvalRequest;
        if ($approvalRequest) {
            return $approvalRequest->status_label;
        }
        return 'Nháp';
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

    // ✅ Get workflow status display from approval_requests
    public function getWorkflowStatusDisplayAttribute(): string
    {
        $approvalRequest = $this->approvalRequest;
        if ($approvalRequest) {
            return $approvalRequest->status_label;
        }
        return 'Nháp';
    }

    // ✅ Override methods to use approval_requests
    public function getNextWorkflowStep(): ?string
    {
        $approvalRequest = $this->approvalRequest;
        if (!$approvalRequest) {
            return null;
        }
        
        $currentStep = $approvalRequest->current_step;
        $steps = $approvalRequest->approval_steps ?? [];
        $currentIndex = array_search($currentStep, $steps);
        
        if ($currentIndex !== false && isset($steps[$currentIndex + 1])) {
            return $steps[$currentIndex + 1];
        }
        
        return null;
    }

    public function canBeApproved(): bool
    {
        $approvalRequest = $this->approvalRequest;
        if (!$approvalRequest) {
            return false;
        }
        
        $user = backpack_user();
        if (!$user) {
            return false;
        }
        
        return $approvalRequest->canBeApprovedBy($user);
    }

    public function canBeRejected(): bool
    {
        $approvalRequest = $this->approvalRequest;
        if (!$approvalRequest) {
            return false;
        }
        
        return in_array($approvalRequest->status, ['submitted', 'in_review']);
    }

    public function isApproved()
    {
        $approvalRequest = $this->approvalRequest;
        return $approvalRequest && $approvalRequest->status === 'approved';
    }

    public function isRejected()
    {
        $approvalRequest = $this->approvalRequest;
        return $approvalRequest && $approvalRequest->status === 'rejected';
    }

    public function isUserSelectedApprover($userId)
    {
        $approvalRequest = $this->approvalRequest;
        if (!$approvalRequest || !$approvalRequest->selected_approvers) {
            return false;
        }

        $selectedApprovers = is_array($approvalRequest->selected_approvers)
            ? $approvalRequest->selected_approvers
            : json_decode($approvalRequest->selected_approvers, true);

        if (!is_array($selectedApprovers)) {
            return false;
        }

        // Flatten selected_approvers if it's nested by step
        $allApprovers = [];
        foreach ($selectedApprovers as $stepApprovers) {
            if (is_array($stepApprovers)) {
                $allApprovers = array_merge($allApprovers, $stepApprovers);
            } else {
                $allApprovers[] = $stepApprovers;
            }
        }

        return in_array((int)$userId, array_map('intval', $allApprovers));
    }

    // ✅ Keep assignVehicleButton - specific to VehicleRegistration
    public function assignVehicleButton()
    {
        // Check permission first
        $user = backpack_user();
        if (!\App\Helpers\PermissionHelper::can($user, 'vehicle_registration.assign')) {
            return '';
        }

        $approvalRequest = $this->approvalRequest;
        
        // Hiển thị nút "Phân xe" khi:
        // 1. Có approvalRequest với status = 'submitted' và current_step = 'vehicle_picked' (chờ phân xe)
        // 2. HOẶC chưa có approvalRequest (draft) và chưa có vehicle_id (đơn mới tạo)
        // 3. Và chưa có vehicle_id (chưa được phân xe)
        if (!$this->vehicle_id) {
            if ($approvalRequest) {
                // Có approvalRequest: kiểm tra status và current_step
                if ($approvalRequest->status === 'submitted' && $approvalRequest->current_step === 'vehicle_picked') {
                    return '<a class="btn btn-sm btn-warning" href="' . backpack_url('vehicle-registration/' . $this->id . '/assign-vehicle') . '">
                        <i class="la la-car"></i> Phân xe
                    </a>';
                }
            } else {
                // Chưa có approvalRequest: đơn mới tạo, cho phép phân xe
                return '<a class="btn btn-sm btn-warning" href="' . backpack_url('vehicle-registration/' . $this->id . '/assign-vehicle') . '">
                    <i class="la la-car"></i> Phân xe
                </a>';
            }
        }
        
        return '';
    }

    // ❌ REMOVED: approveButton(), rejectButton(), downloadPdfButton()
    // ✅ These are now provided by ApprovalButtons trait from ApprovalWorkflow module!
}

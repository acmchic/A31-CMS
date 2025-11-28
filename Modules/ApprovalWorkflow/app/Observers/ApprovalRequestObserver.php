<?php

namespace Modules\ApprovalWorkflow\Observers;

use Modules\ApprovalWorkflow\Services\ApprovalRequestService;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer để tự động sync ApprovalRequest khi model thay đổi
 * 
 * Sử dụng trong AppServiceProvider:
 * EmployeeLeave::observe(ApprovalRequestObserver::class);
 */
class ApprovalRequestObserver
{
    protected $service;

    public function __construct(ApprovalRequestService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle the model "created" event.
     */
    public function created(Model $model)
    {
        $moduleType = $this->getModuleType($model);
        if ($moduleType) {
            $this->service->syncFromModel($model, $moduleType);
        }
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model)
    {
        // Chỉ sync nếu workflow_status thay đổi
        if ($model->wasChanged('workflow_status') || $model->wasChanged('selected_approvers')) {
            $moduleType = $this->getModuleType($model);
            if ($moduleType) {
                $this->service->syncFromModel($model, $moduleType);
            }
        }
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model)
    {
        $moduleType = $this->getModuleType($model);
        if ($moduleType) {
            \Modules\ApprovalWorkflow\Models\ApprovalRequest::where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->delete();
        }
    }

    /**
     * Get module type from model
     */
    protected function getModuleType(Model $model): ?string
    {
        $class = get_class($model);
        
        if (strpos($class, 'EmployeeLeave') !== false) {
            return 'leave';
        }
        
        if (strpos($class, 'VehicleRegistration') !== false) {
            return 'vehicle';
        }
        
        if (strpos($class, 'MaterialPlan') !== false) {
            return 'material_plan';
        }
        
        return null;
    }
}




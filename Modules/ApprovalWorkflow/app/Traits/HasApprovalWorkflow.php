<?php

namespace Modules\ApprovalWorkflow\Traits;


/**
 * Trait HasApprovalWorkflow
 * 
 * Add approval workflow capabilities to any model
 * 
 * Required database fields:
 * - workflow_status (enum or string)
 * - workflow_level1_by, workflow_level1_at (nullable)
 * - workflow_level2_by, workflow_level2_at (nullable)
 * - workflow_level3_by, workflow_level3_at (nullable)
 * - rejection_reason (nullable text)
 * - signed_pdf_path (nullable string)
 */
trait HasApprovalWorkflow
{
    /**
     * Boot the trait
     */
    public static function bootHasApprovalWorkflow()
    {
        // Auto-set workflow_status to pending on create
        static::creating(function ($model) {
            if (!$model->workflow_status) {
                $model->workflow_status = 'pending';
            }
        });
    }

    /**
     * Get workflow type for this model
     */
    public function getWorkflowType(): string
    {
        return property_exists($this, 'workflowType') 
            ? $this->workflowType 
            : config('approvalworkflow::approval.default_workflow_type');
    }

    /**
     * Get current workflow step
     */
    public function getCurrentWorkflowStep(): string
    {
        return $this->workflow_status;
    }

    /**
     * Get next workflow step
     */
    public function getNextWorkflowStep(): ?string
    {
        $workflowType = $this->getWorkflowType();
        $currentStep = $this->getCurrentWorkflowStep();
        
        $steps = config("approvalworkflow::approval.workflow_levels.{$workflowType}.steps");
        
        return $steps[$currentStep]['next'] ?? null;
    }

    /**
     * Check if can be approved at current level
     */
    public function canBeApproved(): bool
    {
        return !in_array($this->workflow_status, ['approved', 'rejected']);
    }

    /**
     * Check if can be rejected
     */
    public function canBeRejected(): bool
    {
        return !in_array($this->workflow_status, ['approved', 'rejected']);
    }

    /**
     * Check if is fully approved
     */
    public function isApproved(): bool
    {
        return $this->workflow_status === 'approved';
    }

    /**
     * Check if is rejected
     */
    public function isRejected(): bool
    {
        return $this->workflow_status === 'rejected';
    }

    /**
     * Get workflow status display text
     */
    public function getWorkflowStatusDisplayAttribute(): string
    {
        $workflowType = $this->getWorkflowType();
        $steps = config("approvalworkflow::approval.workflow_levels.{$workflowType}.steps");
        
        return $steps[$this->workflow_status]['label'] ?? $this->workflow_status;
    }

    /**
     * Relationship: Level 1 Approver
     */
    public function level1Approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'workflow_level1_by');
    }

    /**
     * Relationship: Level 2 Approver
     */
    public function level2Approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'workflow_level2_by');
    }

    /**
     * Relationship: Level 3 Approver
     */
    public function level3Approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'workflow_level3_by');
    }

    /**
     * Relationship: Approval History
     */
    public function approvalHistory()
    {
        return $this->morphMany(ApprovalHistory::class, 'approvable');
    }

    /**
     * Get the current level approver
     */
    public function getCurrentLevelApprover()
    {
        $status = $this->workflow_status;
        
        if ($status === 'pending') {
            return $this->level1Approver;
        } elseif ($status === 'level1_approved') {
            return $this->level2Approver;
        } elseif ($status === 'level2_approved') {
            return $this->level3Approver;
        }
        
        return null;
    }
}



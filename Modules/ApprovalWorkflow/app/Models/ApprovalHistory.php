<?php

namespace Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ApprovalHistory Model
 * 
 * Stores audit trail of all approval actions
 */
class ApprovalHistory extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'user_id',
        'action',
        'level',
        'workflow_status_before',
        'workflow_status_after',
        'comment',
        'reason',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the owning approvable model
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get action display text
     */
    public function getActionDisplayAttribute(): string
    {
        $actions = [
            'approved' => 'Đã phê duyệt',
            'rejected' => 'Đã từ chối',
            'cancelled' => 'Đã hủy',
            'returned' => 'Đã trả lại',
        ];

        return $actions[$this->action] ?? $this->action;
    }

    /**
     * Scope: Filter by action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Filter by level
     */
    public function scopeLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope: Recent first
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}



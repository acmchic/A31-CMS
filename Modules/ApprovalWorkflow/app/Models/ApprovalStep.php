<?php

namespace Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ApprovalStep Model
 * 
 * Quản lý các bước duyệt cho từng workflow
 */
class ApprovalStep extends Model
{
    protected $fillable = [
        'flow_id',
        'module_type',
        'step',
        'step_type',
        'order',
        'is_final',
        'needs_modal',
        'metadata',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_final' => 'boolean',
        'needs_modal' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the flow that owns this step
     */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'flow_id');
    }

    /**
     * Get steps by module type and order
     */
    public static function getByModuleType(string $moduleType, int $order): ?self
    {
        return self::where('module_type', $moduleType)
            ->where('order', $order)
            ->first();
    }

    /**
     * Get all steps for a module type
     */
    public static function getAllByModuleType(string $moduleType): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('module_type', $moduleType)
            ->orderBy('order')
            ->get();
    }
}


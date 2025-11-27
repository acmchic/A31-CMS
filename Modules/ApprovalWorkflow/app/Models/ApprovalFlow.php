<?php

namespace Modules\ApprovalWorkflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ApprovalFlow Model
 * 
 * Quản lý metadata workflow cho từng module
 */
class ApprovalFlow extends Model
{
    protected $fillable = [
        'module_type',
        'name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all steps for this flow
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'flow_id')->orderBy('order');
    }

    /**
     * Get steps by module type
     */
    public static function getByModuleType(string $moduleType): ?self
    {
        return self::where('module_type', $moduleType)->first();
    }
}


<?php

namespace Modules\ProductionManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Material extends Model
{
    use CrudTrait, SoftDeletes;

    protected $fillable = [
        'code',
        'ten_vat_tu',
        'quy_cach',
        'ky_hieu',
        'don_vi_tinh',
        'mo_ta',
        'min_stock_level',
        'max_stock_level',
        'status',
        'can_import',
        'can_export',
    ];

    protected $casts = [
        'min_stock_level' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
        'can_import' => 'boolean',
        'can_export' => 'boolean',
    ];

    /**
     * Get full name (ten_vat_tu + quy_cach + ky_hieu)
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([$this->ten_vat_tu, $this->quy_cach, $this->ky_hieu]);
        return implode(' - ', $parts);
    }

    /**
     * Get display name for select2
     */
    public function getDisplayNameAttribute()
    {
        return $this->full_name . ' (' . $this->don_vi_tinh . ')';
    }
}

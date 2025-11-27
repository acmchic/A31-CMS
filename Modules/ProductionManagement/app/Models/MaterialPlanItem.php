<?php

namespace Modules\ProductionManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class MaterialPlanItem extends Model
{
    use CrudTrait;
    protected $table = 'material_plan_items';

    protected $fillable = [
        'material_plan_id',
        'material_id',
        'so_thu_tu',
        'so_luong',
        'doi_cu',
        'cap_moi',
        'ghi_chu',
    ];

    protected $casts = [
        'so_luong' => 'decimal:2',
        'doi_cu' => 'decimal:2',
        'cap_moi' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function materialPlan()
    {
        return $this->belongsTo(MaterialPlan::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}

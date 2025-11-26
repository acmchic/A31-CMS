<?php

namespace Modules\ProductionManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Material extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        'code',
        'name',
        'unit',
        'description',
        'min_stock_level',
        'max_stock_level',
        'status',
        'can_import',
        'can_export',
    ];

    protected $casts = [
        'min_stock_level' => 'float',
        'max_stock_level' => 'float',
        'can_import' => 'boolean',
        'can_export' => 'boolean',
    ];

    // Relationships
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function materialPlanItems()
    {
        return $this->hasMany(MaterialPlanItem::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCanImport($query)
    {
        return $query->where('can_import', true);
    }

    public function scopeCanExport($query)
    {
        return $query->where('can_export', true);
    }

    // Accessors
    public function getStatusDisplayAttribute()
    {
        return $this->status === 'active' ? 'Hoạt động' : 'Ngừng hoạt động';
    }

    public function getCanImportDisplayAttribute()
    {
        return $this->can_import ? 'Cho phép' : 'Dừng nhập';
    }

    public function getCanExportDisplayAttribute()
    {
        return $this->can_export ? 'Cho phép' : 'Cấm xuất';
    }
}


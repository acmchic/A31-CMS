<?php

namespace Modules\VehicleRegistration\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Vehicle extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        'name',
        'license_plate',
        'type',
        'status',
        'capacity',
        'description'
    ];

    // Relationships
    public function registrations()
    {
        return $this->hasMany(VehicleRegistration::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->license_plate . ')';
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        // Check if vehicle is not already assigned for these dates
        return $query->whereDoesntHave('registrations', function($q) use ($startDate, $endDate) {
            $q->where('status', '!=', 'rejected')
              ->where(function($dateQuery) use ($startDate, $endDate) {
                  // Check both old date fields and new datetime fields
                  $dateQuery->where(function($oldFields) use ($startDate, $endDate) {
                      $oldFields->whereBetween('departure_date', [$startDate, $endDate])
                               ->orWhereBetween('return_date', [$startDate, $endDate])
                               ->orWhere(function($rangeQuery) use ($startDate, $endDate) {
                                   $rangeQuery->where('departure_date', '<=', $startDate)
                                             ->where('return_date', '>=', $endDate);
                               });
                  })
                  ->orWhere(function($newFields) use ($startDate, $endDate) {
                      $newFields->whereDate('departure_datetime', '>=', $startDate)
                               ->whereDate('departure_datetime', '<=', $endDate)
                               ->orWhereDate('return_datetime', '>=', $startDate)
                               ->orWhereDate('return_datetime', '<=', $endDate)
                               ->orWhere(function($rangeQuery) use ($startDate, $endDate) {
                                   $rangeQuery->whereDate('departure_datetime', '<=', $startDate)
                                             ->whereDate('return_datetime', '>=', $endDate);
                               });
                  });
              });
        });
    }
}

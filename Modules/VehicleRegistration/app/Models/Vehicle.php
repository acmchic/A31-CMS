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

    /**
     * Scope to get vehicles available for a date range
     * A vehicle is NOT available if it's already assigned to another registration
     * that overlaps with the given date range and is in a submitted state
     */
    public function scopeForDateRange($query, $startDate, $endDate, $excludeRegistrationId = null)
    {
        return $query->whereDoesntHave('registrations', function($q) use ($startDate, $endDate, $excludeRegistrationId) {
            // Exclude the current registration if editing
            if ($excludeRegistrationId) {
                $q->where('id', '!=', $excludeRegistrationId);
            }
            
            // Only check registrations that are in active workflow (not rejected, not cancelled)
            // Check via approval_requests table
            $q->whereHas('approvalRequest', function($approvalQuery) {
                $approvalQuery->whereIn('status', ['submitted', 'in_review', 'approved']);
            })
              ->where(function($dateQuery) use ($startDate, $endDate) {
                  // Check date overlap: two date ranges overlap if:
                  // - start1 <= end2 AND start2 <= end1
                  
                  // Check both old date fields and new datetime fields
                  $dateQuery->where(function($oldFields) use ($startDate, $endDate) {
                      // Overlap condition: departure_date <= $endDate AND return_date >= $startDate
                      $oldFields->where(function($overlap) use ($startDate, $endDate) {
                          $overlap->where('departure_date', '<=', $endDate)
                                  ->where('return_date', '>=', $startDate);
                      });
                  })
                  ->orWhere(function($newFields) use ($startDate, $endDate) {
                      // Overlap condition: departure_datetime <= $endDate AND return_datetime >= $startDate
                      $newFields->where(function($overlap) use ($startDate, $endDate) {
                          $overlap->whereDate('departure_datetime', '<=', $endDate)
                                  ->whereDate('return_datetime', '>=', $startDate);
                      });
                  });
              });
        });
    }
}

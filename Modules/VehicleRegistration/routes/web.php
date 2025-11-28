<?php

use Illuminate\Support\Facades\Route;
use Modules\VehicleRegistration\Http\Controllers\Admin\VehicleRegistrationCrudController;

/*
|--------------------------------------------------------------------------
| VehicleRegistration Module Routes
|--------------------------------------------------------------------------
*/

// Admin routes for vehicle registration
Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    
    // Vehicle Registration routes - basic CRUD
    Route::group(['middleware' => 'permission:vehicle_registration.view'], function () {
        Route::crud('vehicle-registration', VehicleRegistrationCrudController::class);
    });
    
    Route::group(['middleware' => 'permission:vehicle_registration.assign'], function () {
        Route::get('vehicle-registration/{id}/assign-vehicle', [VehicleRegistrationCrudController::class, 'showAssignForm'])->name('vehicle-registration.assign-vehicle');
        Route::post('vehicle-registration/{id}/assign-vehicle', [VehicleRegistrationCrudController::class, 'processAssignment'])->name('vehicle-registration.process-assignment');
    });
    
});
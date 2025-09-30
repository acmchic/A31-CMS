<?php

use Illuminate\Support\Facades\Route;
use Modules\VehicleRegistration\Http\Controllers\Admin\VehicleRegistrationCrudController;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Vehicle Registration PIN approval route (backup)
Route::post('admin/vehicle-registration/{id}/approve-with-pin', [VehicleRegistrationCrudController::class, 'approveWithPin'])
    ->name('vehicle-registration.approve-with-pin')
    ->middleware(['web', 'admin']);


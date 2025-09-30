<?php

use Illuminate\Support\Facades\Route;
use Modules\VehicleRegistration\Http\Controllers\VehicleRegistrationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('vehicleregistrations', VehicleRegistrationController::class)->names('vehicleregistration');
});

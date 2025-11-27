<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductionManagement\Http\Controllers\ProductionManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('productionmanagements', ProductionManagementController::class)->names('productionmanagement');
});

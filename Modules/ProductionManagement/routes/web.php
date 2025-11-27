<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductionManagement\Http\Controllers\ProductionManagementController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('productionmanagements', ProductionManagementController::class)->names('productionmanagement');
});

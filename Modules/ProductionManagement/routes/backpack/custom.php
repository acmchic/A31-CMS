<?php

use Illuminate\Support\Facades\Route;
use Modules\ProductionManagement\Http\Controllers\Admin\MaterialPlanCrudController;

// --------------------------
// Custom Backpack Routes for ProductionManagement
// --------------------------

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    // Material Plan routes
    Route::crud('material-plan', MaterialPlanCrudController::class);
    
    // AJAX endpoint for material search
    Route::get('material-plan/fetch/material', [MaterialPlanCrudController::class, 'fetchMaterial'])->name('material-plan.fetch.material');
    
    // AJAX endpoint for approvers list
    Route::get('material-plan/fetch/approvers', [MaterialPlanCrudController::class, 'getApprovers'])->name('material-plan.fetch.approvers');
});


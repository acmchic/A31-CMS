<?php

use Illuminate\Support\Facades\Route;
use Modules\OrganizationStructure\Http\Controllers\Admin\DepartmentCrudController;
use Modules\OrganizationStructure\Http\Controllers\Admin\EmployeeCrudController;

/*
|--------------------------------------------------------------------------
| OrganizationStructure Module Routes
|--------------------------------------------------------------------------
*/

// Admin routes for organization structure
Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    
    // Organization Structure CRUD routes
    Route::crud('department', DepartmentCrudController::class);
    Route::crud('employee', EmployeeCrudController::class);
    
});

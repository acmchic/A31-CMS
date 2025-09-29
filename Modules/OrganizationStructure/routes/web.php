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
    
    // Department routes - require view_department permission
    Route::group(['middleware' => 'permission:view_department'], function () {
        Route::crud('department', DepartmentCrudController::class);
    });
    
    // Employee routes - require view_employee permission
    Route::group(['middleware' => 'permission:view_employee'], function () {
        Route::crud('employee', EmployeeCrudController::class);
    });
    
});

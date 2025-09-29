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
    
    // Department routes - require department.view permission
    Route::group(['middleware' => 'permission:department.view'], function () {
        Route::crud('department', DepartmentCrudController::class);
    });
    
    // Employee routes - require employee.view permission
    Route::group(['middleware' => 'permission:employee.view'], function () {
        Route::crud('employee', EmployeeCrudController::class);
    });
    
});

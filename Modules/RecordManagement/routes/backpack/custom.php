<?php

use Illuminate\Support\Facades\Route;
use Modules\RecordManagement\Http\Controllers\Admin\SalaryUpRecordCrudController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    // Trang chính - Danh sách các loại sổ
    Route::get('record-management', 'Modules\RecordManagement\Http\Controllers\RecordManagementController@index')
        ->name('record-management.index');
    
    // API endpoints for auto-fill (inside admin prefix)
    Route::group(['namespace' => 'Modules\RecordManagement\Http\Controllers\Admin'], function () {
        Route::get('salary-up-record/api/employees-by-department/{departmentId}', 'SalaryUpRecordCrudController@getEmployeesByDepartment');
        Route::get('salary-up-record/api/employee-info/{employeeId}', 'SalaryUpRecordCrudController@getEmployeeInfo');
        
        Route::get('quan-nhan-record/api/employees-by-department/{departmentId}', 'QuanNhanRecordCrudController@getEmployeesByDepartment');
        Route::get('quan-nhan-record/api/employee-info/{employeeId}', 'QuanNhanRecordCrudController@getEmployeeInfo');
        
        Route::get('so-dieu-dong-record/api/employees-by-department/{departmentId}', 'SoDieuDongRecordCrudController@getEmployeesByDepartment');
        Route::get('so-dieu-dong-record/api/employee-info/{employeeId}', 'SoDieuDongRecordCrudController@getEmployeeInfo');
        
        // Test route
        Route::get('so-dieu-dong-record/api/test', function() {
            return response()->json(['message' => 'API working']);
        });
    });
    
    // CRUD cho từng loại sổ
    Route::group(['namespace' => 'Modules\RecordManagement\Http\Controllers\Admin'], function () {
        // Sổ nâng lương
        Route::crud('salary-up-record', 'SalaryUpRecordCrudController');
        
        // Sổ danh sách quân nhân
        Route::crud('quan-nhan-record', 'QuanNhanRecordCrudController');
        
        // Sổ đăng ký điều động nội bộ
        Route::crud('so-dieu-dong-record', 'SoDieuDongRecordCrudController');
        
        // TODO: Thêm các loại sổ khác ở đây
        // Route::crud('discipline-record', 'DisciplineRecordCrudController');
    });
});


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
    
    // Test route
    Route::get('test-an-duong', function() {
        try {
            $controller = new \Modules\RecordManagement\Http\Controllers\Admin\AnDuongRecordCrudController();
            $controller->setup();
            return 'Controller setup OK';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        }
    });
    
    // API endpoints for auto-fill (inside admin prefix)
    Route::group(['namespace' => 'Modules\RecordManagement\Http\Controllers\Admin'], function () {
        Route::get('salary-up-record/api/employees-by-department/{departmentId}', 'SalaryUpRecordCrudController@getEmployeesByDepartment');
        Route::get('salary-up-record/api/employee-info/{employeeId}', 'SalaryUpRecordCrudController@getEmployeeInfo');
        
        Route::get('quan-nhan-record/api/employees-by-department/{departmentId}', 'QuanNhanRecordCrudController@getEmployeesByDepartment');
        Route::get('quan-nhan-record/api/employee-info/{employeeId}', 'QuanNhanRecordCrudController@getEmployeeInfo');
        
        Route::get('so-dieu-dong-record/api/employees-by-department/{departmentId}', 'SoDieuDongRecordCrudController@getEmployeesByDepartment');
        Route::get('so-dieu-dong-record/api/employee-info/{employeeId}', 'SoDieuDongRecordCrudController@getEmployeeInfo');
        
        Route::get('an-duong-record/api/employees-by-department/{departmentId}', 'AnDuongRecordCrudController@getEmployeesByDepartment');
        Route::get('an-duong-record/api/employee-info/{employeeId}', 'AnDuongRecordCrudController@getEmployeeInfo');
    });
    
    // CRUD cho từng loại sổ
    Route::group(['namespace' => 'Modules\RecordManagement\Http\Controllers\Admin'], function () {
        // Sổ nâng lương
        Route::crud('salary-up-record', 'SalaryUpRecordCrudController');
        
        // Sổ danh sách quân nhân
        Route::crud('quan-nhan-record', 'QuanNhanRecordCrudController');
        
        // Sổ đăng ký điều động nội bộ
        Route::crud('so-dieu-dong-record', 'SoDieuDongRecordCrudController');
        
        // Sổ đăng ký an dưỡng, bồi dưỡng
        Route::crud('an-duong-record', 'AnDuongRecordCrudController');
        
        // TODO: Thêm các loại sổ khác ở đây
        // Route::crud('discipline-record', 'DisciplineRecordCrudController');
    });
});
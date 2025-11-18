<?php

use Illuminate\Support\Facades\Route;
use Modules\PersonnelReport\Http\Controllers\Admin\LeaveRequestCrudController;
use Modules\PersonnelReport\Http\Controllers\Admin\DailyPersonnelReportCrudController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    Route::group(['middleware' => 'permission:leave.view|leave.create'], function () {
        Route::crud('leave-request', LeaveRequestCrudController::class);
        Route::get('leave-request/{id}/download-pdf', [LeaveRequestCrudController::class, 'downloadPdf'])->name('leave-request.download-pdf');
    });
    
    Route::group(['middleware' => 'permission:leave.approve'], function () {
        Route::get('leave-request/{id}/approve', [LeaveRequestCrudController::class, 'approve'])->name('leave-request.approve');
    });
    
    Route::group(['middleware' => 'permission:leave.approve'], function () {
        Route::get('leave-request/{id}/reject', [LeaveRequestCrudController::class, 'reject'])->name('leave-request.reject');
        Route::post('leave-request/bulk-approve', [LeaveRequestCrudController::class, 'bulkApprove'])->name('leave-request.bulk-approve');
        Route::post('leave-request/bulk-reject', [LeaveRequestCrudController::class, 'bulkReject'])->name('leave-request.bulk-reject');
    });
    
    Route::group(['middleware' => 'permission:report.view'], function () {
        Route::get('daily-personnel-report/api/department-stats/{departmentId}', [DailyPersonnelReportCrudController::class, 'getDepartmentStats']);
        Route::get('daily-personnel-report/create-2', [DailyPersonnelReportCrudController::class, 'create2'])->name('daily-personnel-report.create-2');
        Route::post('daily-personnel-report/store-2', [DailyPersonnelReportCrudController::class, 'store2'])->name('daily-personnel-report.store-2');
        Route::crud('daily-personnel-report', DailyPersonnelReportCrudController::class);
    });
});

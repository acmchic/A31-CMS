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
    // Leave Request routes - require view_leave_request permission
    Route::group(['middleware' => 'permission:view_leave_request'], function () {
        Route::crud('leave-request', LeaveRequestCrudController::class);
        Route::get('leave-request/{id}/download-pdf', [LeaveRequestCrudController::class, 'downloadPdf'])->name('leave-request.download-pdf');
    });
    
    // Approval routes - require approve-leave-request permission
    Route::group(['middleware' => 'permission:approve-leave-request'], function () {
        Route::get('leave-request/{id}/approve', [LeaveRequestCrudController::class, 'approve'])->name('leave-request.approve');
    });
    
    // Reject routes - require reject-leave-request permission
    Route::group(['middleware' => 'permission:reject-leave-request'], function () {
        Route::get('leave-request/{id}/reject', [LeaveRequestCrudController::class, 'reject'])->name('leave-request.reject');
    });
    
    // Daily Report routes - require view_daily_report permission
    Route::group(['middleware' => 'permission:view_daily_report'], function () {
        Route::crud('daily-personnel-report', DailyPersonnelReportCrudController::class);
    });
});

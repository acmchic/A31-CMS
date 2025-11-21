<?php

use Illuminate\Support\Facades\Route;
use Modules\ApprovalWorkflow\Http\Controllers\ApprovalController;
use Modules\ApprovalWorkflow\Http\Controllers\ApprovalCenterController;

/*
|--------------------------------------------------------------------------
| Approval Workflow Routes
|--------------------------------------------------------------------------
|
| Generic routes for approval workflow that can be used by any module
|
*/

// Approval Center routes (without admin prefix)
Route::group([
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    Route::prefix('approval-center')->name('approval-center.')->group(function () {
        Route::get('/', [ApprovalCenterController::class, 'index'])->name('index');
        Route::get('details', [ApprovalCenterController::class, 'getDetails'])->name('details');
        Route::get('history', [ApprovalCenterController::class, 'getHistory'])->name('history');
        Route::get('directors', [ApprovalCenterController::class, 'getDirectors'])->name('directors');
        Route::post('approve', [ApprovalCenterController::class, 'approve'])->name('approve');
        Route::post('bulk-approve', [ApprovalCenterController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('bulk-assign-approvers', [ApprovalCenterController::class, 'bulkAssignApprovers'])->name('bulk-assign-approvers');
        Route::post('reject', [ApprovalCenterController::class, 'reject'])->name('reject');
        Route::post('assign-approvers', [ApprovalCenterController::class, 'assignApprovers'])->name('assign-approvers');
    });
});

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    
    // Generic approval routes
    Route::prefix('approval')->name('approval.')->group(function () {
        
        // Approve with PIN
        Route::post('approve/{modelClass}/{id}', [ApprovalController::class, 'approveWithPin'])
            ->name('approve-with-pin');
        
        // Approve without PIN (for reviewer step)
        Route::post('approve-without-pin/{modelClass}/{id}', [ApprovalController::class, 'approveWithoutPin'])
            ->name('approve-without-pin');
        
        // Reject
        Route::post('reject/{modelClass}/{id}', [ApprovalController::class, 'reject'])
            ->name('reject');
        
        // Download signed PDF
        Route::get('download-pdf/{modelClass}/{id}', [ApprovalController::class, 'downloadPdf'])
            ->name('download-pdf');
        
        // Get approval history
        Route::get('history/{modelClass}/{id}', [ApprovalController::class, 'history'])
            ->name('history');
    });
});

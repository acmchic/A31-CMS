<?php

use Illuminate\Support\Facades\Route;
use Modules\ApprovalWorkflow\Http\Controllers\ApprovalController;

/*
|--------------------------------------------------------------------------
| Approval Workflow Routes
|--------------------------------------------------------------------------
|
| Generic routes for approval workflow that can be used by any module
|
*/

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

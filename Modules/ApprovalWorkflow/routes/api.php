<?php

use Illuminate\Support\Facades\Route;
use Modules\ApprovalWorkflow\Http\Controllers\ApprovalWorkflowController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('approvalworkflows', ApprovalWorkflowController::class)->names('approvalworkflow');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\RecordManagement\Http\Controllers\RecordManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('recordmanagements', RecordManagementController::class)->names('recordmanagement');
});

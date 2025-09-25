<?php

use Illuminate\Support\Facades\Route;
use Modules\PersonnelReport\Http\Controllers\PersonnelReportController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('personnelreports', PersonnelReportController::class)->names('personnelreport');
});

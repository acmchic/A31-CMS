<?php

use Illuminate\Support\Facades\Route;
use Modules\OrganizationStructure\Http\Controllers\OrganizationStructureController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('organizationstructures', OrganizationStructureController::class)->names('organizationstructure');
});

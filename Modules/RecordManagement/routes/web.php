<?php

use Illuminate\Support\Facades\Route;
use Modules\RecordManagement\Http\Controllers\RecordManagementController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('recordmanagements', RecordManagementController::class)->names('recordmanagement');
});

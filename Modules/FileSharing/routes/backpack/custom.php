<?php

use Illuminate\Support\Facades\Route;
use Modules\FileSharing\Http\Controllers\Admin\SharedFileCrudController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'middleware' => ['web', 'admin'],
], function () {
    // File Sharing Management
    Route::get('file-sharing-management', function() {
        return redirect()->route('file-sharing.index');
    })->name('file-sharing-management.index');
    
    // CRUD cho Shared Files
    Route::group(['namespace' => 'Modules\FileSharing\Http\Controllers\Admin'], function () {
        Route::crud('shared-file', 'SharedFileCrudController');
        
        // Download route for CRUD
        Route::get('shared-file/{id}/download', 'SharedFileCrudController@download')
            ->name('crud.shared-file.download');
    });
});

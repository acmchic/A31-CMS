<?php

use Illuminate\Support\Facades\Route;
use Modules\FileSharing\Http\Controllers\FileSharingController;

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
    'middleware' => ['web', 'admin'],
], function () {
    // File Sharing routes
    Route::get('file-sharing', [FileSharingController::class, 'index'])
        ->name('file-sharing.index');
    
    Route::get('file-sharing/create', [FileSharingController::class, 'create'])
        ->name('file-sharing.create');
    
    Route::post('file-sharing', [FileSharingController::class, 'store'])
        ->name('file-sharing.store');
    Route::post('file-sharing/folders', [FileSharingController::class, 'storeFolder'])
        ->name('file-sharing.folders.store');
    
    Route::get('file-sharing/{id}', [FileSharingController::class, 'show'])
        ->name('file-sharing.show');
    
    Route::get('file-sharing/{id}/download', [FileSharingController::class, 'download'])
        ->name('file-sharing.download');
    
    Route::delete('file-sharing/{id}', [FileSharingController::class, 'destroy'])
        ->name('file-sharing.destroy');
});

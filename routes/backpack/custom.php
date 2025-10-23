<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::get('dashboard', 'DashboardController@dashboard')->name('backpack.dashboard')->middleware('permission:dashboard.view');
    
    // Profile routes
    Route::get('edit-account-info', 'ProfileController@edit')->name('admin.profile.edit');
    Route::get('account/info', 'ProfileController@edit')->name('backpack.account.info'); // Alias for Backpack default
    Route::post('edit-account-info', 'ProfileController@update')->name('backpack.account.info.store'); // Alias for Backpack default
    Route::put('profile', 'ProfileController@update')->name('admin.profile.update');
    Route::put('profile/upload-photo', 'ProfileController@uploadPhoto')->name('admin.profile.upload-photo');
    Route::put('profile/upload-signature', 'ProfileController@uploadSignature')->name('admin.profile.upload-signature');
    Route::put('profile/change-password', 'ProfileController@changePassword')->name('admin.profile.change-password');
    Route::put('profile/update-pin', 'ProfileController@updatePin')->name('admin.profile.update-pin');
    Route::delete('profile/delete-photo', 'ProfileController@deleteProfilePhoto')->name('admin.profile.delete-photo');
    Route::delete('profile/delete-signature', 'ProfileController@deleteSignature')->name('admin.profile.delete-signature');
    
    // System Settings routes
    Route::get('system-settings', 'SystemSettingsController@index')->name('admin.system-settings.index');
    Route::put('system-settings', 'SystemSettingsController@update')->name('admin.system-settings.update');
    Route::post('system-settings/reset', 'SystemSettingsController@reset')->name('admin.system-settings.reset');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */

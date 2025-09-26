<?php

/*
|--------------------------------------------------------------------------
| Backpack\PermissionManager Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Backpack\PermissionManager package.
|
*/

// Custom Role routes - Only for admin
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', backpack_middleware(), 'admin.role'],
], function () {
    Route::crud('role', 'App\Http\Controllers\Admin\RoleCrudController');
});

// Custom Permission routes - Only for admin
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', backpack_middleware(), 'admin.role'],
], function () {
    Route::crud('permission', 'App\Http\Controllers\Admin\PermissionCrudController');
});

// Custom User routes with department field - Only for admin
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', backpack_middleware(), 'admin.role'],
], function () {
    Route::crud('user', 'App\Http\Controllers\Admin\UserCrudController');
});

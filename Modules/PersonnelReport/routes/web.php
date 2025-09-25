<?php

use Illuminate\Support\Facades\Route;
use Modules\PersonnelReport\Http\Controllers\Admin\LeaveRequestCrudController;
use Modules\PersonnelReport\Http\Controllers\Admin\DailyPersonnelReportCrudController;

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
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
], function () {
    Route::crud('leave-request', LeaveRequestCrudController::class);
    Route::crud('daily-personnel-report', DailyPersonnelReportCrudController::class);
});

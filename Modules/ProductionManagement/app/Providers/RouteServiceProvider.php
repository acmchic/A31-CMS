<?php

namespace Modules\ProductionManagement\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'ProductionManagement';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapBackpackRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')->group(module_path($this->name, '/routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::middleware('api')->prefix('api')->name('api.')->group(module_path($this->name, '/routes/api.php'));
    }

    /**
     * Define the "backpack" routes for the application.
     */
    protected function mapBackpackRoutes(): void
    {
        $backpackRoutePath = module_path($this->name, '/routes/backpack/custom.php');
        if (file_exists($backpackRoutePath)) {
            Route::group([
                'middleware' => array_merge(
                    (array) config('backpack.base.web_middleware', 'web'),
                    (array) config('backpack.base.middleware_key', 'admin')
                ),
            ], function () use ($backpackRoutePath) {
                require $backpackRoutePath;
            });
        }
    }
}

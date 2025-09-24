<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Backpack\CRUD\app\Http\Controllers\Auth\LoginController as BackpackLoginController;
use App\Http\Controllers\AuthLoginController;

class AuthOverrideServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Override LoginController binding
        $this->app->bind(BackpackLoginController::class, AuthLoginController::class);
    }
}

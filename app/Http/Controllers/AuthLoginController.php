<?php

namespace App\Http\Controllers;
use Backpack\CRUD\app\Http\Controllers\Auth\LoginController as BackpackLoginController;
use Illuminate\Http\Request;

class AuthLoginController extends BackpackLoginController
{
    /**
     * Redirect to dashboard after login
     */
    protected ?string $redirectTo = '/dashboard';
    
    /**
     * Use username instead of email for login
     */
    public function username()
    {
        return 'username';
    }
}

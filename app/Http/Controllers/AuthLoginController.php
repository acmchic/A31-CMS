<?php

namespace App\Http\Controllers;
use Backpack\CRUD\app\Http\Controllers\Auth\LoginController as BackpackLoginController;


use Illuminate\Http\Request;

class AuthLoginController extends BackpackLoginController
{
     public function username()
    {
        return 'username';
    }
}

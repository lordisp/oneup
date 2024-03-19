<?php

namespace App\Http\Controllers;

class AuthController extends Controller
{
    public function logout()
    {
        auth()->logout();
    }
}

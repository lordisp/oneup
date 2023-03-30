<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): Application|Factory|View
    {
        return view('auth.login');
    }
}

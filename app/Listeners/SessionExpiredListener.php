<?php

namespace App\Listeners;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionExpiredListener
{
    public function __construct(public Request $request)
    {
    }

    public function handle(): void
    {
        Auth::logout();

        $this->request->session()->invalidate();

        $this->request->session()->regenerateToken();

        return redirect(route('login'));
    }
}

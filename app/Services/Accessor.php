<?php

namespace App\Services;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Accessor
{
    public static function title(): string
    {
        $path = Str::after(Request::path(), '/');

        $routeName = Str::after(Route::currentRouteName(), '.');

        $title = empty($routeName) ? $path : $routeName;

        return $title ? Str::headline($title) : 'No title set!';
    }

    public static function jwt_decode($token)
    {
        return json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $token)[1]))));
    }
}

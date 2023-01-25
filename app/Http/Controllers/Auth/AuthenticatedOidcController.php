<?php

namespace App\Http\Controllers\Auth;

use App\Facades\TokenCache;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthenticatedOidcController extends Controller
{

    public const provider = 'lhg_graph';

    public function signin()
    {
        if (Auth::check()) return redirect(RouteServiceProvider::HOME);

        auth()->logout();

        return TokenCache::provider(self::provider)->authCode();
    }

    public function callback(Request $request)
    {
        $state = $request->query('state');

        $authState = session('authState');

        $request->session()->forget('authState');

        if ($state != $authState) redirect(route('login'))->with('Invalid AuthState');

        $params = [
            'code' => $request->query('code'),
            'code_challenge' => $state,
        ];

        $token = TokenCache::provider(self::provider)->accessToken($params);

        $oid = TokenCache::jwt(decrypt($token))['oid'];

        $user = User::where('provider_id', $oid)->first();

        if ($user) return $this->login($user);

        return redirect(route('login'))->withErrors(['error_description' => 'Access denied']);

    }

    public function login(User $user)
    {
        auth()->login($user);

        return Auth::check() ? redirect(RouteServiceProvider::HOME) : redirect()->intended(route('login'));
    }

    public function logout(Request $request)
    {
        $token = TokenCache::provider(self::provider)->accessToken();

        if (is_string($token)) {
            $jwt = TokenCache::jwt(decrypt($token));

            Cache::tags($jwt['oid'])->flush();
        }

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Auth::logout();

        return is_string($token) ? redirect()->intended('https://login.microsoftonline.com/' . $jwt['tid'] . '/oauth2/logout?post_logout_redirect_uri=' . config('app.url')) : redirect()->intended();
    }
}

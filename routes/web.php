<?php


use App\Http\Livewire\Admin\Provider;
use App\Http\Livewire\Profile\Clients;
use App\Http\Livewire\Rbac\Users;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

/* auth web routes*/
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    });

    /* Profile Routes */
    Route::get('/profile/clients', Clients::class)->name('profile.clients');

    /* Admin Routes*/
    Route::get('/admin/users', Users::class)->name('admin.users');
    Route::get('/admin/provider', Provider::class)->name('admin.provider');

});

require __DIR__ . '/auth.php';
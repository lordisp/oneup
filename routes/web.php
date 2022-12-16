<?php


use App\Http\Livewire\Admin\Groups;
use App\Http\Livewire\Admin\GroupsCreate;
use App\Http\Livewire\Admin\GroupsEdit;
use App\Http\Livewire\Admin\Operations;
use App\Http\Livewire\Admin\Provider;
use App\Http\Livewire\Admin\Roles;
use App\Http\Livewire\Admin\RolesEdit;
use App\Http\Livewire\PCI\FirewallRequestsImport;
use App\Http\Livewire\PCI\FirewallRulesRead;
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
    Route::get('/admin/operations', Operations::class)->name('admin.operations');
    Route::get('/admin/roles', Roles::class)->name('admin.roles');
    Route::get('/admin/roles/create', RolesEdit::class)->name('admin.roles.create');
    Route::get('/admin/roles/{id}/edit', RolesEdit::class)->name('admin.roles.edit');
    Route::get('/admin/group', Groups::class)->name('admin.group');
    Route::get('/admin/group/create', GroupsCreate::class)->name('admin.group.create');
    Route::get('/admin/group/{id}/{tab}', GroupsEdit::class)->name('admin.group.edit');

    /* Firewall Management */
    Route::get('/firewall/requests/read', FirewallRulesRead::class)->name('firewall.requests.read');
    Route::get('/firewall/requests/import', FirewallRequestsImport::class)->name('firewall.requests.import');
});

require __DIR__ . '/auth.php';
<?php

namespace App\Http\Controllers\V1\Rbac;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Rbac\RbacUserRequest;
use App\Http\Resources\V1\Rbac\RbacUserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return RbacUserResource
     */
    public function index(Request $request)
    {
        return new RbacUserResource(User::paginate((int)$request->input('top', 999)));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RbacUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(RbacUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}

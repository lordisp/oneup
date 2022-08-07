<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Uuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstName',
        'lastName',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function groups($type = 'user')
    {
        $table = match ($type) {
            'owner' => 'groups_owners',
            default => 'groups_users',
        };

        return $this->belongsToMany(Group::class, $table)->withTimestamps();
    }

    public function assignGroup($group)
    {
        if (is_string($group)) {
            $group = Group::whereName($group)->firstOrFail();
        }
        $this->groups()->sync($group, false);
    }

    public function unassignGroup($group)
    {
        if (is_string($group)) {
            $group = Group::whereName($group)->firstOrFail();
        }
        $this->groups()->detach($group);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_users')->withTimestamps();
    }

    public function allRoles()
    {
        $roles = $this->belongsToMany(Role::class, 'roles_users')->withTimestamps();
        $rolesFromGroups = $this->groups->map->roles->flatten()->pluck('name')->unique();

        return $roles->get()->collect()->flatten()->pluck('name')->push(...$rolesFromGroups)->unique();
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->firstOrFail();
        }
        $this->roles()->sync($role, false);
    }

    public function unassignRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->firstOrFail();
        }
        $this->roles()->detach($role);
    }

    public function operations()
    {
        $operations = $this->roles->map->operations->flatten()->pluck('operation')->unique()->toArray();
        $groupsOperations = $this->groups->map->operations()->flatten()->unique()->toArray();
        array_push($operations, ...$groupsOperations);

        return collect(array_unique($operations));
    }
}

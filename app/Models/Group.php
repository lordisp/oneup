<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static first()
 * @property mixed $roles
 */
class Group extends Model
{
    use HasFactory, HasSlug, Uuid;

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groups_users')->withTimestamps();
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groups_owners')->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_groups')->withTimestamps();
    }

    public function attachUsers($users)
    {
        $this->users()->attach($users);
    }

    public function attachOwners($users)
    {
        $this->owners()->attach(($users));
    }

    public function detachOwners($users)
    {
        $this->owners()->detach($users);
    }

    public function detachUsers($users)
    {
        $this->users()->detach($users);
    }

    public function attachRoles($roles)
    {
        $this->roles()->attach(($roles));
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
        return $this->roles->map->operations->flatten()->pluck('operation')->unique();
    }

    #Todo: consider to implement search methods for `owners`, `users` and `roles`
}

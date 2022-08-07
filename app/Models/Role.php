<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static whereName(string $role)
 */
class Role extends Model
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
        return $this->belongsToMany(User::class, 'roles_users')->withTimestamps();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'roles_groups')->withTimestamps();
    }

    public function operations(): BelongsToMany
    {
        return $this->belongsToMany(Operation::class, 'roles_operations')->withTimestamps();
    }

    public function detach($operation)
    {
        $this->operations()->detach($operation);
    }

    public function attach($operation)
    {
        $this->operations()->attach($operation);
    }
}

<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Operation extends Model
{
    use HasFactory, HasSlug, Uuid;

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('operation')
            ->saveSlugsTo('slug');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'roles_operations')->withTimestamps();
    }

    public static function search($search)
    {
        $search = str_replace([' ', '*'], '%', trim($search));
        if (empty($search)) {
            $search = '%';
        }

        return self::where(function ($query) use ($search) {
            return $query->where('operation', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        })->get();
    }
}

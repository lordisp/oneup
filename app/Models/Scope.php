<?php

namespace App\Models;

use App\Models\Passport\Client;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static firstOrCreate(string[] $array)
 */
class Scope extends Model
{
    use HasFactory, HasSlug, Uuid;

    protected $fillable = [
        'scope',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('scope')
            ->saveSlugsTo('slug');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'oauth_client_scope', 'oauth_client_id', 'scope_id')
            ->withPivot(['approved_at', 'approved_by']);
    }
}

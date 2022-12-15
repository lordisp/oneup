<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property mixed $requestor_mail
 * @method static where(string $string, mixed $email)
 */
class ServiceNowRequest extends Model
{
    use HasFactory, Uuid, HasSlug;

    protected $casts = [
        'tags' => 'array'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('subject')
            ->saveSlugsTo('slug');
    }

    protected $fillable = [
        'template',
        'description',
        'requestor_mail',
        'ritm_number',
        'subject',
        'opened_by',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(FirewallRule::class);
    }

    /**
     * Get all the tags for the post.
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function requestor_name(): Attribute
    {
        return Attribute::make(
            set: fn() => $this->requestor_firstName . ' ' . $this->requestor_lastName
        );
    }
}

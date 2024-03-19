<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property mixed $requestor_mail
 *
 * @method static where(string $string, mixed $email)
 */
class ServiceNowRequest extends Model
{
    use HasSlug, Uuid;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('subject')
            ->saveSlugsTo('slug');
    }

    protected $fillable = [
        'opened_by',
        'cost_center',
        'requestor_name',
        'requestor_mail',
        'ritm_number',
        'subject',
        'description',
        'created_at',
        'updated_at',
    ];

    public function rules(): HasMany
    {
        return $this->hasMany(FirewallRule::class);
    }

    public function requestor_name(): Attribute
    {
        return Attribute::make(
            set: fn () => Str::title($this->requestor_firstName).' '.Str::title($this->requestor_lastName)
        );
    }
}

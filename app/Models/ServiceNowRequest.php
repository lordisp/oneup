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
 * @method static where(string $string, mixed $email)
 */
class ServiceNowRequest extends Model
{
    use Uuid, HasSlug;

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

    public function requestor_name(): Attribute
    {
        return Attribute::make(
            set: fn() => Str::title($this->requestor_firstName) . ' ' . Str::title($this->requestor_lastName)
        );
    }
public function dddd(): Attribute
{
    return Attribute::make(
        get: fn($value) => $value,
        set: fn($value) => $value,
    );
}

    public function subjectName(): Attribute
    {
        return Attribute::make(
            get: fn() => str_replace(['_', 'Firewall', 'Request'], '', $this->subject),
        );
    }
}

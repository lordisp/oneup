<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static create(string[] $array)
 */
class BusinessService extends Model
{
    use Uuid, HasSlug;

    protected $fillable = ['name', 'pci_dss'];

    protected $table = 'business_services';

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(FirewallRule::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            related: User::class,
            table: 'business_service_user',
            foreignPivotKey: 'business_service_id',
            relatedPivotKey: 'user_id',
        )
            ->withPivot('business_service_id', 'user_id')
            ->withTimestamps();
    }

    public function scopeByName(Builder $query, $value): Builder
    {
        $value = trim($value);

        return $query->where('name', 'like', "%{$value}%");
    }

    public function scopeByOwner(Builder $query): Builder
    {
        return $query->whereRelation('users', 'user_id', auth()->id());
    }
}

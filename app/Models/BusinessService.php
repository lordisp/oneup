<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @method static create(string[] $array)
 */
class BusinessService extends Model
{
    use HasFactory, HasSlug, Uuid;

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

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
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

    protected function trimmedBusinessServiceName(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (Str::contains($value, '[non-operational]', true)) {
                    $value = Str::remove('[non-operational]', $value, false);
                }
                if (Str::contains($value, '_damaged', true)) {
                    $value = Str::remove('_damaged', $value, false);
                }

                return $value;
            },
        );
    }
}

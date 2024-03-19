<?php

namespace App\Models;

use App\Http\Livewire\DataTable\WithRbacCache;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

/**
 * @property mixed $request
 */
class FirewallRule extends Model
{
    use Uuid, WithRbacCache;

    protected $fillable = [
        'action',
        'type_destination',
        'destination',
        'type_source',
        'source',
        'service',
        'destination_port',
        'description',
        'no_expiry',
        'end_date',
        'pci_dss',
        'nat_required',
        'application_id',
        'contact',
        'business_purpose',
        'last_review',
        'hash',
        'status',
        'business_service_id',
    ];

    protected function casts(): array
    {
        return [
            'end_date' => 'datetime',
            'last_review' => 'datetime',
            'destination' => 'json',
            'source' => 'json',
            'destination_port' => 'json',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceNowRequest::class, 'service_now_request_id');
    }

    public function businessService(): BelongsTo
    {
        return $this->belongsTo(BusinessService::class);
    }

    public function audits(): MorphMany
    {
        return $this->morphMany(Audit::class, 'auditable');
    }

    public function requests()
    {
        return $this->all()->map->request;
    }

    public function statusName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => [
                'review' => __('lines.statuses.status'),
                'extended' => __('lines.statuses.extended'),
                'deleted' => __('lines.statuses.deleted'),
                'open' => __('lines.statuses.open'),
            ][$this->newStatus] ?? '',
        );
    }

    public function expires(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => [
                'Yes' => 'never',
                'No' => $this->end_date->format('d.m.Y'),
            ][$this->no_expiry] ?? '',
        );
    }

    public function lastStatusName(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'review' => __('lines.statuses.status'),
                'extended' => __('lines.statuses.extended'),
                'deleted' => __('lines.statuses.deleted'),
                'open' => '',
            ][$this->status] ?? '',
        );
    }

    public function businessServiceName(): Attribute
    {
        return Attribute::make(
            get: fn () => cache()->rememberForever("business_service_name-{$this->id}",
                fn () => $this->businessService()->get('name')->first()->name),
        );
    }

    public function statusBackground(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'open' => 'bg-gray-100',
                'extended' => 'bg-green-100',
                'deleted' => 'bg-red-100',
                'review' => 'bg-yellow-100',
            ][$this->newStatus] ?? '',
        );
    }

    public function statusText(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'open' => 'text-gray-500',
                'extended' => 'text-green-500',
                'deleted' => 'text-red-500',
                'review' => 'text-yellow-500',
            ][$this->newStatus] ?? '',
        );
    }

    /**
     * Change the Status for PCI to 'review' if its value is unequal to `delete` and `last_review` is behind 6 months or `null`
     */
    public function newStatus(): Attribute
    {
        return Attribute::make(fn () => $this->status != 'deleted' && $this->pci_dss
            ? ($this->last_review <= now()->subQuarter() || $this->last_review == null
                ? 'review'
                : $this->status)
            : $this->status,
        );
    }

    public function pci(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->pci_dss ? 'Yes' : 'No',
        );
    }

    public function sourceString(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', json_decode($this->source, true)),
        );
    }

    public function destinationString(): Attribute
    {
        return Attribute::make(
            get: fn () => implode(', ', json_decode($this->destination, true)),
        );
    }

    public function scopeAdded($query)
    {
        $query->where('action', '=', 'add')
            ->where('end_date', '>=', now());
    }

    /**
     * Show only PCI Relevant values which where never reviewed or its last review is behind 6 Month.
     */
    public function scopeReview($query, $pci = false, $all = false): void
    {
        $query->where(function ($query) use ($all, $pci) {
            $query->where('action', '=', 'add');
            if (! $all) {
                $query->where('pci_dss', '=', ! $pci);
            }
            $query->where('status', '!=', 'deleted');
            $query->where('end_date', '>', now());

            $query->where(function ($sub) {
                $sub->where('last_review', '=', null)
                    ->orWhere('last_review', '<=', now()->subQuarter());
            });
        });
    }

    public function scopeForFirewallRequest($query)
    {
        return $query->with(['businessService' => fn ($request) => $request->select('id', 'name')])
            ->select(['action', 'type_destination', 'destination', 'type_source', 'source', 'service', 'destination_port', 'description', 'no_expiry', 'end_date', 'pci_dss', 'nat_required', 'application_id', 'contact', 'business_purpose', 'business_service_id'])
            ->first();
    }

    public function scopeOpen($query): void
    {
        $query->where(function ($query) {
            $query->where('action', '=', 'add');
            $query->where('pci_dss', '=', false);
            $query->where('status', '!=', 'deleted');
            $query->where('end_date', '>=', now());

            $query->where(function ($sub) {
                $sub->where('last_review', '=', null)
                    ->orWhere('last_review', '<=', now()->subQuarter());
            });
        });
    }

    public function scopeExtended($query): void
    {
        $query->where(function ($query) {
            $query->where('action', '=', 'add');
            $query->where('status', '=', 'extended');
            $query->where(function ($sub) {
                $sub->where('last_review', '=', null)
                    ->orWhere('last_review', '<=', now()->subQuarters(3));
            });

        });
    }

    public function scopeDeleted($query): void
    {
        $query->where(function ($query) {
            $query->where('status', '=', 'deleted')
                ->whereNotNull('last_review');
        });
    }

    public function scopeOwn($query): void
    {
        $query->whereRelation('businessService', function ($query) {
            $query->whereIn('name', auth()->user()->businessServices()->select('name')->get()->map->name);
        });
    }

    public function scopeByBusinessService(Builder $query, $value): Builder
    {
        return $query->whereRelation('businessService', 'name', '=', $value);
    }

    public function scopeLastReview(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('last_review', '=', null)
                ->orWhere('last_review', '<=', now()->subQuarter());
        });
    }

    public function scopeOrNotLastReview(Builder $query): Builder
    {
        return $query->orWhereNot('last_review', '<=', now()->subQuarter());
    }

    public function scopeNotLastReview(Builder $query): Builder
    {
        return $query->whereNot('last_review', '<=', now()->subQuarter());
    }

    public function scopeSearchBy($query, $terms = null)
    {
        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%'.$term.'%';
            $query->when($term, fn ($query, $term) => $query
                ->whereIn('service_now_request_id', ServiceNowRequest::query()
                    ->where('ritm_number', 'like', $term)
                    ->orWhere('subject', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('requestor_mail', 'like', $term)
                    ->pluck('id'))
                ->orWhere('description', 'like', $term)
                ->orWhere('source', 'like', $term)
                ->orWhere('destination', 'like', $term)
                ->orWhere('id', 'like', $term)
            );
        });
    }

    public function scopeVisibleTo($query, $own = false)
    {
        if (auth()->user()->operations()->contains(
            $this->updateOrCreate('serviceNow/firewall/request/readAll', 'Can read all firewall-requests')
        ) && ! $own) {
            return;
        }
        $query->own();
    }

    public function sourceStringShort($length = 80): Attribute
    {
        return Attribute::make(
            get: fn () => Str::limit(implode(', ', json_decode($this->source, true)), $length),
        );
    }

    public function destinationStringShort($length = 80): Attribute
    {
        return Attribute::make(
            get: fn () => Str::limit(implode(', ', json_decode($this->destination, true)), $length),
        );
    }
}

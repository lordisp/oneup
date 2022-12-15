<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Gate;


/**
 * @property mixed $request
 */
class FirewallRule extends Model
{
    use HasFactory, Uuid;

    const REVIEW = 6;

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
        'last_review'
    ];

    protected $casts = [
        'end_date' => 'datetime',
        'last_review' => 'datetime',
    ];


    public function request(): BelongsTo
    {
        return $this->belongsTo(ServiceNowRequest::class, 'service_now_request_id');
    }

    public function requests()
    {
        return $this->all()->map->request;
    }

    public function tags()
    {
        return $this->request->tags();
    }


    public function statusName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => [
                'review' => __('lines.statuses.status'),
                'extended' => __('lines.statuses.extended'),
                'deleted' => __('lines.statuses.deleted'),
                'open' => '',
            ][$this->newStatus] ?? '',
        );
    }

    public function lastStatusName(): Attribute
    {
        return Attribute::make(
            get: fn() => [
                'review' => __('lines.statuses.status'),
                'extended' => __('lines.statuses.extended'),
                'deleted' => __('lines.statuses.deleted'),
                'open' => '',
            ][$this->status] ?? '',
        );
    }

    public function getStatusBackgroundAttribute()
    {
        return [
            'open' => 'bg-gray-100',
            'extended' => 'bg-green-100',
            'deleted' => 'bg-red-100',
            'review' => 'bg-yellow-100',
        ][$this->newStatus] ?? '';
    }

    public function statusText(): Attribute
    {
        return Attribute::make(
            get: fn() => [
                'open' => 'text-gray-500',
                'extended' => 'text-green-500',
                'deleted' => 'text-red-500',
                'review' => 'text-yellow-500',
            ][$this->newStatus] ?? '',
        );
    }

    /**
     * Change the Status to 'review' if its value is unequal to `delete` and `last_review` is behind 6 months or `null`
     * @return Attribute
     */
    public function newStatus(): Attribute
    {
        return Attribute::make(
            function () {
                if ($this->status != 'deleted' && $this->pci_dss == true) {
                    if ($this->last_review <= now()->subMonths(self::REVIEW) || $this->last_review == null) {
                        return 'review';
                    } else {
                        return $this->status;
                    }
                } else {
                    return $this->status;
                }
            },
        );
    }


    public function scopeAdded($query)
    {
        $query->where('action', '=', 'add');
    }

    /**
     * Show only PCI Relevant values which where never reviewed or its last review is behind 6 Month.
     * @param $query
     * @return void
     */
    public function scopeReview($query)
    {
        $query->where(function ($query) {
            $query->where('action', '=', 'add');
            $query->where('pci_dss', '=', true);
            $query->where('status', '!=', 'deleted');

            $query->where(function ($sub) {
                $sub->where('last_review', '=', null)
                    ->orWhere('last_review', '<=', now()->subMonths(self::REVIEW));
            });
        });
    }

    public function scopeLastReview(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('last_review', '=', null)
                ->orWhere('last_review', '<=', now()->subMonths(self::REVIEW));
        });
    }

    public function scopeOrNotLastReview(Builder $query): Builder
    {
        return $query->orWhereNot('last_review', '<=', now()->subMonths(self::REVIEW));
    }

    public function scopeNotLastReview(Builder $query): Builder
    {
        return $query->whereNot('last_review', '<=', now()->subMonths(self::REVIEW));
    }

    public function scopeSearchBy($query, $terms = null)
    {
        collect(explode(' ', $terms))->filter()->each(function ($term) use ($query) {
            $term = '%' . $term . '%';
            $query->when($term, fn($query, $term) => $query
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

    public function scopeVisibleTo($query, User $user, $own = false)
    {
        if (Gate::allows('serviceNow-firewallRequests-readAll') && !$own) return;
        $query->whereIn('service_now_request_id', ServiceNowRequest::query()
            ->where('requestor_mail', '=', $user->email)
            ->pluck('id'));
    }
}

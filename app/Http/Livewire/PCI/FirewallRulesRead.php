<?php

namespace App\Http\Livewire\PCI;

use App\Events\FirewallRequestsDeleteAllEvent;
use App\Http\Livewire\DataTable\WithBulkActions;
use App\Http\Livewire\DataTable\WithCachedRows;
use App\Http\Livewire\DataTable\WithFilteredColumns;
use App\Http\Livewire\DataTable\WithPerPagePagination;
use App\Http\Livewire\DataTable\WithSearch;
use App\Http\Livewire\DataTable\WithSorting;
use App\Jobs\InviteFirewallReviewerJob;
use App\Jobs\ServiceNow\CreateFirewallRequestJob;
use App\Jobs\ServiceNowDeleteAllJob;
use App\Models\BusinessService;
use App\Models\FirewallRule;
use App\Models\ServiceNowRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * @property mixed $rows
 * @property mixed $queryRows
 */
class FirewallRulesRead extends Component
{
    use WithPerPagePagination, WithSorting, WithFilteredColumns, WithBulkActions, WithSearch, WithCachedRows;

    public ServiceNowRequest $request;

    public $rule;

    protected $queryString = [
        'filters' => ['except' => [
            'own' => true,
            'status' => 'review',
            'bs' => '',
        ]],
    ];

    public function mount()
    {
        if (!Gate::any(['viewAny'], FirewallRule::class)) $this->redirect(RouteServiceProvider::HOME);
    }

    public function deleteAll()
    {
        if (Gate::denies('serviceNow-firewallRequests-deleteAll')) abort(403, __('messages.not_allowed'));

        ServiceNowDeleteAllJob::dispatch(auth()->user());

        Log::info(auth()->user()->email . ' deleted all firewall-rules.');

        $this->event(__('messages.start_delete_all_requests'), 'success');
    }

    public function edit($key)
    {
        $this->useCachedRows();
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'edit']);
        $this->rule = $this->queryRows->filter(function ($query) use ($key) {
            return $query->id == $key;
        })->first();
    }

    public function extendConfirm()
    {
        $this->useCachedRows();
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'edit']);
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'extendConfirm']);
    }

    public function deleteConfirm()
    {
        $this->useCachedRows();
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'edit']);
        $this->dispatchBrowserEvent('open-modal', ['modal' => 'deleteConfirm']);
    }

    public function extend()
    {
        $this->rule->update([
            'status' => 'extended',
            'last_review' => now(),
        ]);

        $this->rule->audits()->create([
            'actor' => auth()->user()->email,
            'activity' => 'Extend Firewall-Rule',
            'status' => 'Success',
        ]);

        $this->event('Rule has been extended!', 'success');

        $this->dispatchBrowserEvent('close-modal', ['modal' => 'extendConfirm']);
    }

    public function delete()
    {
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'deleteConfirm']);

        CreateFirewallRequestJob::dispatch($this->rule, auth()->user());

        $this->rule->update([
            'status' => 'deleted',
            'last_review' => now(),
        ]);

        $this->event('Saved...', 'success');
    }

    public function sendNotification()
    {
        InviteFirewallReviewerJob::dispatch();

        info(__('messages.dispatched_firewall_review_mails', ['email' => auth()->user()->email]));

        $this->event('Done', 'success');
    }

    public function getQueryRowsProperty()
    {
        $query = $this->rows
            ->added()
            ->visibleTo()
            ->searchBy($this->search);

        $this->applyFiltering($query);

        return $this->cache(function () use ($query) {
            return $this->applyPagination($query);
        });
    }

    public $searchBs;
    public $bs = [];

    public function updatedSearchBs()
    {
        $this->filters['bs'] = $this->searchBs;
    }


    public function getMyBusinessServiceProperty()
    {
        return auth()->user()->businessServices()
            ->when(!empty($this->searchBs), fn($query) => $query->byName($this->searchBs))
            ->select(['id', 'name'])
            ->get()
            ->map(function ($q) {
                return [
                    'value' => $q->name,
                    'lable' => $q->id,
                ];
            });
    }

    public function getBusinessServicesProperty()
    {
        return $this->filters['own']
            ? $this->myBusinessService
            : BusinessService::query()
                ->when(!empty($this->searchBs), fn($query) => $query->byName($this->searchBs))
                ->select(['id', 'name'])
                ->get()
                ->map(function ($q) {
                    return [
                        'value' => $q->name,
                        'lable' => $q->id,
                    ];
                });
    }


    public function setBs($bs)
    {
        $this->searchBs = $bs;
        $this->bs = $bs;
        $this->filters['bs'] = $bs;
    }

    public function getRowsProperty(FirewallRule $model): Builder
    {
        return $model::query()->with(['request', 'businessService', 'audits']);
    }

    public function render(): View
    {
        return view('livewire.p-c-i.firewall-rules-read', [
            'rows' => $this->queryRows,
        ]);
    }
}

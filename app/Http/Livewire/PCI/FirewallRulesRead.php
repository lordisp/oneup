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
use Illuminate\Database\QueryException;
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
        $this->rule->status = 'extended';
        $this->rule->last_review = now();
        try {
            $this->rule->save();
            Log::info(auth()->user()->id . ' has extended rule from ' . $this->rule->request->subject, $this->rule->toArray());
            $this->event('Rule has been extended!', 'success');
        } catch (QueryException $exception) {
            $this->event('Failed to update Database! ' . $exception->getMessage(), 'error');
            Log::error(auth()->user()->id . ' tried to extended rule from ' . $this->rule->request->subject . 'but failed.', $this->rule->toArray());
        }
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'extendConfirm']);


    }

    public function delete()
    {
        $this->rule->status = 'deleted';
        $this->rule->last_review = now();
        $saved = $this->rule->save();
        $this->dispatchBrowserEvent('close-modal', ['modal' => 'deleteConfirm']);

        // CreateFirewallRequestJob::dispatch($this->rule->id, auth()->user());

        $this->event('Rule has been flagged as decommissioned!', 'success');
        if ($saved) Log::info(auth()->user()->id . ' has decommissioned rule from ' . $this->rule->request->subject, $this->rule->toArray());
        if (!$saved) Log::error(auth()->user()->id . ' tried to decommission rule from ' . $this->rule->request->subject . 'but failed.', $this->rule->toArray());
    }

    public function sendNotification()
    {
        InviteFirewallReviewerJob::dispatch();
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
        return $model::query()->with(['request', 'businessService']);
    }

    public function render(): View
    {
        return view('livewire.p-c-i.firewall-rules-read', [
            'rows' => $this->queryRows,
        ]);
    }
}

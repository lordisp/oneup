<?php

namespace App\Http\Livewire\DataTable;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

trait WithFilteredColumns
{
    public array $filters = [
        'own' => true,
        'status' => 'review',
        'bs' => '',
    ];

    public $columns;

    public function mountWithFilteredColumns()
    {
        $this->filters = session('filters', $this->filters);
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function persistFilter($key, $value): void
    {
        $filters[$value] = $key;
        $filters = array_merge($this->filters, $filters);
        session()->put('filters', $filters);
    }

    public function resetFilters(): void
    {
        $this->reset('filters', 'bs', 'searchBs');
        session()->forget('filters');
        $this->resetPage();
    }

    public function applyFiltering($query): void
    {
        foreach ($this->filters as $filter => $value) {
            if ($filter === 'status') {
                $this->applyStatusFilter($query, $value);
            }

            if ($filter === 'own' && $value === true) {
                $query->when($filter, fn($query) => $query->own());
            }

            if ($filter === 'bs' && !empty($value)) {
                $query->when($filter, fn($query) => $query->byBusinessService($value));
            }
        }
    }

    private function applyStatusFilter($query, $value): void
    {
        if (in_array($value, ['review', 'open', 'extended', 'deleted'])) {
            $query->when('status', fn($query) => $query->{$value}());
        }
    }

    private function getDate($filters): Carbon|string
    {
        $date = new Carbon();
        if (Arr::has($filters, ['expiry-max'])) {
            return $date->parse($filters['expiry-max']);
        }

        return $date->now();
    }
}

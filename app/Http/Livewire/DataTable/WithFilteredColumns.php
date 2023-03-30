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
            switch ($filter) {
                case $filter == 'status' && $value === 'review' :
                    $query->when($filter, fn($query) => $query->review());
                    break;
                case $filter == 'status' && $value === 'open' :
                    $query->when($filter, fn($query) => $query->open());
                    break;
                case $filter == 'status' && $value === 'extended' :
                    $query->when($filter, fn($query) => $query->extended());
                    break;
                case $filter == 'status' && $value === 'deleted' :
                    $query->when($filter, fn($query) => $query->deleted());
                    break;
                case $filter === 'own' && $value === true:
                    $query->when($filter, fn($query) => $query->own());
                    break;
                case $filter === 'bs' && !empty($value):
                    $query->when($filter, fn($query) => $query->byBusinessService($value));
                    break;
            }
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

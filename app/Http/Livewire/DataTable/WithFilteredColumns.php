<?php

namespace App\Http\Livewire\DataTable;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

trait WithFilteredColumns
{
    public array $filters = [
        'pci_dss' => '1',
        'own' => '',
        'status' => 'review',
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
        $this->reset('filters');
        session()->forget('filters');
        $this->resetPage();
    }

    public function applyFiltering($query): void
    {
        foreach ($this->filters as $filter => $value) {
            switch ($filter) {
                case $filter == 'pci_dss' && !empty($value) :
                    $value = (int)$value;
                    $query->when($filter, function ($query, $filter) use ($value) {
                        return $query->where('pci_dss', '=', $value);
                    });
                    break;
                case $filter == 'status' && !empty($value) && is_string($value) :
                    if ($value == 'review') {
                        $query->when($filter, fn($query, $filter) => $query->where(function ($sub) use ($value) {
                            $sub->where('status', '=', $value)
                                ->orWhere('status', '=', 'extended')
                                ->where('pci_dss', '=', 1);
                        })->lastReview());

                    } elseif ($value == 'deleted') {
                        $query->when($filter, fn($query, $filter) => $query->where('status', '=', $value));

                    } elseif ($value == 'extended') {
                        $query->when($filter, fn($query, $filter) => $query->where(function ($sub) use ($value) {
                            if ($this->filters['pci_dss'] == 1) {
                                $sub->where('status', '=', $value)
                                    ->where('pci_dss', '=', 1)
                                    ->notLastReview();
                            } elseif ($this->filters['pci_dss'] == "'0'") {
                                $sub->where('status', '=', $value)
                                    ->where('pci_dss', '=', 0)
                                    ->notLastReview();
                            } else {
                                $sub->where('status', '=', $value)
                                    ->notLastReview();
                            }
                        }));
                    } elseif ($value == 'open') {
                        $query->when($filter, fn($query, $filter) => $query->where(function ($sub) use ($value) {
                            $sub->where('status', '=', $value)
                                ->orWhere('status', '=', 'extended')
                                ->where('pci_dss', '=', 0);
                        })->lastReview());
                    }
                    break;
                case $filter == 'own' && !empty($value) :
                    $value = (int)$value;
                    $query->when($filter, fn($query) => $query->visibleTo(auth()->user(), $value));
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

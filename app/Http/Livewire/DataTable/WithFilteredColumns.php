<?php

namespace App\Http\Livewire\DataTable;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

trait WithFilteredColumns
{
    public array $filters = [
        'sso' => '',
        'created-min' => null,
        'created-max' => null,
        'show-deleted' => false,
    ];

    public $columns;

    public function mountWithFilteredColumns()
    {
        $this->filters = session()->get('filters', $this->filters);
    }

    public function persistFilter($key, $value)
    {
        $filters[$value] = $key;
        $filters = array_merge($this->filters, $filters);
        session()->put('filters', $filters);
    }

    public function applyFiltering($query)
    {
        foreach ($this->filters as $filter => $value) {
            switch ($filter) {
                case $filter == 'sso' && ! empty($value):
                    $query->when($filter, fn ($query, $filter) => $query->where('az_applications.manifest->sso', $value));
                    break;
                case $filter == 'created-min' && ! empty($value):
                    $query->when($filter, fn ($query, $filter) => $query->where('az_applications.manifest->createdDateTime', '>=', Carbon::parse($value)->startOfDay()->toIso8601String()));
                    break;
                case $filter == 'created-max' && ! empty($value):
                    $query->when($filter, fn ($query, $filter) => $query->where('az_applications.manifest->createdDateTime', '<=', Carbon::parse($value)->endOfDay()->toIso8601String()));
                    break;
                case $filter == 'expiry' && $value == 'preferredTokenSigningKeyEndDateTime':
                    $this->columns['preferredTokenSigningKeyEndDateTime'] = true;
                    $query->where('az_service_principals.manifest->preferredTokenSigningKeyEndDateTime', '<=', $this->getDate($this->filters)->timezone('Europe/Berlin')->endOfDay()->toIso8601String());
                    break;
                case $filter == 'show-deleted' && $value == true:
                    $query->onlyTrashed();
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

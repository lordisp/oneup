<?php

namespace App\Http\Livewire\DataTable;

/*
 *  This Trait requires to have all sortable data within the public $results property array and the <x-table> component.
 *  To enable sorting, use the following pattern in the tables' header for each row to be sortable:
 *  <x-table.heading sortable wire:click="sortBy('displayName')" :direction="$sorts['displayName'] ?? null">Display name</x-table.heading>
 * */

trait WithSorting
{
    public $sorts = [];

    public function sortBy($field)
    {
        if (! isset($this->sorts[$field])) {
            return $this->sorts[$field] = 'asc';
        }

        if ($this->sorts[$field] === 'asc') {
            return $this->sorts[$field] = 'desc';
        }

        unset($this->sorts[$field]);
    }

    public function applySorting($query)
    {
        foreach ($this->sorts as $field => $directions) {
            $query->when($field, fn ($query, $field) => $query->orderBy($field, $directions));
        }

        return $query;
    }
}

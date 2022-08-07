<?php

namespace App\Http\Livewire\DataTable;

use Illuminate\Support\Arr;

trait WithSearchQueryString
{
    public string $query = '';

    public function mountWithSearchQueryString()
    {
        $this->query = $this->search ? Arr::query(['search' => request()->query('search')]) : '';
        $this->search = Arr::has(request()->query(), 'search') ? request()->query('search') : '';
    }

    public function updatedSearch()
    {
        $this->query = $this->search ? Arr::query(['search' => $this->search]) : '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}

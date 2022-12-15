<?php

namespace App\Http\Livewire\DataTable;

trait WithSearch
{
    public string $search = '';

    public function updatedSearch(){
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }
}
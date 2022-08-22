<?php

namespace App\Http\Livewire\DataTable;

/* You need to create a computed property in your livewire component
   that returns a (eloquent) collection
   e.g.:
        public function getRowsProperty()
        {
            return User::all();
        }
 */

trait WithBulkActions
{
    public bool $selectPage = false, $selectPagePopup = false, $selectAll = false;

    public array $selected = [];
    public $objects = [];

    public function updatedSelected(): void
    {
        $this->selectPage = count($this->selected) >= $this->queryRows->count();

        $this->selectPagePopup = count($this->selected) >= $this->queryRows->total()
            ? false
            : $this->selectPage;
    }

    public function updatedSelectPage($value): void
    {
        $this->selected = $value ? $this->queryRows->pluck('id')->toArray() : [];
        $this->selectPagePopup = $value;
    }

    public function selectAll(): void
    {
        $this->selected = $this->rows->pluck('id')->toArray();

        $this->selectPagePopup = false;
    }

    public function resetBulk(): void
    {
        $this->selected = [];
        $this->selectPage = false;
        $this->selectAll = false;
        $this->selectPagePopup = false;
    }
}

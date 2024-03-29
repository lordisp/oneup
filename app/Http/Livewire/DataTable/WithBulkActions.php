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
    public bool $selectPage = false;

    public bool $selectPagePopup = false;

    public bool $selectAll = false;

    public bool $action = false;

    public array $selected = [];

    public $objects = [];

    public function updatedSelected(): void
    {
        $this->selectPage = count($this->selected) >= $this->queryRows->count();

        $this->selectPagePopup = count($this->selected) >= $this->queryRows->total()
            ? false
            : $this->selectPage;

        $this->active = ! empty($this->selected);
    }

    public function updatedSelectPage($value): void
    {
        $this->selected = $value ? $this->queryRows->pluck('id')->toArray() : [];
        $this->selectPagePopup = count($this->selected) >= $this->queryRows->total()
            ? false
            : $this->selectPage;
        $this->active = ! empty($this->selected);
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

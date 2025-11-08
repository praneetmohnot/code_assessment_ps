<?php

namespace App\Http\Livewire\GeoZones;

use App\Models\Category;
use App\Models\GeoZone;
use Livewire\Component;
use Livewire\WithPagination;

class ListZones extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryId = '';
    public $sortField = 'updated_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryId' => ['except' => ''],
        'sortField' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categoryId = '';
        $this->sortField = 'updated_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $zonesQuery = GeoZone::query()
            ->with('category')
            ->search($this->search)
            ->filterCategory($this->categoryId);

        if ($this->sortField === 'category') {
            $zonesQuery->leftJoin('categories', 'geo_zones.category_id', '=', 'categories.id')
                ->select('geo_zones.*')
                ->orderBy('categories.name', $this->sortDirection);
        } else {
            $zonesQuery->orderBy($this->sortField, $this->sortDirection);
        }

        $zones = $zonesQuery->paginate(5);

        return view('livewire.geo-zones.list-zones', [
            'zones' => $zones,
            'categories' => Category::orderBy('name')->get(),
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ])->layout('layouts.app');
    }
}

<?php

namespace App\Http\Livewire\GeoZones;

use App\DataTransferObjects\GeoZoneData;
use App\Models\Category;
use App\Services\GeoZoneService;
use Livewire\Component;

class CreateZone extends Component
{
    public string $name = '';

    public string $categoryId = '';

    public string $geometry = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'categoryId' => ['required', 'exists:categories,id'],
            'geometry' => ['required', 'string'],
        ];
    }

    public function save(GeoZoneService $service)
    {
        $data = $this->validate();

        $zone = $service->create(GeoZoneData::fromArray([
            'name' => $data['name'],
            'categoryId' => $data['categoryId'],
            'geometry' => $this->geometry,
        ]));

        session()->flash('status', 'GeoZone created successfully.');

        return redirect()->route('geo-zones.show', $zone);
    }

    public function render()
    {
        return view('livewire.geo-zones.edit-zone', [
            'categories' => Category::orderBy('name')->get(),
            'isEditing' => false,
            'zone' => null,
        ])->layout('layouts.app');
    }
}

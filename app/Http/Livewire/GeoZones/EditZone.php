<?php

namespace App\Http\Livewire\GeoZones;

use App\DataTransferObjects\GeoZoneData;
use App\Models\Category;
use App\Models\GeoZone;
use App\Services\GeoZoneService;
use Livewire\Component;

class EditZone extends Component
{
    public GeoZone $zone;

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

    public function mount(GeoZone $zone): void
    {
        $this->zone = GeoZone::select('geo_zones.*')
            ->selectRaw('ST_AsGeoJSON(geometry) as geometry_geojson')
            ->findOrFail($zone->id);

        $this->name = $this->zone->name;
        $this->categoryId = (string) $this->zone->category_id;
        $this->geometry = $this->zone->geometry_geojson;
    }

    public function save(GeoZoneService $service)
    {
        $data = $this->validate();

        $zone = $service->update(
            $this->zone,
            GeoZoneData::fromArray([
                'name' => $data['name'],
                'categoryId' => $data['categoryId'],
                'geometry' => $this->geometry,
            ])
        );

        session()->flash('status', 'GeoZone updated successfully.');

        return redirect()->route('geo-zones.show', $zone);
    }

    public function render()
    {
        return view('livewire.geo-zones.edit-zone', [
            'categories' => Category::orderBy('name')->get(),
            'isEditing' => true,
            'zone' => $this->zone,
        ])->layout('layouts.app');
    }
}

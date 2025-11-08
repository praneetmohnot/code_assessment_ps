<?php

namespace App\Http\Livewire\GeoZones;

use App\Models\GeoZone;
use Livewire\Component;

class ViewZone extends Component
{
    public GeoZone $zone;

    public string $geometry = '';

    public function mount(GeoZone $zone): void
    {
        $this->zone = GeoZone::with('category')
            ->select('geo_zones.*')
            ->selectRaw('ST_AsGeoJSON(geometry) as geometry_geojson')
            ->findOrFail($zone->id);

        $this->geometry = $this->zone->geometry_geojson;
    }

    public function render()
    {
        return view('livewire.geo-zones.view-zone', [
            'zone' => $this->zone,
            'geometry' => $this->geometry,
        ])->layout('layouts.app');
    }
}

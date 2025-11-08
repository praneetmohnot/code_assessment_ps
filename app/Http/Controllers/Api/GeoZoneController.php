<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GeoZoneResource;
use App\Models\GeoZone;
use Illuminate\Http\Request;

class GeoZoneController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $zones = GeoZone::with('category')
            ->select('geo_zones.*')
            ->selectRaw('ST_AsGeoJSON(geometry) as geometry_geojson')
            ->search($request->query('search'))
            ->filterCategory($request->query('category_id'))
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        return GeoZoneResource::collection($zones);
    }

    public function show(GeoZone $geoZone)
    {
        $zone = GeoZone::with('category')
            ->select('geo_zones.*')
            ->selectRaw('ST_AsGeoJSON(geometry) as geometry_geojson')
            ->findOrFail($geoZone->id);

        return new GeoZoneResource($zone);
    }
}

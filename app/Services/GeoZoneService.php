<?php

namespace App\Services;

use App\DataTransferObjects\GeoZoneData;
use App\Models\GeoZone;
use App\Support\GeoJson;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class GeoZoneService
{
    public function create(GeoZoneData $data): GeoZone
    {
        $ewkt = $this->normalizeGeometry($data->geoJson);

        $zone = new GeoZone();
        $zone->fill([
            'name' => $data->name,
            'category_id' => $data->categoryId,
        ]);
        $zone->geometry = $ewkt;
        $zone->save();

        return $zone;
    }

    public function update(GeoZone $zone, GeoZoneData $data): GeoZone
    {
        $ewkt = $this->normalizeGeometry($data->geoJson);

        $zone->fill([
            'name' => $data->name,
            'category_id' => $data->categoryId,
        ]);
        $zone->geometry = $ewkt;
        $zone->save();

        return $zone;
    }

    private function normalizeGeometry(string $geoJson): string
    {
        try {
            $geometry = GeoJson::extractGeometry($geoJson);
            GeoJson::assertMinimumVertices($geometry);
            $normalized = json_encode($geometry);
            if ($normalized === false) {
                throw new InvalidArgumentException('Unable to encode the geometry payload.');
            }
            $valid = GeoJson::ensureValidity($normalized);

            return GeoJson::toEwkt($valid);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'geometry' => $exception->getMessage(),
            ]);
        }
    }
}

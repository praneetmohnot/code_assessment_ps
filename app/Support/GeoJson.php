<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GeoJson
{
    public static function extractGeometry(string $payload): array
    {
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException('The provided GeoJSON is malformed.');
        }

        if (($decoded['type'] ?? null) === 'Feature') {
            $geometry = $decoded['geometry'] ?? null;
        } else {
            $geometry = $decoded;
        }

        if (! is_array($geometry) || ! isset($geometry['type'])) {
            throw new InvalidArgumentException('The GeoJSON payload is missing a geometry object.');
        }

        if (! in_array($geometry['type'], ['Polygon', 'MultiPolygon'], true)) {
            throw new InvalidArgumentException('Only Polygon or MultiPolygon geometries are supported.');
        }

        if ($geometry['type'] === 'Polygon') {
            $geometry = [
                'type' => 'MultiPolygon',
                'coordinates' => [$geometry['coordinates'] ?? []],
            ];
        }

        return $geometry;
    }

    public static function assertMinimumVertices(array $geometry): void
    {
        $coordinates = $geometry['coordinates'] ?? [];

        foreach ($coordinates as $polygon) {
            $outerRing = $polygon[0] ?? [];
            if (count($outerRing) >= 4) {
                return;
            }
        }

        throw new InvalidArgumentException('Each polygon must contain at least three vertices.');
    }

    public static function ensureValidity(string $geoJson): string
    {
        if (self::isValid($geoJson)) {
            return $geoJson;
        }

        $repaired = DB::selectOne('SELECT ST_AsGeoJSON(ST_MakeValid(ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(?)), 4326))) AS geojson', [$geoJson]);

        if ($repaired && $repaired->geojson && self::isValid($repaired->geojson)) {
            return $repaired->geojson;
        }

        throw new InvalidArgumentException('The geometry could not be validated (ST_IsValid failed).');
    }

    public static function toEwkt(string $geoJson): string
    {
        $result = DB::selectOne('SELECT ST_AsEWKT(ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(?)), 4326)) AS ewkt', [$geoJson]);

        if (! $result || empty($result->ewkt)) {
            throw new InvalidArgumentException('Unable to convert GeoJSON into EWKT.');
        }

        return $result->ewkt;
    }

    public static function isValid(string $geoJson): bool
    {
        $result = DB::selectOne('SELECT ST_IsValid(ST_SetSRID(ST_Multi(ST_GeomFromGeoJSON(?)), 4326)) AS valid', [$geoJson]);

        return (bool) ($result->valid ?? false);
    }
}

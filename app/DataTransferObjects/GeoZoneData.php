<?php

namespace App\DataTransferObjects;

class GeoZoneData
{
    public function __construct(
        public readonly string $name,
        public readonly int $categoryId,
        public readonly string $geoJson,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        return new self(
            $payload['name'],
            (int) $payload['categoryId'],
            $payload['geometry'],
        );
    }
}

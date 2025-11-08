<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\GeoZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GeoZoneApiTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name = 'War Risk'): Category
    {
        return Category::create(['name' => $name]);
    }

    private function makeZone(string $name, Category $category): GeoZone
    {
        $record = DB::selectOne(
            'INSERT INTO geo_zones (name, category_id, geometry, created_at, updated_at)
             VALUES (?, ?, ST_GeomFromEWKT(?), now(), now())
             RETURNING id',
            [$name, $category->id, 'SRID=4326;MULTIPOLYGON(((0 0,1 0,1 1,0 0)))']
        );

        return GeoZone::findOrFail($record->id);
    }

    public function test_index_returns_paginated_geo_zones(): void
    {
        $category = $this->category();
        $this->makeZone('Zone A', $category);

        $response = $this->getJson('/api/geo-zones');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Zone A'])
            ->assertJsonStructure(['data' => [['id', 'name', 'category', 'geometry']]]);
    }

    public function test_show_returns_single_geo_zone(): void
    {
        $category = $this->category();
        $zone = $this->makeZone('Zone B', $category);

        $response = $this->getJson('/api/geo-zones/' . $zone->id);

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $zone->id,
                'name' => 'Zone B',
            ]);
    }
}

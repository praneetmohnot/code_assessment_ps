<?php

namespace Tests\Feature;

use App\Http\Livewire\GeoZones\EditZone;
use App\Models\Category;
use App\Models\GeoZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EditGeoZoneTest extends TestCase
{
    use RefreshDatabase;

    private function createCategory(string $name = 'War Risk'): Category
    {
        return Category::create(['name' => $name]);
    }

    private function createZone(string $name, Category $category, ?string $ewkt = null): GeoZone
    {
        $ewkt = $ewkt ?? 'SRID=4326;MULTIPOLYGON(((0 0, 1 0, 1 1, 0 0)))';

        $record = DB::selectOne(
            'INSERT INTO geo_zones (name, category_id, geometry, created_at, updated_at)
             VALUES (?, ?, ST_GeomFromEWKT(?), now(), now())
             RETURNING id',
            [$name, $category->id, $ewkt]
        );

        return GeoZone::findOrFail($record->id);
    }

    private function samplePolygon(): string
    {
        return json_encode([
            'type' => 'Polygon',
            'coordinates' => [[[0, 0], [2, 0], [2, 2], [0, 0]]],
        ]);
    }

    public function test_user_can_update_geo_zone(): void
    {
        $warRisk = $this->createCategory('War Risk');
        $country = $this->createCategory('Country');
        $zone = $this->createZone('Old Zone', $warRisk);

        Livewire::test(EditZone::class, ['zone' => $zone])
            ->set('name', 'Updated Zone')
            ->set('categoryId', (string) $country->id)
            ->set('geometry', $this->samplePolygon())
            ->call('save')
            ->assertSessionHas('status', 'GeoZone updated successfully.');

        $this->assertDatabaseHas('geo_zones', [
            'id' => $zone->id,
            'name' => 'Updated Zone',
            'category_id' => $country->id,
        ]);
    }

    public function test_name_is_required_when_updating(): void
    {
        $category = $this->createCategory();
        $zone = $this->createZone('No Name Zone', $category);

        Livewire::test(EditZone::class, ['zone' => $zone])
            ->set('name', '')
            ->set('geometry', $this->samplePolygon())
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_geometry_validation_runs_on_update(): void
    {
        $category = $this->createCategory();
        $zone = $this->createZone('Invalid Geometry Zone', $category);

        $invalidPolygon = json_encode([
            'type' => 'Polygon',
            'coordinates' => [[[0, 0], [1, 1], [0, 0]]],
        ]);

        Livewire::test(EditZone::class, ['zone' => $zone])
            ->set('geometry', $invalidPolygon)
            ->call('save')
            ->assertHasErrors(['geometry']);
    }
}

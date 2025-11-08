<?php

namespace Tests\Feature;

use App\Http\Livewire\GeoZones\CreateZone;
use App\Models\Category;
use App\Models\GeoZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CreateGeoZoneTest extends TestCase
{
    use RefreshDatabase;

    private function seedCategory(): Category
    {
        return Category::create(['name' => 'War Risk']);
    }

    private function samplePolygon(): string
    {
        return json_encode([
            'type' => 'Polygon',
            'coordinates' => [[[0, 0], [1, 0], [1, 1], [0, 0]]],
        ]);
    }

    public function test_user_can_create_geo_zone(): void
    {
        $category = $this->seedCategory();

        Livewire::test(CreateZone::class)
            ->set('name', 'Test Zone')
            ->set('categoryId', (string) $category->id)
            ->set('geometry', $this->samplePolygon())
            ->call('save')
            ->assertSessionHas('status', 'GeoZone created successfully.');

        $this->assertDatabaseHas('geo_zones', [
            'name' => 'Test Zone',
            'category_id' => $category->id,
        ]);
    }

    public function test_name_is_required(): void
    {
        $category = $this->seedCategory();

        Livewire::test(CreateZone::class)
            ->set('categoryId', (string) $category->id)
            ->set('geometry', $this->samplePolygon())
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_category_is_required(): void
    {
        Livewire::test(CreateZone::class)
            ->set('name', 'No Category Zone')
            ->set('geometry', $this->samplePolygon())
            ->call('save')
            ->assertHasErrors(['categoryId' => 'required']);
    }

    public function test_geometry_is_required(): void
    {
        $category = $this->seedCategory();

        Livewire::test(CreateZone::class)
            ->set('name', 'No Geometry Zone')
            ->set('categoryId', (string) $category->id)
            ->call('save')
            ->assertHasErrors(['geometry' => 'required']);
    }

    public function test_geometry_requires_at_least_three_vertices(): void
    {
        $category = $this->seedCategory();

        $invalidPolygon = json_encode([
            'type' => 'Polygon',
            'coordinates' => [[[0, 0], [1, 0], [0, 0]]],
        ]);

        Livewire::test(CreateZone::class)
            ->set('name', 'Thin Zone')
            ->set('categoryId', (string) $category->id)
            ->set('geometry', $invalidPolygon)
            ->call('save')
            ->assertHasErrors(['geometry']);
    }

    public function test_only_polygon_or_multipolygon_supported(): void
    {
        $category = $this->seedCategory();

        $line = json_encode([
            'type' => 'LineString',
            'coordinates' => [[0, 0], [1, 1]],
        ]);

        Livewire::test(CreateZone::class)
            ->set('name', 'Bad Geometry Zone')
            ->set('categoryId', (string) $category->id)
            ->set('geometry', $line)
            ->call('save')
            ->assertHasErrors(['geometry']);
    }

    public function test_invalid_geometry_is_auto_repaired(): void
    {
        $category = $this->seedCategory();

        $bowTie = json_encode([
            'type' => 'Polygon',
            'coordinates' => [[[0, 0], [1, 1], [1, 0], [0, 1], [0, 0]]],
        ]);

        Livewire::test(CreateZone::class)
            ->set('name', 'Repaired Zone')
            ->set('categoryId', (string) $category->id)
            ->set('geometry', $bowTie)
            ->call('save')
            ->assertSessionHas('status', 'GeoZone created successfully.');

        $zone = GeoZone::whereName('Repaired Zone')->firstOrFail();
        $valid = DB::selectOne('SELECT ST_IsValid(geometry) as valid FROM geo_zones WHERE id = ?', [$zone->id]);
        $this->assertTrue((bool) $valid->valid);
    }

    public function test_polygon_is_stored_as_multipolygon(): void
    {
        $category = $this->seedCategory();

        Livewire::test(CreateZone::class)
            ->set('name', 'Polygon Zone')
            ->set('categoryId', (string) $category->id)
            ->set('geometry', $this->samplePolygon())
            ->call('save');

        $zone = GeoZone::whereName('Polygon Zone')->firstOrFail();
        $type = DB::selectOne('SELECT ST_GeometryType(geometry) as type FROM geo_zones WHERE id = ?', [$zone->id]);
        $this->assertSame('ST_MultiPolygon', $type->type);
    }

    public function test_duplicate_names_are_allowed(): void
    {
        $category = $this->seedCategory();

        $component = fn () => Livewire::test(CreateZone::class)
            ->set('name', 'Duplicate Zone')
            ->set('categoryId', (string) $category->id)
            ->set('geometry', $this->samplePolygon())
            ->call('save');

        $component();
        $component();

        $this->assertSame(2, GeoZone::whereName('Duplicate Zone')->count());
    }

    public function test_inputs_remain_after_validation_error(): void
    {
        $category = $this->seedCategory();

        Livewire::test(CreateZone::class)
            ->set('name', 'Sticky Zone')
            ->set('categoryId', (string) $category->id)
            ->call('save')
            ->assertHasErrors(['geometry' => 'required'])
            ->assertSet('name', 'Sticky Zone')
            ->assertSet('categoryId', (string) $category->id);
    }
}

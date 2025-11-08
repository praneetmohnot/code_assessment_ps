<?php

namespace Tests\Feature;

use App\Http\Livewire\GeoZones\ListZones;
use App\Models\Category;
use App\Models\GeoZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ListGeoZoneTest extends TestCase
{
    use RefreshDatabase;

    private function category(string $name): Category
    {
        return Category::create(['name' => $name]);
    }

    private function makeZone(string $name, Category $category, ?string $ewkt = null): GeoZone
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

    public function test_search_filters_by_name(): void
    {
        $warRisk = $this->category('War Risk');
        $this->makeZone('Atlantic Zone', $warRisk);
        $this->makeZone('Pacific Zone', $warRisk);

        Livewire::test(ListZones::class)
            ->set('search', 'Atlantic')
            ->assertSee('Atlantic Zone')
            ->assertDontSee('Pacific Zone');
    }

    public function test_category_filter_limits_results(): void
    {
        $warRisk = $this->category('War Risk');
        $country = $this->category('Country');

        $this->makeZone('War Zone', $warRisk);
        $this->makeZone('Country Zone', $country);

        Livewire::test(ListZones::class)
            ->set('categoryId', (string) $country->id)
            ->assertSee('Country Zone')
            ->assertDontSee('War Zone');
    }

    public function test_sort_by_name_changes_order(): void
    {
        $category = $this->category('War Risk');
        $this->makeZone('Zulu Zone', $category);
        $this->makeZone('Alpha Zone', $category);

        Livewire::test(ListZones::class)
            ->call('sortBy', 'name')
            ->assertSet('sortField', 'name')
            ->assertSeeInOrder(['Alpha Zone', 'Zulu Zone']);
    }

    public function test_sort_toggle_flips_direction(): void
    {
        $category = $this->category('War Risk');
        $this->makeZone('Alpha Zone', $category);
        $this->makeZone('Zulu Zone', $category);

        Livewire::test(ListZones::class)
            ->call('sortBy', 'name') // asc
            ->call('sortBy', 'name') // desc
            ->assertSet('sortDirection', 'desc')
            ->assertSeeInOrder(['Zulu Zone', 'Alpha Zone']);
    }

    public function test_clear_filters_resets_state(): void
    {
        $category = $this->category('War Risk');
        $another = $this->category('Country');
        $this->makeZone('Reset Zone', $category);

        Livewire::test(ListZones::class)
            ->set('search', 'Reset')
            ->set('categoryId', (string) $another->id)
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('categoryId', '')
            ->assertSet('sortField', 'updated_at')
            ->assertSet('sortDirection', 'desc');
    }
}

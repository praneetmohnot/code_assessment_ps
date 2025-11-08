<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\GeoZone;
use Illuminate\Database\Seeder;

class GeoZoneSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(['name' => 'War Risk']);

        $zones = [
            [
                'name' => 'Demo Gulf Zone',
                'geometry' => 'SRID=4326;MULTIPOLYGON(((50.0 24.5, 51.0 24.5, 51.0 25.5, 50.0 25.5, 50.0 24.5)))',
            ],
            [
                'name' => 'Demo Lagos Zone',
                'geometry' => 'SRID=4326;MULTIPOLYGON(((-3.6 6.2, -3.2 6.2, -3.2 6.6, -3.6 6.6, -3.6 6.2)))',
            ],
        ];

        foreach ($zones as $zoneData) {
            GeoZone::updateOrCreate(
                ['name' => $zoneData['name']],
                [
                    'category_id' => $category->id,
                    'geometry' => $zoneData['geometry'],
                ]
            );
        }
    }
}

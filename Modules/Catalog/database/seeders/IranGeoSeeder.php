<?php

declare(strict_types=1);

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use JsonException;
use Modules\Catalog\Models\City;
use Modules\Catalog\Models\Province;
use RuntimeException;

final class IranGeoSeeder extends Seeder
{
    public function run(): void
    {
        $file = module_path('Catalog', 'database/data/iran-geo.json');

        $raw = file_get_contents($file);
        if ($raw === false) {
            throw new RuntimeException("Cannot read geo data file at [{$file}].");
        }

        try {
            /** @var list<array{province: string, slug: string, cities: list<array{name: string, slug: string, lat: float, lng: float, major: bool}>}> $data */
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Invalid JSON in geo data: {$e->getMessage()}", previous: $e);
        }

        foreach ($data as $entry) {
            $province = Province::query()->updateOrCreate(
                ['slug' => $entry['slug']],
                ['name' => $entry['province']],
            );

            foreach ($entry['cities'] as $cityData) {
                City::query()->updateOrCreate(
                    ['slug' => $cityData['slug']],
                    [
                        'province_id' => $province->id,
                        'name' => $cityData['name'],
                        'latitude' => $cityData['lat'],
                        'longitude' => $cityData['lng'],
                        'is_major' => $cityData['major'],
                    ],
                );
            }
        }
    }
}

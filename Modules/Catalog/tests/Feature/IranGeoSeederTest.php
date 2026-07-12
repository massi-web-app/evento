<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Database\Seeders\IranGeoSeeder;
use Modules\Catalog\Models\City;
use Modules\Catalog\Models\Province;

uses(RefreshDatabase::class);

it('seeds provinces with their cities linked correctly', function (): void {
    $this->seed(IranGeoSeeder::class);

    expect(Province::query()->count())->toBeGreaterThanOrEqual(3);

    $tehran = City::query()->where('slug', 'tehran')->firstOrFail();

    expect($tehran->province->slug)->toBe('tehran')
        ->and($tehran->is_major)->toBeTrue();
});

it('is idempotent across repeated runs', function (): void {
    $this->seed(IranGeoSeeder::class);
    $before = City::query()->count();

    $this->seed(IranGeoSeeder::class);

    expect(City::query()->count())->toBe($before);
});

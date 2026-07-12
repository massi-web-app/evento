<?php

declare(strict_types=1);

namespace Modules\Catalog\Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Catalog\Models\City;
use Modules\Catalog\Models\Province;


/**
 * @extends Factory<City>
 */
final class CityFactory extends Factory
{

    protected $model = City::class;

    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'province_id' => Province::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::lower(Str::random(4)),
            'latitude' => fake()->latitude(25, 39),    // بازهٔ جغرافیایی ایران
            'longitude' => fake()->longitude(44, 63),
            'is_major' => false,
        ];
    }
}

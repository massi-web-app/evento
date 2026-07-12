<?php

declare(strict_types=1);

namespace Modules\Catalog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Catalog\Models\City;
use Modules\Catalog\Models\Venue;


/**
 * @extends Factory<Venue>
 */
final class VenueFactory extends Factory
{
    protected $model = Venue::class;

    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name' => 'سالن ' . fake()->unique()->company(),
            'address' => fake()->streetAddress(),
            'latitude' => fake()->latitude(25, 39),
            'longitude' => fake()->longitude(44, 63),
            'capacity' => fake()->numberBetween(50, 2000),
            'amenities' => ['parking', 'wifi'],
        ];
    }

    public function verified(): static
    {
        return $this->state(['is_verified' => true]);
    }
}

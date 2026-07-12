<?php

declare(strict_types=1);

namespace Modules\Catalog\Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Province;

/**
 * @extends Factory<Province>
 */
final class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }


}

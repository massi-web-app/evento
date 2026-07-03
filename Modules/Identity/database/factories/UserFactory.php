<?php

declare(strict_types=1);

namespace Modules\Identity\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Identity\Enums\UserStatus;
use Modules\Identity\Models\User;

final class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'first_name' => fake('fa_IR')->firstName(),
            'last_name' => fake('fa_IR')->lastName(),
            'phone' => '09' . fake()->unique()->numerify('#########'),
            'status' => UserStatus::Active,
            'locale' => 'fa',
            'timezone' => 'Asia/Tehran',
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => UserStatus::Pending, 'phone_verified_at' => null]);
    }

    public function banned(): static
    {
        return $this->state(['status' => UserStatus::Banned]);
    }

}

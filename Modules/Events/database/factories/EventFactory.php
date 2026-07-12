<?php
declare(strict_types=1);

namespace Modules\Events\Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Events\Enums\EventFormat;
use Modules\Events\Enums\EventStatus;
use Modules\Events\Models\Event;

final class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            // organizer_id و category_id عمداً اینجا نیستند —
            // تست باید صریح بدهد (helper: makeEventFor)
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(4)),
            'summary' => fake()->paragraph(),
            'format' => EventFormat::InPerson,
            'starts_at' => now()->addDays(30),
            'ends_at' => now()->addDays(30)->addHours(3),
            'capacity_total' => 200,
        ];
    }

    public function published(): EventFactory
    {
        return $this->state([
            'status' => EventStatus::Published,
            'published_at' => now(),
        ]);
    }
}

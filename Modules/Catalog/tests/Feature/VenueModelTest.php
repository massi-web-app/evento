<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Models\Venue;

uses(RefreshDatabase::class);

it('creates a venue with ULID, defaults and city chain', function (): void {
    $venue = Venue::factory()->create();

    expect($venue->public_id)->toHaveLength(26)
        ->and($venue->is_verified)->toBeFalse()
        ->and($venue->city->province)->not->toBeNull()
        ->and($venue->amenities)->toBe(['parking', 'wifi']);
});

it('soft deletes venues', function (): void {
    $venue = Venue::factory()->create();

    $venue->delete();

    expect(Venue::withTrashed()->find($venue->id))->not->toBeNull()
        ->and(Venue::find($venue->id))->toBeNull();
});

<?php

declare(strict_types=1);

namespace Modules\Identity\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Identity\Models\Organizer;

/**
 * @mixin Organizer
 */
final class OrganizerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,              // ULID — id عددی هرگز بیرون نمی‌رود
            'brand_name' => $this->brand_name,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'verification_tier' => $this->verification_tier->value,
            'bio' => $this->bio,
            'logo_url' => $this->logo_url,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

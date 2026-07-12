<?php

declare(strict_types=1);

namespace Modules\Events\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Events\Models\Event;

/**
 * @mixin Event
 */
final class PublicEventResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'cover_url' => $this->cover_url,
            'format' => $this->format->value,
            'format_label' => $this->format->label(),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'is_sold_out' => $this->isSoldOut(),
        ];
    }

}

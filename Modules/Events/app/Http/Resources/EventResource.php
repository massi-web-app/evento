<?php

declare(strict_types=1);

namespace Modules\Events\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'status' => $this->status->value,
            'status_name' => $this->status->name,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'published_at' => $this->published_at?->toIso8601String(),
            'capacity_total' => $this->capacity_total,
            'is_sold_out' => $this->isSoldOut(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

}

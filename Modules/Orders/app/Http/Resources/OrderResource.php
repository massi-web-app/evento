<?php

declare(strict_types=1);

namespace Modules\Orders\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'status' => $this->status->value,
            'status_name' => $this->status->name,
            'subtotal' => $this->subtotal_amount->amount,
            'service_fee' => $this->service_fee_amount->amount,
            'discount' => $this->discount_amount->amount,
            'total' => $this->total_amount->amount,
            'currency' => $this->currency,
            'hold_expires_at' => $this->hold_expires_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'name' => $i->ticket_type_name_snapshot,
                'quantity' => $i->quantity,
                'unit_amount' => $i->unit_amount_snapshot->amount,
                'line_total' => $i->line_total_amount->amount,
            ])),
        ];
    }
}

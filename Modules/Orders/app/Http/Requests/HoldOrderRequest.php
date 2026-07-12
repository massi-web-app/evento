<?php

declare(strict_types=1);

namespace Modules\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class HoldOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'ticket_type_id' => ['required', 'string', Rule::exists('ticket_types', 'public_id')],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function ticketTypePublicId(): string
    {
        return (string) $this->validated('ticket_type_id');
    }

    public function quantity(): int
    {
        return (int) $this->validated('quantity');
    }

}

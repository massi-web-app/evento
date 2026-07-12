<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Events\Enums\EventFormat;

final class ListEventsRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;   // عمومی
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'format' => ['nullable', Rule::enum(EventFormat::class)],
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}

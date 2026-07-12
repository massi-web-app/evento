<?php

declare(strict_types=1);

namespace Modules\Events\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Events\Enums\EventFormat;

final class CreateEventRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->user() !== null;
    }


    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'format' => ['required', Rule::enum(EventFormat::class)],
            'venue_id' => ['nullable', 'integer', Rule::exists('venues', 'id')],
            'city_id' => ['nullable', 'integer', Rule::exists('cities', 'id')],
            'summary' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity_total' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ];
    }


    public function eventFormat(): EventFormat
    {
        return EventFormat::from((int) $this->validated('format'));
    }
    public function starsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse((string) $this->validated('starts_at'));
    }

    public function endsAt(): CarbonImmutable
    {
        return CarbonImmutable::parse((string) $this->validated('ends_at'));
    }

}

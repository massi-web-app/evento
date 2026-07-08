<?php

declare(strict_types=1);

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Identity\Enums\OrganizerType;

final class RegisterOrganizerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && $this->user()->status->canAuthenticate();
    }

    public function rules(): array
    {
        return [
            'brand_name' => ['required', 'string', 'min:2', 'max:150'],
            'type' => ['required', Rule::enum(OrganizerType::class)],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function brandName(): string
    {
        return (string) $this->validated('brand_name');
    }

    public function type(): OrganizerType
    {
        return OrganizerType::from((int) $this->validated('type'));
    }

    public function bio(): ?string
    {
        /** @var string|null */
        return $this->validated('bio');
    }
}

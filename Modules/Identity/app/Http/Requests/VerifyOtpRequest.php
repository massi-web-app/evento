<?php

declare(strict_types=1);

namespace Modules\Identity\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'code' => ['required', 'string', 'digits:6'],
            'device_name' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function phone(): string
    {
        return (string) $this->validated('phone');
    }

    public function code(): string
    {
        return (string) $this->validated('code');
    }

    public function deviceName(): ?string
    {
        /**
         * @var string|null
         */
        return $this->validated('device_name');

    }
}

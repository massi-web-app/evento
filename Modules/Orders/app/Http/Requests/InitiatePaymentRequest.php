<?php

declare(strict_types=1);
namespace Modules\Orders\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];   // ورودی بدنه ندارد — order از مسیر URL می‌آید
    }

}

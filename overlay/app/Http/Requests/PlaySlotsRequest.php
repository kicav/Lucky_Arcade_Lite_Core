<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaySlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'request_id' => ['required', 'uuid'],
            'stake' => ['required', 'integer', 'min:1'],
        ];
    }
}

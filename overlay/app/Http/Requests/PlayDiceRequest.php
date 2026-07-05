<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlayDiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'stake' => ['required', 'integer', 'min:1'],
            'direction' => ['required', Rule::in(['under', 'over'])],
            'target' => ['required', 'integer', 'between:2,98'],
            'request_id' => ['required', 'uuid'],
        ];
    }
}

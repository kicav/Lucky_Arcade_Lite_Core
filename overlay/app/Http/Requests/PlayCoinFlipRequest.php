<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlayCoinFlipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'stake' => ['required', 'integer', 'min:1'],
            'selection' => ['required', Rule::in(['heads', 'tails'])],
            'request_id' => ['required', 'uuid'],
        ];
    }
}

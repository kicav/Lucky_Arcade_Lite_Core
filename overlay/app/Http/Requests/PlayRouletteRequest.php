<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlayRouletteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'stake' => ['required', 'integer', 'min:1'],
            'bet_type' => ['required', Rule::in(['straight', 'color', 'parity', 'range', 'dozen'])],
            'selection' => ['required', 'string', 'max:16'],
            'request_id' => ['required', 'uuid'],
        ];
    }
}

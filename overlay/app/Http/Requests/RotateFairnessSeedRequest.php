<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RotateFairnessSeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'client_seed' => ['nullable', 'string', 'min:8', 'max:64', 'regex:/^[A-Za-z0-9._-]+$/'],
        ];
    }
}

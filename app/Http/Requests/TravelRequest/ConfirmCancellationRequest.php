<?php

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmCancellationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}

<?php

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;

class RequestCancellationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}

<?php

namespace App\Http\Requests\TravelRequest;

use App\Enums\TravelRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterTravelRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::enum(TravelRequestStatus::class)],
            'destination' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'departure_date_start' => ['nullable', 'date'],
            'departure_date_end' => ['nullable', 'date', 'after_or_equal:departure_date_start'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}

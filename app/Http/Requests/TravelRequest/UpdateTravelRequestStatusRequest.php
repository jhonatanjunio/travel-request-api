<?php

namespace App\Http\Requests\TravelRequest;

use App\Enums\TravelRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTravelRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    TravelRequestStatus::Approved->value,
                    TravelRequestStatus::Canceled->value,
                ]),
            ],
        ];
    }

    public function statusEnum(): TravelRequestStatus
    {
        return TravelRequestStatus::from($this->validated('status'));
    }
}

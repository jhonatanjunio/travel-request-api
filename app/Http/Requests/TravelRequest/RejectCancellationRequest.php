<?php

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;

class RejectCancellationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'rejection_reason' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'A razão de rejeição é obrigatória',
            'rejection_reason.max' => 'A razão de rejeição não pode ter mais que 255 caracteres',
            'token.required' => 'O token é obrigatório',
            'token.string' => 'O token deve ser uma string',
        ];
    }
}
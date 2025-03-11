<?php

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTravelRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:approved,rejected,canceled,pending_cancellation',
            'cancellation_reason' => 'required_if:status,canceled|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'O status é obrigatório',
            'status.in' => 'O status deve ser aprovado, rejeitado, cancelado ou pendente de cancelamento',
            'cancellation_reason.required_if' => 'O motivo do cancelamento é obrigatório quando o status é cancelado',
            'cancellation_reason.max' => 'O motivo do cancelamento não pode ter mais que 255 caracteres',
        ];
    }
} 
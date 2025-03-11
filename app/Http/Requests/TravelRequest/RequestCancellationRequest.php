<?php

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RequestCancellationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cancellation_reason' => 'required|string|min:10|max:500',
        ];
    }

    public function messages()
    {
        return [
            'cancellation_reason.required' => 'O motivo do cancelamento é obrigatório',
            'cancellation_reason.min' => 'O motivo do cancelamento deve ter pelo menos 10 caracteres',
            'cancellation_reason.max' => 'O motivo do cancelamento deve ter no máximo 500 caracteres',
        ];
    }
    
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Erro de validação',
            'errors' => $validator->errors()
        ], 422));
    }
} 
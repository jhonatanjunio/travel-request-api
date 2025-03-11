<?php

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateTravelRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination' => ['required', 'string', 'max:255'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination.required' => 'O destino é obrigatório',
            'destination.max' => 'O destino não pode ter mais que 255 caracteres',
            'departure_date.required' => 'A data de partida é obrigatória',
            'departure_date.date' => 'A data de partida deve ser uma data válida',
            'departure_date.after_or_equal' => 'A data de partida deve ser hoje ou uma data futura',
            'return_date.required' => 'A data de retorno é obrigatória',
            'return_date.date' => 'A data de retorno deve ser uma data válida',
            'return_date.after_or_equal' => 'A data de retorno deve ser igual ou posterior à data de partida',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        
        foreach ($this->rules() as $field => $rules) {
            if (in_array('required', $rules) && !$this->has($field)) {
                $errors->add($field, $this->messages()["{$field}.required"] ?? "O campo {$field} é obrigatório");
            }
        }

        throw new HttpResponseException(
            response()->json([
                'message' => 'Os dados fornecidos são inválidos.',
                'errors' => $errors
            ], 422)
        );
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'requester_name' => auth()->user()->name
        ]);

        if ($this->has('departure_date')) {
            $this->merge(['departure_date' => date('Y-m-d', strtotime($this->departure_date))]);
        }
        if ($this->has('return_date')) {
            $this->merge(['return_date' => date('Y-m-d', strtotime($this->return_date))]);
        }
    }

    public function bodyParameters()
    {
        return [
            'destination' => [
                'description' => 'Destino da viagem',
                'example' => 'São Paulo',
            ],
            'departure_date' => [
                'description' => 'Data de partida no formato Y-m-d',
                'example' => '2024-03-15',
            ],
            'return_date' => [
                'description' => 'Data de retorno no formato Y-m-d',
                'example' => '2024-03-20',
            ],
        ];
    }
} 
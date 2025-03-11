<?php

namespace App\Http\Requests\TravelRequest;

use Illuminate\Foundation\Http\FormRequest;

class FilterTravelRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:solicitado,aprovado,cancelado',
            'destination' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'departure_date_start' => 'nullable|date',
            'departure_date_end' => 'nullable|date|after_or_equal:departure_date_start',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'O status deve ser solicitado, aprovado ou cancelado',
            'end_date.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial',
            'departure_date_end.after_or_equal' => 'A data final de partida deve ser igual ou posterior à data inicial de partida',
            'per_page.integer' => 'O número de itens por página deve ser um número inteiro',
            'per_page.min' => 'O número de itens por página deve ser pelo menos 1',
            'per_page.max' => 'O número de itens por página não pode ser maior que 100',
        ];
    }

    public function bodyParameters()
    {
        return [
            'status' => [
                'description' => 'Filtrar por status (solicitado, aprovado, cancelado)',
                'example' => 'aprovado',
            ],
            'destination' => [
                'description' => 'Filtrar por destino',
                'example' => 'São Paulo',
            ],
            'start_date' => [
                'description' => 'Data inicial para filtro (formato Y-m-d)',
                'example' => '2023-01-01',
            ],
            'end_date' => [
                'description' => 'Data final para filtro (formato Y-m-d)',
                'example' => '2023-12-31',
            ],
            'departure_date_start' => [
                'description' => 'Data inicial de partida para filtro (formato Y-m-d)',
                'example' => '2023-01-01',
            ],
            'departure_date_end' => [
                'description' => 'Data final de partida para filtro (formato Y-m-d)',
                'example' => '2023-12-31',
            ],
            'per_page' => [
                'description' => 'Número de itens por página',
                'example' => 15,
            ],
        ];
    }
} 
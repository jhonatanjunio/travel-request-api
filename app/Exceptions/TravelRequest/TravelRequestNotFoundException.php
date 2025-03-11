<?php

namespace App\Exceptions\TravelRequest;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TravelRequestNotFoundException extends Exception
{
    public function __construct(int $id, ?string $message = null)
    {
        $defaultMessage = "Pedido de viagem com ID {$id} não encontrado";
        parent::__construct($message ?? $defaultMessage);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 404);
    }
} 
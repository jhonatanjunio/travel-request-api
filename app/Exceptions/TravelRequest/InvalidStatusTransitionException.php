<?php

namespace App\Exceptions\TravelRequest;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvalidStatusTransitionException extends Exception
{
    public function __construct(string $currentStatus, string $newStatus, ?string $message = null)
    {
        $defaultMessage = "Não é possível alterar o status de '{$currentStatus}' para '{$newStatus}'";
        parent::__construct($message ?? $defaultMessage);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], 422);
    }
} 
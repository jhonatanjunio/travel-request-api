<?php

namespace App\Exceptions\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserRegistrationException extends Exception
{
    protected $errors;

    public function __construct(array $errors, string $message = "Erro no registro de usuário")
    {
        $this->errors = $errors;
        parent::__construct($message);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $this->errors,
        ], 422);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
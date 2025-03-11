<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        // Registrar início da requisição
        $startTime = microtime(true);

        // Processar a requisição
        $response = $next($request);

        // Calcular tempo de execução
        $executionTime = microtime(true) - $startTime;

        // Registrar informações da requisição
        Log::channel('api')->info('API Request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'status' => $response->getStatusCode(),
            'execution_time' => round($executionTime * 1000, 2) . 'ms',
            'request_body' => $this->sanitizeData($request->all()),
        ]);

        return $response;
    }

    /**
     * Sanitizar dados sensíveis
     */
    protected function sanitizeData(array $data): array
    {
        // Remover campos sensíveis como senhas
        if (isset($data['password'])) {
            $data['password'] = '******';
        }

        if (isset($data['password_confirmation'])) {
            $data['password_confirmation'] = '******';
        }

        return $data;
    }
}

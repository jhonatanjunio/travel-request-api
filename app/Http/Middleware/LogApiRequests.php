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
        $startTime = microtime(true);

        $response = $next($request);
        
        $executionTime = microtime(true) - $startTime;

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
        if (isset($data['password'])) {
            $data['password'] = '******';
        }

        if (isset($data['password_confirmation'])) {
            $data['password_confirmation'] = '******';
        }

        return $data;
    }
}

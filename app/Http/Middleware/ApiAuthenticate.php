<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticate
{
    
    public function handle($request, Closure $next, ...$guards)
    {
        
        if (!$request->hasHeader('Accept') || !str_contains($request->header('Accept'), 'application/json')) {
            $request->headers->set('Accept', 'application/json');
        }
        
        if (Auth::check()) {
            return $next($request);
        }
        
        throw new AuthenticationException(
            'Não autenticado.',
            $guards,
            null
        );
    }
} 
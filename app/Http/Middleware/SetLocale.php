<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supported = ['en', 'pt_BR'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->parseLocale($request->header('Accept-Language'));

        app()->setLocale($locale);

        return $next($request);
    }

    protected function parseLocale(?string $header): string
    {
        if (!$header) {
            return config('app.locale', 'en');
        }

        $locales = collect(explode(',', $header))
            ->map(function (string $part) {
                $segments = explode(';', trim($part));
                $locale = str_replace('-', '_', trim($segments[0]));
                $quality = isset($segments[1]) ? (float) str_replace('q=', '', $segments[1]) : 1.0;

                return ['locale' => $locale, 'quality' => $quality];
            })
            ->sortByDesc('quality');

        foreach ($locales as $item) {
            if (in_array($item['locale'], $this->supported)) {
                return $item['locale'];
            }

            $prefix = explode('_', $item['locale'])[0];
            foreach ($this->supported as $supported) {
                if (str_starts_with($supported, $prefix)) {
                    return $supported;
                }
            }
        }

        return config('app.locale', 'en');
    }
}

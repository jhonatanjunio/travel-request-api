<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Interfaces\TravelRequestInterface::class,
            \App\Repositories\TravelRequestRepository::class
        );
        
        $this->app->bind(
            \App\Services\Interfaces\NotificationServiceInterface::class,
            \App\Services\NotificationService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Cache::getDefaultDriver() === 'redis' || Cache::getDefaultDriver() === 'memcached') {
            Cache::setDefaultCacheTime(600); // 10 minutos
        }

        if ($this->app->environment('local')) {
            \URL::forceScheme('http');
        }
    }
}

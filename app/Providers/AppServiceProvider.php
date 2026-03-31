<?php

namespace App\Providers;

use App\Repositories\Interfaces\TravelRequestInterface;
use App\Repositories\TravelRequestRepository;
use App\Services\Interfaces\NotificationServiceInterface;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TravelRequestInterface::class, TravelRequestRepository::class);
        $this->app->bind(NotificationServiceInterface::class, NotificationService::class);
    }

    public function boot(): void
    {
        //
    }
}

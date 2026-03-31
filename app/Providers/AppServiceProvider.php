<?php

namespace App\Providers;

use App\Repositories\Interfaces\TravelRequestInterface;
use App\Repositories\TravelRequestRepository;
use App\Services\Interfaces\NotificationServiceInterface;
use App\Services\NotificationService;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\InfoObject;
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
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->info = new InfoObject('Travel Request API', '1.0.0');
            });
    }
}

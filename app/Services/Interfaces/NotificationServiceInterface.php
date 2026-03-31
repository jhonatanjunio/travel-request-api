<?php

namespace App\Services\Interfaces;

use App\Models\TravelRequest;

interface NotificationServiceInterface
{
    public function notifyStatusChange(TravelRequest $travelRequest): void;
}

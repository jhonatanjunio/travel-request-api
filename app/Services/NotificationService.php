<?php

namespace App\Services;

use App\Events\TravelRequestStatusUpdated;
use App\Models\TravelRequest;
use App\Services\Interfaces\NotificationServiceInterface;

class NotificationService implements NotificationServiceInterface
{
    public function notifyStatusChange(TravelRequest $travelRequest): void
    {
        event(new TravelRequestStatusUpdated($travelRequest));
    }
}

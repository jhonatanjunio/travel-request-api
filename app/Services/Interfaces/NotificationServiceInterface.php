<?php

namespace App\Services\Interfaces;

use App\Models\TravelRequest;

interface NotificationServiceInterface
{
    public function notifyStatusChange(TravelRequest $travelRequest): void;

    public function notifyAdminsCancellationRequest(TravelRequest $travelRequest): void;

    public function notifyCancellationRejected(TravelRequest $travelRequest, string $reason): void;
}

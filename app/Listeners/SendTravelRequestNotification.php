<?php

namespace App\Listeners;

use App\Events\TravelRequestStatusUpdated;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendTravelRequestNotification implements ShouldQueue
{
    public function handle(TravelRequestStatusUpdated $event): void
    {
        $travelRequest = $event->travelRequest;
        $user = $travelRequest->user;

        if (!$user) {
            Log::warning("Could not send notification: user not found (ID: {$travelRequest->user_id})");
            return;
        }

        $user->notify(new TravelRequestStatusChanged($travelRequest));

        Log::info("Status change notification sent to user {$user->name} (ID: {$user->id})");
    }

    public function failed(TravelRequestStatusUpdated $event, \Throwable $exception): void
    {
        Log::error("Failed to send status change notification: {$exception->getMessage()}");
    }
}

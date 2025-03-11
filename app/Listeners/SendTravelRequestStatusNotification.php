<?php

namespace App\Listeners;

use App\Events\TravelRequestStatusChanged;
use App\Notifications\TravelRequestStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTravelRequestStatusNotification implements ShouldQueue
{
    public function handle(TravelRequestStatusChanged $event): void
    {
        $user = $event->travelRequest->user;
        $user->notify(new TravelRequestStatusUpdated($event->travelRequest, $event->oldStatus));
    }
}
<?php

namespace App\Services;

use App\Events\TravelRequestStatusUpdated;
use App\Mail\CancellationRejectedMail;
use App\Mail\CancellationRequestedMail;
use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use App\Services\Interfaces\NotificationServiceInterface;
use Illuminate\Support\Facades\Mail;

class NotificationService implements NotificationServiceInterface
{
    public function notifyStatusChange(TravelRequest $travelRequest): void
    {
        event(new TravelRequestStatusUpdated($travelRequest));
    }

    public function notifyAdminsCancellationRequest(TravelRequest $travelRequest): void
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new CancellationRequestedMail($travelRequest));
        }
    }

    public function notifyCancellationRejected(TravelRequest $travelRequest, string $reason): void
    {
        $user = $travelRequest->user;

        if ($user) {
            Mail::to($user->email)->queue(new CancellationRejectedMail($travelRequest, $reason));
        }
    }
}

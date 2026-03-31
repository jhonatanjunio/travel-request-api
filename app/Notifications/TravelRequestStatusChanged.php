<?php

namespace App\Notifications;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected TravelRequest $travelRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->travelRequest->status->label();

        $message = (new MailMessage)
            ->subject(__('messages.notification_status_subject', ['status' => $statusLabel]))
            ->greeting(__('messages.notification_greeting', ['name' => $notifiable->name]))
            ->line(__('messages.notification_status_line', [
                'destination' => $this->travelRequest->destination,
                'status' => $statusLabel,
            ]))
            ->line(__('messages.notification_departure', [
                'date' => $this->travelRequest->departure_date->format('d/m/Y'),
            ]))
            ->line(__('messages.notification_return', [
                'date' => $this->travelRequest->return_date->format('d/m/Y'),
            ]));

        if ($this->travelRequest->cancellation_reason) {
            $message->line(__('messages.notification_cancellation_reason', [
                'reason' => $this->travelRequest->cancellation_reason,
            ]));
        }

        return $message->line(__('messages.notification_thanks'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'status' => $this->travelRequest->status->value,
            'destination' => $this->travelRequest->destination,
            'departure_date' => $this->travelRequest->departure_date->format('Y-m-d'),
            'return_date' => $this->travelRequest->return_date->format('Y-m-d'),
        ];
    }
}

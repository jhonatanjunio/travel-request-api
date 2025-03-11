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

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected TravelRequest $travelRequest
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->travelRequest->status === 'approved' ? 'aprovada' : 'cancelada';

        $message = (new MailMessage)
            ->subject("Sua solicitação de viagem foi {$status}")
            ->greeting("Olá {$notifiable->name}")
            ->line("Sua solicitação de viagem para {$this->travelRequest->destination} foi {$status}.");

        if ($this->travelRequest->status === 'cancelled') {
            $message->line("Motivo: {$this->travelRequest->cancellation_reason}");
        }

        return $message
            ->line("Data de ida: {$this->travelRequest->departure_date->format('d/m/Y')}")
            ->line("Data de volta: {$this->travelRequest->return_date->format('d/m/Y')}")
            ->action('Ver detalhes', url('/'))
            ->line('Obrigado por usar nosso sistema!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'travel_request_id' => $this->travelRequest->id,
            'status' => $this->travelRequest->status,
            'destination' => $this->travelRequest->destination,
            'departure_date' => $this->travelRequest->departure_date->format('Y-m-d'),
            'return_date' => $this->travelRequest->return_date->format('Y-m-d'),
        ];
    }
}

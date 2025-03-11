<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CancellationRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $travelRequest;
    public $confirmationLink;

    public function __construct(TravelRequest $travelRequest, string $confirmationLink)
    {
        $this->travelRequest = $travelRequest;
        $this->confirmationLink = $confirmationLink;
    }

    public function build()
    {
        return $this->subject('Nova solicitação de cancelamento de viagem')
                    ->view('emails.cancellation-requested');
    }
} 
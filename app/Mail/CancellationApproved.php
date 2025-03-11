<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CancellationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $travelRequest;

    public function __construct(TravelRequest $travelRequest)
    {
        $this->travelRequest = $travelRequest;
    }

    public function build()
    {
        return $this->subject('Cancelamento de viagem aprovado')
            ->view('emails.cancellation-approved');
    }
}

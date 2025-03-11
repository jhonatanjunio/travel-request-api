<?php

namespace App\Mail;

use App\Models\TravelRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CancellationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $travelRequest;
    public $rejectionReason;

    public function __construct(TravelRequest $travelRequest, string $rejectionReason)
    {
        $this->travelRequest = $travelRequest;
        $this->rejectionReason = $rejectionReason;
    }

    public function build()
    {
        return $this->subject('Cancelamento de viagem rejeitado')
            ->view('emails.cancellation-rejected');
    }
}

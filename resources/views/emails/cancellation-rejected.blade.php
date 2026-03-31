<h2>{{ __('messages.mail_cancellation_rejected_subject') }}</h2>

<p>{{ __('messages.mail_cancellation_rejected_body', ['destination' => $travelRequest->destination]) }}</p>

<p><strong>Reason:</strong> {{ $reason }}</p>

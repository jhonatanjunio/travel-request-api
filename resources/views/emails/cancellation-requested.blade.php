<h2>{{ __('messages.mail_cancellation_requested_subject') }}</h2>

<p>{{ __('messages.mail_cancellation_requested_body', ['name' => $travelRequest->user->name]) }}</p>

<ul>
    <li><strong>{{ __('messages.notification_departure', ['date' => $travelRequest->departure_date->format('d/m/Y')]) }}</strong></li>
    <li><strong>{{ __('messages.notification_return', ['date' => $travelRequest->return_date->format('d/m/Y')]) }}</strong></li>
    <li><strong>Destination:</strong> {{ $travelRequest->destination }}</li>
    <li><strong>Reason:</strong> {{ $travelRequest->cancellation_reason }}</li>
</ul>

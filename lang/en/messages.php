<?php

return [
    // Auth
    'login_invalid' => 'Invalid e-mail or password.',
    'logout_success' => 'Successfully logged out.',
    'unauthenticated' => 'Unauthenticated.',

    // Travel Request
    'travel_request_canceled' => 'Travel request canceled successfully.',
    'travel_request_not_found' => 'Travel request with ID :id not found.',
    'cannot_cancel' => 'This request cannot be canceled. Only requests with status "requested" can be canceled.',
    'unauthorized_action' => 'Unauthorized action.',

    // Validation
    'validation_failed' => 'The given data was invalid.',

    // Notifications
    'notification_greeting' => 'Hello :name',
    'notification_status_subject' => 'Your travel request has been :status',
    'notification_status_line' => 'Your travel request to :destination has been :status.',
    'notification_departure' => 'Departure date: :date',
    'notification_return' => 'Return date: :date',
    'notification_cancellation_reason' => 'Cancellation reason: :reason',
    'notification_thanks' => 'Thank you for using our system!',

    // Status labels
    'status_requested' => 'Requested',
    'status_approved' => 'Approved',
    'status_canceled' => 'Canceled',
];

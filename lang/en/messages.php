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
    'cannot_update_canceled' => 'Cannot update a canceled travel request.',

    // Enhanced cancellation
    'cannot_request_cancellation' => 'Cancellation cannot be requested. The request must be approved and the departure date must be more than 2 days away.',
    'cancellation_awaiting_confirmation' => 'Your cancellation request has been initiated. Please confirm using the provided link.',
    'invalid_cancellation_token' => 'Invalid token or the request is not awaiting confirmation.',
    'cancellation_confirmed' => 'Your cancellation request has been confirmed and sent for admin review.',
    'not_pending_cancellation' => 'This request is not pending cancellation.',

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

    // Mail
    'mail_cancellation_requested_subject' => 'New travel cancellation request',
    'mail_cancellation_requested_body' => 'User :name has requested the cancellation of an approved travel request.',
    'mail_cancellation_rejected_subject' => 'Travel cancellation request rejected',
    'mail_cancellation_rejected_body' => 'Your cancellation request for the trip to :destination has been rejected.',

    // Status labels
    'status_requested' => 'Requested',
    'status_approved' => 'Approved',
    'status_canceled' => 'Canceled',
    'status_awaiting_confirmation' => 'Awaiting Confirmation',
    'status_pending_cancellation' => 'Pending Cancellation',
];

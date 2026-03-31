<?php

namespace App\Enums;

enum TravelRequestStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Canceled = 'canceled';
    case AwaitingCancellationConfirmation = 'awaiting_cancellation_confirmation';
    case PendingCancellation = 'pending_cancellation';

    public function label(): string
    {
        return match ($this) {
            self::Requested => __('messages.status_requested'),
            self::Approved => __('messages.status_approved'),
            self::Canceled => __('messages.status_canceled'),
            self::AwaitingCancellationConfirmation => __('messages.status_awaiting_confirmation'),
            self::PendingCancellation => __('messages.status_pending_cancellation'),
        };
    }
}

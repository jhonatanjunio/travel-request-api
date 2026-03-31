<?php

namespace App\Enums;

enum TravelRequestStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Requested => __('messages.status_requested'),
            self::Approved => __('messages.status_approved'),
            self::Canceled => __('messages.status_canceled'),
        };
    }
}

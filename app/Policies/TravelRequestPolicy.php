<?php

namespace App\Policies;

use App\Models\TravelRequest;
use App\Models\User;

class TravelRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TravelRequest $travelRequest): bool
    {
        return $user->id === $travelRequest->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only admins can update status, and the requester cannot update their own request.
     */
    public function updateStatus(User $user, TravelRequest $travelRequest): bool
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->id !== $travelRequest->user_id;
    }

    /**
     * Only the owner can cancel/request cancellation of their own request.
     */
    public function cancel(User $user, TravelRequest $travelRequest): bool
    {
        return $user->id === $travelRequest->user_id;
    }

    /**
     * Only admins can approve/reject cancellation requests.
     */
    public function manageCancellation(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }
}

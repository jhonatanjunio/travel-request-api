<?php

namespace App\Policies;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TravelRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TravelRequest $travelRequest): bool
    {
        return $user->id === $travelRequest->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the status of the model.
     */
    public function updateStatus(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can initiate cancellation of the model.
     */
    public function initiateCancellation(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin() || $user->id == $travelRequest->user_id;
    }

    /**
     * Determine whether the user can confirm cancellation of the model.
     */
    public function confirmCancellation(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin() || $user->id == $travelRequest->user_id;
    }

    /**
     * Determine whether the user can approve cancellation of the model.
     */
    public function approveCancellation(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can reject cancellation of the model.
     */
    public function rejectCancellation(User $user, TravelRequest $travelRequest): bool
    {
        return $user->isAdmin();
    }

} 
<?php

namespace App\Services;

use App\DTOs\TravelRequestDTO;
use App\DTOs\TravelRequestFilterDTO;
use App\Enums\TravelRequestStatus;
use App\Exceptions\UnauthorizedActionException;
use App\Models\TravelRequest;
use App\Repositories\Interfaces\TravelRequestInterface;
use App\Services\Interfaces\NotificationServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;

class TravelRequestService
{
    public function __construct(
        protected TravelRequestInterface $repository,
        protected NotificationServiceInterface $notificationService,
    ) {}

    public function getAllTravelRequests(TravelRequestFilterDTO $filters, int $userId, bool $isAdmin): LengthAwarePaginator
    {
        return $this->repository->getAllWithFilters($filters, $userId, $isAdmin);
    }

    public function createTravelRequest(TravelRequestDTO $dto, int $userId): TravelRequest
    {
        return $this->repository->create([
            'user_id' => $userId,
            'destination' => $dto->destination,
            'departure_date' => $dto->departureDate,
            'return_date' => $dto->returnDate,
            'status' => TravelRequestStatus::Requested,
        ]);
    }

    public function updateTravelRequestStatus(TravelRequest $travelRequest, TravelRequestStatus $status): TravelRequest
    {
        $oldStatus = $travelRequest->status;

        if ($oldStatus === TravelRequestStatus::Canceled) {
            throw new UnauthorizedActionException(__('messages.cannot_update_canceled'));
        }

        $travelRequest = $this->repository->update($travelRequest, [
            'status' => $status,
        ]);

        if ($oldStatus !== $status) {
            $this->notificationService->notifyStatusChange($travelRequest);
        }

        return $travelRequest;
    }

    /**
     * Direct cancellation for requests still in 'requested' status.
     */
    public function cancelTravelRequest(TravelRequest $travelRequest, ?string $reason = null): TravelRequest
    {
        if (!$travelRequest->canCancel()) {
            throw new UnauthorizedActionException(__('messages.cannot_cancel'));
        }

        $travelRequest = $this->repository->update($travelRequest, [
            'status' => TravelRequestStatus::Canceled,
            'cancellation_reason' => $reason,
        ]);

        $this->notificationService->notifyStatusChange($travelRequest);

        return $travelRequest;
    }

    /**
     * Initiate cancellation of an approved request (multi-step flow).
     * Returns confirmation link for the user to confirm.
     */
    public function initiateCancellation(TravelRequest $travelRequest, string $reason): array
    {
        if ($travelRequest->canCancel()) {
            $this->cancelTravelRequest($travelRequest, $reason);
            return ['message' => __('messages.travel_request_canceled')];
        }

        if (!$travelRequest->canRequestCancellation()) {
            throw new UnauthorizedActionException(__('messages.cannot_request_cancellation'));
        }

        $token = $travelRequest->generateCancellationToken();

        $this->repository->update($travelRequest, [
            'status' => TravelRequestStatus::AwaitingCancellationConfirmation,
            'cancellation_reason' => $reason,
            'cancellation_requested_at' => now(),
        ]);

        $confirmationLink = URL::signedRoute('travel-requests.confirm-cancellation', [
            'travelRequest' => $travelRequest->id,
            'token' => $token,
        ]);

        return [
            'message' => __('messages.cancellation_awaiting_confirmation'),
            'confirmation_link' => $confirmationLink,
        ];
    }

    /**
     * Confirm a pending cancellation request (user clicks confirmation link).
     */
    public function confirmCancellation(TravelRequest $travelRequest, string $token): TravelRequest
    {
        if (
            $travelRequest->cancellation_token !== $token ||
            $travelRequest->status !== TravelRequestStatus::AwaitingCancellationConfirmation
        ) {
            throw new UnauthorizedActionException(__('messages.invalid_cancellation_token'));
        }

        $travelRequest = $this->repository->update($travelRequest, [
            'status' => TravelRequestStatus::PendingCancellation,
        ]);

        $this->notificationService->notifyAdminsCancellationRequest($travelRequest);

        return $travelRequest;
    }

    /**
     * Admin approves a pending cancellation.
     */
    public function approveCancellation(TravelRequest $travelRequest): TravelRequest
    {
        if (!$travelRequest->isPendingCancellation()) {
            throw new UnauthorizedActionException(__('messages.not_pending_cancellation'));
        }

        $travelRequest = $this->repository->update($travelRequest, [
            'status' => TravelRequestStatus::Canceled,
            'cancellation_confirmed_at' => now(),
        ]);

        $this->notificationService->notifyStatusChange($travelRequest);

        return $travelRequest;
    }

    /**
     * Admin rejects a pending cancellation (returns to approved).
     */
    public function rejectCancellation(TravelRequest $travelRequest, string $reason): TravelRequest
    {
        if (!$travelRequest->isPendingCancellation()) {
            throw new UnauthorizedActionException(__('messages.not_pending_cancellation'));
        }

        $travelRequest = $this->repository->update($travelRequest, [
            'status' => TravelRequestStatus::Approved,
            'cancellation_reason' => null,
            'cancellation_token' => null,
        ]);

        $this->notificationService->notifyCancellationRejected($travelRequest, $reason);

        return $travelRequest;
    }
}

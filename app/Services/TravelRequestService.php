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

    public function getTravelRequestById(int $id): TravelRequest
    {
        return $this->repository->findById($id);
    }

    public function updateTravelRequestStatus(TravelRequest $travelRequest, TravelRequestStatus $status): TravelRequest
    {
        $oldStatus = $travelRequest->status;

        $travelRequest = $this->repository->update($travelRequest, [
            'status' => $status,
        ]);

        if ($oldStatus !== $status) {
            $this->notificationService->notifyStatusChange($travelRequest);
        }

        return $travelRequest;
    }

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
}

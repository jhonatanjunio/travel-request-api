<?php

namespace App\Http\Controllers\API\v1;

use App\DTOs\TravelRequestDTO;
use App\DTOs\TravelRequestFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\CancelTravelRequestRequest;
use App\Http\Requests\TravelRequest\ConfirmCancellationRequest;
use App\Http\Requests\TravelRequest\CreateTravelRequestRequest;
use App\Http\Requests\TravelRequest\FilterTravelRequestsRequest;
use App\Http\Requests\TravelRequest\RequestCancellationRequest;
use App\Http\Requests\TravelRequest\UpdateTravelRequestStatusRequest;
use App\Http\Resources\TravelRequestCollection;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TravelRequestController extends Controller
{
    public function __construct(
        protected TravelRequestService $travelRequestService,
    ) {}

    public function index(FilterTravelRequestsRequest $request): TravelRequestCollection
    {
        $this->authorize('viewAny', TravelRequest::class);

        $filters = new TravelRequestFilterDTO(
            status: $request->validated('status'),
            destination: $request->validated('destination'),
            startDate: $request->validated('start_date'),
            endDate: $request->validated('end_date'),
            departureDateStart: $request->validated('departure_date_start'),
            departureDateEnd: $request->validated('departure_date_end'),
            perPage: $request->validated('per_page'),
        );

        $travelRequests = $this->travelRequestService->getAllTravelRequests(
            $filters,
            $request->user()->id,
            $request->user()->isAdmin(),
        );

        return new TravelRequestCollection($travelRequests);
    }

    public function store(CreateTravelRequestRequest $request): JsonResponse
    {
        $this->authorize('create', TravelRequest::class);

        $dto = TravelRequestDTO::fromRequest($request->validated());
        $travelRequest = $this->travelRequestService->createTravelRequest($dto, $request->user()->id);

        return (new TravelRequestResource($travelRequest))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(TravelRequest $travelRequest): TravelRequestResource
    {
        $this->authorize('view', $travelRequest);

        return new TravelRequestResource($travelRequest);
    }

    public function updateStatus(UpdateTravelRequestStatusRequest $request, TravelRequest $travelRequest): TravelRequestResource
    {
        $this->authorize('updateStatus', $travelRequest);

        $travelRequest = $this->travelRequestService->updateTravelRequestStatus(
            $travelRequest,
            $request->statusEnum(),
        );

        return new TravelRequestResource($travelRequest);
    }

    public function cancel(CancelTravelRequestRequest $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('cancel', $travelRequest);

        $this->travelRequestService->cancelTravelRequest(
            $travelRequest,
            $request->validated('cancellation_reason'),
        );

        return response()->json([
            'message' => __('messages.travel_request_canceled'),
        ]);
    }

    // ── Enhanced Cancellation Flow ──────────────────────────

    public function requestCancellation(RequestCancellationRequest $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('cancel', $travelRequest);

        $result = $this->travelRequestService->initiateCancellation(
            $travelRequest,
            $request->validated('cancellation_reason'),
        );

        return response()->json($result);
    }

    public function confirmCancellation(ConfirmCancellationRequest $request, TravelRequest $travelRequest): JsonResponse
    {
        $this->authorize('cancel', $travelRequest);

        $this->travelRequestService->confirmCancellation(
            $travelRequest,
            $request->validated('token'),
        );

        return response()->json([
            'message' => __('messages.cancellation_confirmed'),
        ]);
    }

    public function approveCancellation(TravelRequest $travelRequest): TravelRequestResource
    {
        $this->authorize('manageCancellation', $travelRequest);

        $travelRequest = $this->travelRequestService->approveCancellation($travelRequest);

        return new TravelRequestResource($travelRequest);
    }

    public function rejectCancellation(CancelTravelRequestRequest $request, TravelRequest $travelRequest): TravelRequestResource
    {
        $this->authorize('manageCancellation', $travelRequest);

        $travelRequest = $this->travelRequestService->rejectCancellation(
            $travelRequest,
            $request->validated('cancellation_reason') ?? '',
        );

        return new TravelRequestResource($travelRequest);
    }
}

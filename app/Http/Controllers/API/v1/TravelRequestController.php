<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\FilterTravelRequestsRequest;
use App\Http\Requests\TravelRequest\CreateTravelRequestRequest;
use App\Http\Requests\TravelRequest\RequestCancellationRequest;
use App\Http\Requests\TravelRequest\ApproveCancellationRequest;
use App\Http\Requests\TravelRequest\RejectCancellationRequest;
use App\Http\Requests\TravelRequest\ConfirmCancellationRequest;
use App\Http\Requests\TravelRequest\UpdateTravelRequestStatusRequest;
use App\Http\Resources\TravelRequestResource;
use App\Http\Resources\TravelRequestCollection;
use App\Models\TravelRequest;
use App\Services\TravelRequestService;
use Illuminate\Http\JsonResponse;
use App\DTOs\TravelRequestFilterDTO;
use App\DTOs\TravelRequestDTO;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TravelRequestController extends Controller
{
    protected $travelRequestService;

    public function __construct(TravelRequestService $travelRequestService)
    {
        $this->travelRequestService = $travelRequestService;
    }

    /**
     * Listar Pedidos de Viagem
     */
    public function index(FilterTravelRequestsRequest $request): TravelRequestCollection | JsonResponse
    {
        $this->authorize('viewAny', TravelRequest::class);
        
        $filters = new TravelRequestFilterDTO(
            status: $request->status,
            destination: $request->destination,
            startDate: $request->start_date,
            endDate: $request->end_date,
            departureDateStart: $request->departure_date_start,
            departureDateEnd: $request->departure_date_end,
            perPage: $request->per_page
        );

        $travelRequests = $this->travelRequestService->getAllTravelRequests($filters);
        if ($travelRequests->isEmpty()) {
            return response()->json(['message' => 'Nenhum pedido de viagem encontrado'], Response::HTTP_NOT_FOUND);
        }

        return new TravelRequestCollection($travelRequests);
    }

    /**
     * Criar Pedido de Viagem
     */
    public function store(CreateTravelRequestRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        if (empty($validatedData['requester_name'])) {
            $validatedData['requester_name'] = Auth::user()->name;
        }
        $this->authorize('create', TravelRequest::class);
        
        $dto = TravelRequestDTO::fromRequest($validatedData);
        $travelRequest = $this->travelRequestService->createTravelRequest($dto);

        return (new TravelRequestResource($travelRequest))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Exibir Pedido de Viagem
     */
    public function show(int $id): TravelRequestResource
    {
        $travelRequest = $this->travelRequestService->getTravelRequestById($id);
        $this->authorize('view', $travelRequest);

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Atualizar Status do Pedido de Viagem
     */
    public function updateStatus(UpdateTravelRequestStatusRequest $request, $id): JsonResponse
    {
        $travelRequest = $this->travelRequestService->getTravelRequestById($id);
        $this->authorize('updateStatus', $travelRequest);
        $travelRequest = $this->travelRequestService->updateTravelRequestStatus($id, $request->status);
        return response()->json([
            'message' => 'Status do pedido de viagem atualizado com sucesso',
            'travelRequest' => $travelRequest
        ], Response::HTTP_OK);
    }

    /**
     * Iniciar solicitação de cancelamento (primeira etapa)
     */
    public function initiateCancellation(RequestCancellationRequest $request, $id)
    {
        $travelRequest = $this->travelRequestService->getTravelRequestById($id);
        $this->authorize('initiateCancellation', $travelRequest);

        $result = $this->travelRequestService->initiateCancellation(
            $id,
            $request->cancellation_reason
        );
        
        return response()->json($result);
    }
    
    /**
     * Confirmar solicitação de cancelamento (segunda etapa - usuário confirma)
     */
    public function confirmCancellation(ConfirmCancellationRequest $request, $id)
    {
        $travelRequest = $this->travelRequestService->getTravelRequestById($id);
        $this->authorize('confirmCancellation', $travelRequest);

        $result = $this->travelRequestService->confirmCancellation(
            $id,
            $request->token
        );
        
        return response()->json($result);
    }

    /**
     * Listar solicitações pendentes de cancelamento
     */
    public function pendingCancellations()
    {
        $pendingCancellations = $this->travelRequestService->getPendingCancellations();

        return response()->json([
            'data' => $pendingCancellations
        ]);
    }

    /**
     * Exibir Solicitação de Cancelamento
     */
    public function reviewCancellation($id)
    {
        $travelRequest = $this->travelRequestService->reviewCancellation($id);
        $this->authorize('view', $travelRequest);

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Aprovar Cancelamento
     */
    public function approveCancellation(ApproveCancellationRequest $request, $id)
    {
        $travelRequest = $this->travelRequestService->getTravelRequestById($id);
        $this->authorize('approveCancellation', $travelRequest);

        $travelRequest = $this->travelRequestService->approveCancellation(
            $id,
            $request->token
        );
        
        return new TravelRequestResource($travelRequest);
    }

    /**
     * Rejeitar Cancelamento
     */
    public function rejectCancellation(RejectCancellationRequest $request, $id)
    {
        $travelRequest = $this->travelRequestService->getTravelRequestById($id);
        $this->authorize('rejectCancellation', $travelRequest);

        $travelRequest = $this->travelRequestService->rejectCancellation(
            $id,
            $request->token,
            $request->rejection_reason
        );
        
        return new TravelRequestResource($travelRequest);
    }
    
}

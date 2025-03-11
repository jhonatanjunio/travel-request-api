<?php

namespace App\Services;

use App\DTOs\TravelRequestDTO;
use App\DTOs\TravelRequestFilterDTO;
use App\Models\TravelRequest;
use App\Repositories\Interfaces\TravelRequestInterface;
use App\Exceptions\UnauthorizedActionException;
use App\Services\Interfaces\NotificationServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Collection;

class TravelRequestService
{
    public function __construct(
        protected TravelRequestInterface $travelRequestRepository,
        protected NotificationServiceInterface $notificationService
    ) {}

    /**
     * Listar todos os pedidos de viagem com filtros
     */
    public function getAllTravelRequests(TravelRequestFilterDTO $filters): LengthAwarePaginator
    {
        return $this->travelRequestRepository->getAllWithFilters($filters);
    }

    /**
     * Criar Pedido de Viagem
     */
    public function createTravelRequest(TravelRequestDTO $dto): TravelRequest
    {
        $requesterName = $dto->requesterName ?? Auth::user()->name;
        $travelRequest = $this->travelRequestRepository->create([
            'user_id' => Auth::id(),
            'requester_name' => $requesterName,
            'destination' => $dto->destination,
            'departure_date' => $dto->departureDate,
            'return_date' => $dto->returnDate,
            'status' => 'requested'
        ]);

        return $travelRequest;
    }

    /**
     * Exibir Pedido de Viagem
     */
    public function getTravelRequestById(int $id): TravelRequest
    {
        return $this->travelRequestRepository->findById($id);
    }

    /**
     * Atualizar Status do Pedido de Viagem - Exclusivo para Admin
     */
    public function updateTravelRequestStatus(int $id, string $status): TravelRequest
    {
        $travelRequest = $this->travelRequestRepository->findById($id);

        if ($travelRequest->user_id === Auth::id()) {
            throw new UnauthorizedActionException('O usuário que fez o pedido não pode alterar o status do mesmo');
        }

        $oldStatus = $travelRequest->status;
        $travelRequest = $this->travelRequestRepository->update($id, ['status' => $status]);

        if ($oldStatus !== $status) {
            $this->notificationService->sendStatusChangeNotification($travelRequest);
        }

        return $travelRequest;
    }

    /**
     * Iniciar solicitação de cancelamento (primeira etapa)
     */
    public function initiateCancellation(int $id, string $cancellationReason): array
    {
        $travelRequest = $this->travelRequestRepository->findById($id);
        
        if ($travelRequest instanceof TravelRequestNotFoundException) {
            return $travelRequest->toArray();
        }

        if (Auth::id() !== $travelRequest->user_id) {
            throw new UnauthorizedActionException('Você não tem permissão para cancelar esta solicitação');
        }

        if ($travelRequest->isPendingCancellation()) {
            throw new UnauthorizedActionException('Esta solicitação já está aguardando confirmação de cancelamento');
        }

        if (!$travelRequest->canRequestCancellation() && !$travelRequest->canCancelDirectly()) {
            throw new UnauthorizedActionException('Não é possível cancelar o pedido que não esteja aprovado ou que não tenha mais de 2 dias de antecedência da data de partida');
        }

        if ($travelRequest->canCancelDirectly()) {
            $travelRequest = $this->travelRequestRepository->update($id, [
                'status' => TravelRequest::STATUS_CANCELED,
                'cancellation_reason' => $cancellationReason,
                'cancellation_confirmed_at' => now(),
            ]);

            return [
                'message' => "Pedido cancelado com sucesso!"
            ];
        }

        $token = $travelRequest->generateCancellationToken();

        $this->travelRequestRepository->update($id, [
            'status' => TravelRequest::STATUS_AWAITING_CANCELLATION_CONFIRMATION,
            'cancellation_reason' => $cancellationReason,
            'cancellation_requested_at' => now(),
        ]);

        $confirmationLink = URL::signedRoute('travel-requests.confirm-cancellation', [
            'id' => $travelRequest->id,
            'token' => $token
        ]);

        return [
            'message' => 'Você está tentando cancelar um pedido que já está aprovado. Como está no prazo de até 2 dias antes da viagem, você pode solicitar o cancelamento. Para prosseguir, clique no link abaixo.',
            'confirmation_link' => $confirmationLink,
            'token' => $token
        ];
    }

    /**
     * Confirmar solicitação de cancelamento (segunda etapa - usuário confirma)
     */
    public function confirmCancellation(int $id, string $token): array
    {
        $travelRequest = $this->travelRequestRepository->findById($id);

        if (
            $travelRequest->cancellation_token !== $token ||
            $travelRequest->status !== TravelRequest::STATUS_AWAITING_CANCELLATION_CONFIRMATION
        ) {
            throw new UnauthorizedActionException('Token inválido ou solicitação não está aguardando confirmação');
        }

        $travelRequest = $this->travelRequestRepository->update($id, [
            'status' => TravelRequest::STATUS_PENDING_CANCELLATION
        ]);

        $adminReviewLink = URL::signedRoute('admin.travel-requests.cancellation.review', [
            'id' => $travelRequest->id,
            'token' => $token
        ]);

        $this->notificationService->sendCancellationRequestNotification($travelRequest, $adminReviewLink);

        return [
            'message' => 'Sua solicitação de cancelamento foi confirmada e enviada para análise do administrador.'
        ];
    }

    /**
     * Listar solicitações pendentes de cancelamento - Exclusivo para Admin
     */
    public function getPendingCancellations(): Collection
    {
        if (Auth::user()->role !== 'admin') {
            throw new UnauthorizedActionException('Apenas administradores podem ver solicitações pendentes de cancelamento');
        }

        $pendingCancellations = $this->travelRequestRepository->findByStatus(TravelRequest::STATUS_PENDING_CANCELLATION);
        
        foreach ($pendingCancellations as $cancellation) {
            $cancellation->user_cancellation_stats = $this->getUserCancellationStats($cancellation->user_id);
        }

        return $pendingCancellations;
    }

    /**
     * Exibir Solicitação de Cancelamento - Exclusivo para Admin
     */
    public function reviewCancellation(int $id): TravelRequest
    {
        $travelRequest = $this->travelRequestRepository->findById($id);

        if (Auth::user()->role !== 'admin') {
            throw new UnauthorizedActionException('Apenas administradores podem exibir solicitações de cancelamento');
        }

        if (!$travelRequest->isPendingCancellation()) {
            throw new UnauthorizedActionException('Esta solicitação não está pendente de cancelamento');
        }

        $travelRequest->user_cancellation_stats = $this->getUserCancellationStats($travelRequest->user_id);

        return $travelRequest;
    }
    
    /**
     * Aprovar Cancelamento - Exclusivo para Admin
     */
    public function approveCancellation(int $id, string $token): TravelRequest
    {
        $travelRequest = $this->travelRequestRepository->findById($id);
        
        if (Auth::user()->role !== 'admin') {
            throw new UnauthorizedActionException('Apenas administradores podem aprovar cancelamentos');
        }
        
        if ($travelRequest->cancellation_token !== $token || 
            $travelRequest->status !== TravelRequest::STATUS_PENDING_CANCELLATION) {
            throw new UnauthorizedActionException('Token inválido ou solicitação não está pendente de cancelamento');
        }
        
        $travelRequest = $this->travelRequestRepository->update($id, [
            'status' => TravelRequest::STATUS_CANCELED,
            'cancellation_confirmed_at' => now(),
        ]);
        
        $this->notificationService->sendCancellationApprovedNotification($travelRequest);
        
        return $travelRequest;
    }
    
    /**
     * Rejeitar Cancelamento - Exclusivo para Admin
     */
    public function rejectCancellation(int $id, string $token, string $rejectionReason): TravelRequest
    {
        $travelRequest = $this->travelRequestRepository->findById($id);
        
        if (Auth::user()->role !== 'admin') {
            throw new UnauthorizedActionException('Apenas administradores podem rejeitar cancelamentos');
        }
        
        if ($travelRequest->cancellation_token !== $token || 
            $travelRequest->status !== TravelRequest::STATUS_PENDING_CANCELLATION) {
            throw new UnauthorizedActionException('Token inválido ou solicitação não está pendente de cancelamento');
        }
        
        $travelRequest = $this->travelRequestRepository->update($id, [
            'status' => TravelRequest::STATUS_REJECTED,
            'rejection_reason' => $rejectionReason,
            'cancellation_rejected_at' => now(),
        ]);
        
        $this->notificationService->sendCancellationRejectedNotification($travelRequest, $rejectionReason);
        
        return $travelRequest;
    }

    /**
     * Cancelar Pedido Aprovado - Exclusivo para Admin
     */
    public function cancelApprovedRequest(int $id, string $cancellationReason): TravelRequest
    {
        $travelRequest = $this->travelRequestRepository->findById($id);

        if ($travelRequest->status !== 'approved') throw new UnauthorizedActionException('Apenas pedidos aprovados podem ser cancelados por esta operação');

        if (Auth::user()->role !== 'admin') {
            throw new UnauthorizedActionException('Apenas administradores podem cancelar pedidos aprovados');
        }

        if (!$travelRequest->canRequestCancellation()) {
            throw new UnauthorizedActionException('Não é possível cancelar o pedido com menos de 2 dias de antecedência da data de partida');
        }

        $travelRequest = $this->travelRequestRepository->update($id, ['status' => 'canceled', 'cancellation_reason' => $cancellationReason]);

        $this->notificationService->sendStatusChangeNotification($travelRequest);

        return $travelRequest;
    }
    
    /**
     * Estatísticas de Cancelamentos do Usuário - Exclusivo para Admin
     */
    public function getUserCancellationStats(int $userId): array
    {
        return [
            'total_cancellations' => $this->travelRequestRepository->countUserCancellations($userId),
            'pending_cancellations' => $this->travelRequestRepository->countUserPendingCancellations($userId),
        ];
    }
}
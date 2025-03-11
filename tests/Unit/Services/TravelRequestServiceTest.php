<?php

namespace Tests\Unit\Services;

use App\DTOs\TravelRequestDTO;
use App\DTOs\TravelRequestFilterDTO;
use App\Exceptions\UnauthorizedActionException;
use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\Interfaces\TravelRequestInterface;
use App\Services\Interfaces\NotificationServiceInterface;
use App\Services\TravelRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelRequestServiceTest extends TestCase
{
    protected $travelRequestRepository;
    protected $notificationService;
    protected $travelRequestService;
    protected $user;
    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar mocks para as dependências
        $this->travelRequestRepository = Mockery::mock(TravelRequestInterface::class);
        $this->notificationService = Mockery::mock(NotificationServiceInterface::class);
        
        // Instanciar o serviço com os mocks
        $this->travelRequestService = new TravelRequestService(
            $this->travelRequestRepository,
            $this->notificationService
        );

        // Criar usuários para testes
        $this->user = Mockery::mock(User::class);
        $this->user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->user->shouldReceive('getAttribute')->with('name')->andReturn('Usuário Teste');
        $this->user->shouldReceive('getAttribute')->with('role')->andReturn('user');

        $this->adminUser = Mockery::mock(User::class);
        $this->adminUser->shouldReceive('getAttribute')->with('id')->andReturn(2);
        $this->adminUser->shouldReceive('getAttribute')->with('name')->andReturn('Admin Teste');
        $this->adminUser->shouldReceive('getAttribute')->with('role')->andReturn('admin');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_get_all_travel_requests_with_filters()
    {
        // Arrange
        $filters = new TravelRequestFilterDTO();
        $paginatedResults = new LengthAwarePaginator([], 0, 15);
        
        $this->travelRequestRepository
            ->shouldReceive('getAllWithFilters')
            ->once()
            ->with($filters)
            ->andReturn($paginatedResults);

        // Act
        $result = $this->travelRequestService->getAllTravelRequests($filters);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    #[Test]
    public function it_can_create_travel_request()
    {
        // Arrange
        Auth::shouldReceive('id')->andReturn(1);
        Auth::shouldReceive('user')->andReturn($this->user);

        $dto = new TravelRequestDTO(
            requesterName: $this->user->name,
            destination: 'São Paulo',
            departureDate: '2023-12-01',
            returnDate: '2023-12-10'
        );

        $travelRequest = new TravelRequest();
        $travelRequest->id = 1;
        
        $this->travelRequestRepository
            ->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => 1,
                'requester_name' => 'Usuário Teste',
                'destination' => 'São Paulo',
                'departure_date' => '2023-12-01',
                'return_date' => '2023-12-10',
                'status' => 'requested'
            ])
            ->andReturn($travelRequest);

        // Act
        $result = $this->travelRequestService->createTravelRequest($dto);

        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals(1, $result->id);
    }

    #[Test]
    public function it_can_get_travel_request_by_id()
    {
        // Arrange
        $travelRequest = new TravelRequest();
        $travelRequest->id = 1;
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);

        // Act
        $result = $this->travelRequestService->getTravelRequestById(1);

        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals(1, $result->id);
    }

    #[Test]
    public function it_can_update_travel_request_status_as_admin()
    {
        // Arrange
        Auth::shouldReceive('id')->andReturn(2);
        Auth::shouldReceive('user')->andReturn($this->adminUser);

        $travelRequest = new TravelRequest();
        $travelRequest->id = 1;
        $travelRequest->user_id = 1;
        $travelRequest->status = 'requested';
        
        $updatedRequest = clone $travelRequest;
        $updatedRequest->status = 'approved';
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
            
        $this->travelRequestRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, ['status' => 'approved'])
            ->andReturn($updatedRequest);
            
        $this->notificationService
            ->shouldReceive('sendStatusChangeNotification')
            ->once()
            ->with($updatedRequest);

        // Act
        $result = $this->travelRequestService->updateTravelRequestStatus(1, 'approved');

        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals('approved', $result->status);
    }

    #[Test]
    public function it_throws_exception_when_requester_tries_to_update_status()
    {
        // Arrange
        Auth::shouldReceive('id')->andReturn(1);

        $travelRequest = new TravelRequest();
        $travelRequest->id = 1;
        $travelRequest->user_id = 1;
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);

        // Assert & Act
        $this->expectException(UnauthorizedActionException::class);
        $this->expectExceptionMessage('O usuário que fez o pedido não pode alterar o status do mesmo');
        
        $this->travelRequestService->updateTravelRequestStatus(1, 'approved');
    }

    #[Test]
    public function it_can_initiate_direct_cancellation_for_requested_status()
    {
        // Arrange
        Auth::shouldReceive('id')->andReturn(1);

        $travelRequest = Mockery::mock(TravelRequest::class);
        $travelRequest->shouldReceive('getAttribute')->with('user_id')->andReturn(1);
        $travelRequest->shouldReceive('isPendingCancellation')->andReturn(false);
        $travelRequest->shouldReceive('canRequestCancellation')->andReturn(false);
        $travelRequest->shouldReceive('canCancelDirectly')->andReturn(true);
        
        $updatedRequest = Mockery::mock(TravelRequest::class);
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
            
        $this->travelRequestRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, Mockery::on(function($arg) {
                return $arg['status'] === TravelRequest::STATUS_CANCELED &&
                       $arg['cancellation_reason'] === 'Motivo de teste' &&
                       $arg['cancellation_confirmed_at'] instanceof \Carbon\Carbon;
            }))
            ->andReturn($updatedRequest);

        // Act
        $result = $this->travelRequestService->initiateCancellation(1, 'Motivo de teste');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Pedido cancelado com sucesso!', $result['message']);
    }

    #[Test]
    public function it_can_initiate_cancellation_request_for_approved_status()
    {
        // Arrange
        Auth::shouldReceive('id')->andReturn(1);

        $travelRequest = Mockery::mock(TravelRequest::class);
        $travelRequest->shouldReceive('getAttribute')->with('user_id')->andReturn(1);
        $travelRequest->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $travelRequest->shouldReceive('isPendingCancellation')->andReturn(false);
        $travelRequest->shouldReceive('canRequestCancellation')->andReturn(true);
        $travelRequest->shouldReceive('canCancelDirectly')->andReturn(false);
        $travelRequest->shouldReceive('generateCancellationToken')->andReturn('token123');
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
            
        $this->travelRequestRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, Mockery::on(function($arg) {
                return $arg['status'] === TravelRequest::STATUS_AWAITING_CANCELLATION_CONFIRMATION &&
                       $arg['cancellation_reason'] === 'Motivo de teste' &&
                       $arg['cancellation_requested_at'] instanceof \Carbon\Carbon;
            }))
            ->andReturn($travelRequest);
            
        URL::shouldReceive('signedRoute')
            ->once()
            ->with('travel-requests.confirm-cancellation', [
                'id' => 1,
                'token' => 'token123'
            ])
            ->andReturn('http://example.com/confirm-cancellation/1?token=token123');

        // Act
        $result = $this->travelRequestService->initiateCancellation(1, 'Motivo de teste');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('confirmation_link', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('token123', $result['token']);
    }

    #[Test]
    public function it_can_confirm_cancellation()
    {
        // Arrange
        $travelRequest = Mockery::mock(TravelRequest::class);
        $travelRequest->shouldReceive('getAttribute')->with('cancellation_token')->andReturn('token123');
        $travelRequest->shouldReceive('getAttribute')->with('status')->andReturn(TravelRequest::STATUS_AWAITING_CANCELLATION_CONFIRMATION);
        $travelRequest->shouldReceive('getAttribute')->with('id')->andReturn(1);
        
        $updatedRequest = Mockery::mock(TravelRequest::class);
        $updatedRequest->shouldReceive('getAttribute')->with('id')->andReturn(1);
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
            
        $this->travelRequestRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, [
                'status' => TravelRequest::STATUS_PENDING_CANCELLATION
            ])
            ->andReturn($updatedRequest);
            
        URL::shouldReceive('signedRoute')
            ->once()
            ->with('admin.travel-requests.cancellation.review', [
                'id' => 1,
                'token' => 'token123'
            ])
            ->andReturn('http://example.com/admin/review-cancellation/1?token=token123');
            
        $this->notificationService
            ->shouldReceive('sendCancellationRequestNotification')
            ->once()
            ->with($updatedRequest, Mockery::type('string'));

        // Act
        $result = $this->travelRequestService->confirmCancellation(1, 'token123');

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
    }

    #[Test]
    public function it_can_get_pending_cancellations_as_admin()
    {
        // Arrange
        Auth::shouldReceive('user')->andReturn($this->adminUser);

        $pendingCancellations = new Collection([
            (object)['id' => 1, 'user_id' => 3],
            (object)['id' => 2, 'user_id' => 4]
        ]);
        
        $this->travelRequestRepository
            ->shouldReceive('findByStatus')
            ->once()
            ->with(TravelRequest::STATUS_PENDING_CANCELLATION)
            ->andReturn($pendingCancellations);
            
        $this->travelRequestRepository
            ->shouldReceive('countUserCancellations')
            ->twice()
            ->andReturn(2);
        
        $this->travelRequestRepository
            ->shouldReceive('countUserPendingCancellations')
            ->twice()
            ->andReturn(1);

        // Act
        $result = $this->travelRequestService->getPendingCancellations();

        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_throws_exception_when_non_admin_tries_to_get_pending_cancellations()
    {
        // Arrange
        Auth::shouldReceive('user')->andReturn($this->user);

        // Assert & Act
        $this->expectException(UnauthorizedActionException::class);
        $this->expectExceptionMessage('Apenas administradores podem ver solicitações pendentes de cancelamento');
        
        $this->travelRequestService->getPendingCancellations();
    }

    #[Test]
    public function it_can_review_cancellation_as_admin()
    {
        // Arrange
        Auth::shouldReceive('user')->andReturn($this->adminUser);

        $travelRequest = Mockery::mock(TravelRequest::class);
        $travelRequest->shouldReceive('getAttribute')->with('user_id')->andReturn(3);
        $travelRequest->shouldReceive('isPendingCancellation')->andReturn(true);
        $travelRequest->shouldReceive('setAttribute')->with('user_cancellation_stats', Mockery::type('array'))->andReturn($travelRequest);
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
            
        $this->travelRequestRepository
            ->shouldReceive('countUserCancellations')
            ->once()
            ->with(3)
            ->andReturn(2);
        
        $this->travelRequestRepository
            ->shouldReceive('countUserPendingCancellations')
            ->once()
            ->with(3)
            ->andReturn(1);

        // Act
        $result = $this->travelRequestService->reviewCancellation(1);

        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
    }

    #[Test]
    public function it_can_approve_cancellation_as_admin()
    {
        // Arrange
        Auth::shouldReceive('user')->andReturn($this->adminUser);

        $travelRequest = Mockery::mock(TravelRequest::class);
        $travelRequest->shouldReceive('getAttribute')->with('cancellation_token')->andReturn('token123');
        $travelRequest->shouldReceive('getAttribute')->with('status')->andReturn(TravelRequest::STATUS_PENDING_CANCELLATION);
        
        $updatedRequest = Mockery::mock(TravelRequest::class);
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
            
        $this->travelRequestRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, Mockery::on(function($arg) {
                return $arg['status'] === TravelRequest::STATUS_CANCELED &&
                       $arg['cancellation_confirmed_at'] instanceof \Carbon\Carbon;
            }))
            ->andReturn($updatedRequest);
            
        $this->notificationService
            ->shouldReceive('sendCancellationApprovedNotification')
            ->once()
            ->with($updatedRequest);

        // Act
        $result = $this->travelRequestService->approveCancellation(1, 'token123');

        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
    }

    #[Test]
    public function it_can_reject_cancellation_as_admin()
    {
        // Arrange
        Auth::shouldReceive('user')->andReturn($this->adminUser);

        $travelRequest = Mockery::mock(TravelRequest::class);
        $travelRequest->shouldReceive('getAttribute')->with('cancellation_token')->andReturn('token123');
        $travelRequest->shouldReceive('getAttribute')->with('status')->andReturn(TravelRequest::STATUS_PENDING_CANCELLATION);
        
        $updatedRequest = Mockery::mock(TravelRequest::class);
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
            
        $this->travelRequestRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, Mockery::on(function($arg) {
                return $arg['status'] === TravelRequest::STATUS_REJECTED &&
                       $arg['rejection_reason'] === 'Motivo de rejeição' &&
                       $arg['cancellation_rejected_at'] instanceof \Carbon\Carbon;
            }))
            ->andReturn($updatedRequest);
            
        $this->notificationService
            ->shouldReceive('sendCancellationRejectedNotification')
            ->once()
            ->with($updatedRequest, 'Motivo de rejeição');

        // Act
        $result = $this->travelRequestService->rejectCancellation(1, 'token123', 'Motivo de rejeição');

        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
    }

    #[Test]
    public function it_can_cancel_approved_request_as_admin()
    {
        // Arrange
        Auth::shouldReceive('user')->andReturn($this->adminUser);

        $travelRequest = Mockery::mock(TravelRequest::class);
        $travelRequest->shouldReceive('getAttribute')->with('status')->andReturn('approved');
        $travelRequest->shouldReceive('canRequestCancellation')->andReturn(true);
        
        $updatedRequest = Mockery::mock(TravelRequest::class);
        $updatedRequest->shouldReceive('getAttribute')->with('status')->andReturn('canceled');
        
        $this->travelRequestRepository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
            
        $this->travelRequestRepository
            ->shouldReceive('update')
            ->once()
            ->with(1, [
                'status' => 'canceled', 
                'cancellation_reason' => 'Motivo administrativo'
            ])
            ->andReturn($updatedRequest);
            
        $this->notificationService
            ->shouldReceive('sendStatusChangeNotification')
            ->once()
            ->with($updatedRequest);

        // Act
        $result = $this->travelRequestService->cancelApprovedRequest(1, 'Motivo administrativo');

        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
    }

    #[Test]
    public function it_can_get_user_cancellation_stats()
    {
        // Arrange
        $this->travelRequestRepository
            ->shouldReceive('countUserCancellations')
            ->once()
            ->with(1)
            ->andReturn(5);
            
        $this->travelRequestRepository
            ->shouldReceive('countUserPendingCancellations')
            ->once()
            ->with(1)
            ->andReturn(2);

        // Act
        $result = $this->travelRequestService->getUserCancellationStats(1);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_cancellations', $result);
        $this->assertArrayHasKey('pending_cancellations', $result);
        $this->assertEquals(5, $result['total_cancellations']);
        $this->assertEquals(2, $result['pending_cancellations']);
    }
}
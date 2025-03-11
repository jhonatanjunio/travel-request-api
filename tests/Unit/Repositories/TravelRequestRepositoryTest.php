<?php

namespace Tests\Unit\Repositories;

use App\DTOs\TravelRequestFilterDTO;
use App\Models\TravelRequest;
use App\Models\User;
use App\Repositories\Interfaces\TravelRequestInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelRequestRepositoryTest extends TestCase
{
    protected $repository;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        if (DB::connection()->getDatabaseName() !== ':memory:') {
            $currentDatabase = DB::connection()->getDatabaseName();
            if (strpos($currentDatabase, 'testing') === false && strpos($currentDatabase, 'test') === false) {
                $this->markTestSkipped('ATENÇÃO: Testes não executados para proteger o banco de dados de produção/desenvolvimento!');
                return;
            }
        }
        
        $this->user = Mockery::mock(User::class);
        $this->user->shouldReceive('isAdmin')->andReturn(false);
        $this->user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        
        Auth::shouldReceive('id')->andReturn(1);
        Auth::shouldReceive('user')->andReturn($this->user);
        
        $this->repository = Mockery::mock(TravelRequestInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_get_all_with_filters()
    {
        // Arrange
        $filters = new TravelRequestFilterDTO(
            status: 'approved',
            destination: 'São Paulo',
            startDate: '2023-01-01',
            endDate: '2023-12-31'
        );
        
        $paginatedResults = new LengthAwarePaginator([], 0, 15);
        
        $this->repository
            ->shouldReceive('getAllWithFilters')
            ->once()
            ->with(Mockery::type(TravelRequestFilterDTO::class))
            ->andReturn($paginatedResults);
        
        // Act
        $result = $this->repository->getAllWithFilters($filters);
        
        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    #[Test]
    public function it_can_find_by_id()
    {
        // Arrange
        $travelRequest = new TravelRequest();
        $travelRequest->id = 1;
        
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequest);
        
        // Act
        $result = $this->repository->findById(1);
        
        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals(1, $result->id);
    }

    #[Test]
    public function it_can_create_travel_request()
    {
        // Arrange
        $data = [
            'user_id' => 1,
            'requester_name' => 'Usuário Teste',
            'destination' => 'São Paulo',
            'departure_date' => '2023-12-01',
            'return_date' => '2023-12-10',
            'status' => 'requested'
        ];
        
        $travelRequest = new TravelRequest();
        $travelRequest->id = 1;
        
        $this->repository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($travelRequest);
        
        // Act
        $result = $this->repository->create($data);
        
        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals(1, $result->id);
    }

    #[Test]
    public function it_can_update_travel_request()
    {
        // Arrange
        $id = 1;
        $data = ['status' => 'approved'];
        
        $travelRequest = new TravelRequest();
        $travelRequest->id = 1;
        $travelRequest->status = 'approved';
        
        $this->repository
            ->shouldReceive('update')
            ->once()
            ->with($id, $data)
            ->andReturn($travelRequest);
        
        // Act
        $result = $this->repository->update($id, $data);
        
        // Assert
        $this->assertInstanceOf(TravelRequest::class, $result);
        $this->assertEquals('approved', $result->status);
    }

    #[Test]
    public function it_can_find_by_status()
    {
        // Arrange
        $status = 'pending_cancellation';
        $collection = collect([
            (object)['id' => 1, 'status' => $status],
            (object)['id' => 2, 'status' => $status]
        ]);
        
        $this->repository
            ->shouldReceive('findByStatus')
            ->once()
            ->with($status)
            ->andReturn($collection);
        
        // Act
        $result = $this->repository->findByStatus($status);
        
        // Assert
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_can_count_user_cancellations()
    {
        // Arrange
        $userId = 1;
        
        $this->repository
            ->shouldReceive('countUserCancellations')
            ->once()
            ->with($userId)
            ->andReturn(5);
        
        // Act
        $result = $this->repository->countUserCancellations($userId);
        
        // Assert
        $this->assertEquals(5, $result);
    }

    #[Test]
    public function it_can_count_user_pending_cancellations()
    {
        // Arrange
        $userId = 1;
        
        $this->repository
            ->shouldReceive('countUserPendingCancellations')
            ->once()
            ->with($userId)
            ->andReturn(2);
        
        // Act
        $result = $this->repository->countUserPendingCancellations($userId);
        
        // Assert
        $this->assertEquals(2, $result);
    }
} 
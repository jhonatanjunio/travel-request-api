<?php

namespace Tests\Feature\Api;

use App\Enums\TravelRequestStatus;
use App\Events\TravelRequestStatusUpdated;
use App\Models\TravelRequest;
use App\Models\User;
use App\Notifications\TravelRequestStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TravelRequestStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private string $userToken;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->userToken = auth('api')->login($this->user);
        $this->adminToken = auth('api')->login($this->admin);
    }

    protected function authHeader(string $token): array
    {
        return ['Authorization' => "Bearer {$token}"];
    }

    // ── ADMIN UPDATE STATUS ─────────────────────────────────

    public function test_admin_can_approve_request(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);

        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader($this->adminToken))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", [
                'status' => 'approved',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved',
        ]);

        Event::assertDispatched(TravelRequestStatusUpdated::class);
    }

    public function test_admin_can_cancel_request(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);

        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader($this->adminToken))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", [
                'status' => 'canceled',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'canceled');

        Event::assertDispatched(TravelRequestStatusUpdated::class);
    }

    public function test_non_admin_cannot_update_status(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $otherUser = User::factory()->create();
        $otherToken = auth('api')->login($otherUser);

        $response = $this->withHeaders($this->authHeader($otherToken))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", [
                'status' => 'approved',
            ]);

        $response->assertStatus(403);
    }

    public function test_requester_cannot_update_own_request_status(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->withHeaders($this->authHeader($this->adminToken))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", [
                'status' => 'approved',
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_set_invalid_status(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader($this->adminToken))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ── USER CANCEL ─────────────────────────────────────────

    public function test_user_can_cancel_own_requested_order(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);

        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader($this->userToken))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/cancel", [
                'cancellation_reason' => 'Changed my plans',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'canceled',
            'cancellation_reason' => 'Changed my plans',
        ]);

        Event::assertDispatched(TravelRequestStatusUpdated::class);
    }

    public function test_user_cannot_cancel_approved_order(): void
    {
        $travelRequest = TravelRequest::factory()->approved()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader($this->userToken))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/cancel", [
                'cancellation_reason' => 'Want to cancel',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_user_cannot_cancel_already_canceled_order(): void
    {
        $travelRequest = TravelRequest::factory()->canceled()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader($this->userToken))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_user_cannot_cancel_other_users_order(): void
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authHeader($this->userToken))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(403);
    }

    // ── EVENT / NOTIFICATION ────────────────────────────────

    public function test_event_dispatched_on_status_change(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);

        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $this->withHeaders($this->authHeader($this->adminToken))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", [
                'status' => 'approved',
            ]);

        Event::assertDispatched(TravelRequestStatusUpdated::class, function ($event) use ($travelRequest) {
            return $event->travelRequest->id === $travelRequest->id;
        });
    }

    public function test_no_event_when_status_unchanged(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);

        $travelRequest = TravelRequest::factory()->approved()->create(['user_id' => $this->user->id]);

        $this->withHeaders($this->authHeader($this->adminToken))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", [
                'status' => 'approved',
            ]);

        Event::assertNotDispatched(TravelRequestStatusUpdated::class);
    }

    // ── I18N ────────────────────────────────────────────────

    public function test_api_responds_in_english_by_default(): void
    {
        $response = $this->getJson('/api/v1/travel-requests');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_api_responds_in_portuguese_when_requested(): void
    {
        $response = $this->withHeader('Accept-Language', 'pt-BR')
            ->getJson('/api/v1/travel-requests');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Não autenticado.']);
    }
}

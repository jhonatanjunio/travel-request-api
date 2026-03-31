<?php

namespace Tests\Feature\Api;

use App\Enums\TravelRequestStatus;
use App\Events\TravelRequestStatusUpdated;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TravelRequestStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    protected function authAs(User $user): array
    {
        return ['Authorization' => 'Bearer ' . auth('api')->login($user)];
    }

    // ── ADMIN UPDATE STATUS ─────────────────────────────────

    public function test_admin_can_approve_request(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", ['status' => 'approved']);

        $response->assertOk()->assertJsonPath('data.status', 'approved');
        $this->assertDatabaseHas('travel_requests', ['id' => $travelRequest->id, 'status' => 'approved']);
        Event::assertDispatched(TravelRequestStatusUpdated::class);
    }

    public function test_admin_can_cancel_request(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", ['status' => 'canceled']);

        $response->assertOk()->assertJsonPath('data.status', 'canceled');
        Event::assertDispatched(TravelRequestStatusUpdated::class);
    }

    public function test_non_admin_cannot_update_status(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);
        $otherUser = User::factory()->create();

        $response = $this->withHeaders($this->authAs($otherUser))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", ['status' => 'approved']);

        $response->assertStatus(403);
    }

    public function test_requester_cannot_update_own_request_status(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", ['status' => 'approved']);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_set_invalid_status(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", ['status' => 'invalid']);

        $response->assertStatus(422)->assertJsonValidationErrors(['status']);
    }

    public function test_admin_cannot_update_canceled_request(): void
    {
        $travelRequest = TravelRequest::factory()->canceled()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", ['status' => 'approved']);

        $response->assertStatus(403);
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'canceled',
        ]);
    }

    // ── USER CANCEL ─────────────────────────────────────────

    public function test_user_can_cancel_own_requested_order(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/cancel", [
                'cancellation_reason' => 'Changed my plans',
            ]);

        $response->assertOk()->assertJsonStructure(['message']);
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

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(403);
        $this->assertDatabaseHas('travel_requests', ['id' => $travelRequest->id, 'status' => 'approved']);
    }

    public function test_user_cannot_cancel_already_canceled_order(): void
    {
        $travelRequest = TravelRequest::factory()->canceled()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(403);
    }

    public function test_user_cannot_cancel_other_users_order(): void
    {
        $otherUser = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(403);
    }

    // ── EVENT ───────────────────────────────────────────────

    public function test_event_dispatched_on_status_change(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $this->withHeaders($this->authAs($this->admin))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", ['status' => 'approved']);

        Event::assertDispatched(TravelRequestStatusUpdated::class, fn ($e) => $e->travelRequest->id === $travelRequest->id);
    }

    public function test_no_event_when_status_unchanged(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);
        $travelRequest = TravelRequest::factory()->approved()->create(['user_id' => $this->user->id]);

        $this->withHeaders($this->authAs($this->admin))
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}", ['status' => 'approved']);

        Event::assertNotDispatched(TravelRequestStatusUpdated::class);
    }

}

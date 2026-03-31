<?php

namespace Tests\Feature\Api;

use App\Enums\TravelRequestStatus;
use App\Events\TravelRequestStatusUpdated;
use App\Mail\CancellationRejectedMail;
use App\Mail\CancellationRequestedMail;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EnhancedCancellationTest extends TestCase
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

    // ── INITIATE CANCELLATION ───────────────────────────────

    public function test_user_can_directly_cancel_requested_order_via_request_cancellation(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/request-cancellation", [
                'cancellation_reason' => 'Changed my travel plans entirely',
            ]);

        $response->assertOk()->assertJsonStructure(['message']);
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'canceled',
        ]);
    }

    public function test_user_can_initiate_cancellation_of_approved_order(): void
    {
        $travelRequest = TravelRequest::factory()->approved()->create([
            'user_id' => $this->user->id,
            'departure_date' => now()->addWeeks(2),
            'return_date' => now()->addWeeks(3),
        ]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/request-cancellation", [
                'cancellation_reason' => 'Meeting was rescheduled',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'confirmation_link']);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'awaiting_cancellation_confirmation',
            'cancellation_reason' => 'Meeting was rescheduled',
        ]);
    }

    public function test_user_cannot_cancel_approved_order_within_2_days_of_departure(): void
    {
        $travelRequest = TravelRequest::factory()->approved()->create([
            'user_id' => $this->user->id,
            'departure_date' => now()->addDay(),
            'return_date' => now()->addDays(5),
        ]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/request-cancellation", [
                'cancellation_reason' => 'Too late to cancel',
            ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_cancellation_reason_is_required(): void
    {
        $travelRequest = TravelRequest::factory()->approved()->create([
            'user_id' => $this->user->id,
            'departure_date' => now()->addWeeks(2),
            'return_date' => now()->addWeeks(3),
        ]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/request-cancellation", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cancellation_reason']);
    }

    // ── CONFIRM CANCELLATION ────────────────────────────────

    public function test_user_can_confirm_cancellation_with_valid_token(): void
    {
        Mail::fake();

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => TravelRequestStatus::AwaitingCancellationConfirmation,
            'cancellation_reason' => 'Test reason',
            'cancellation_token' => 'valid-test-token',
        ]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/confirm-cancellation", [
                'token' => 'valid-test-token',
            ]);

        $response->assertOk()->assertJsonStructure(['message']);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'pending_cancellation',
        ]);

        Mail::assertQueued(CancellationRequestedMail::class);
    }

    public function test_user_cannot_confirm_with_invalid_token(): void
    {
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'status' => TravelRequestStatus::AwaitingCancellationConfirmation,
            'cancellation_token' => 'real-token',
        ]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/travel-requests/{$travelRequest->id}/confirm-cancellation", [
                'token' => 'wrong-token',
            ]);

        $response->assertStatus(403);
    }

    // ── ADMIN APPROVE/REJECT ────────────────────────────────

    public function test_admin_can_approve_pending_cancellation(): void
    {
        Event::fake([TravelRequestStatusUpdated::class]);

        $travelRequest = TravelRequest::factory()->pendingCancellation()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->postJson("/api/v1/admin/travel-requests/{$travelRequest->id}/approve-cancellation");

        $response->assertOk()->assertJsonPath('data.status', 'canceled');

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'canceled',
        ]);

        Event::assertDispatched(TravelRequestStatusUpdated::class);
    }

    public function test_admin_can_reject_pending_cancellation(): void
    {
        Mail::fake();

        $travelRequest = TravelRequest::factory()->pendingCancellation()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->postJson("/api/v1/admin/travel-requests/{$travelRequest->id}/reject-cancellation", [
                'cancellation_reason' => 'Business trip is mandatory',
            ]);

        $response->assertOk()->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'approved',
        ]);

        Mail::assertQueued(CancellationRejectedMail::class);
    }

    public function test_non_admin_cannot_approve_cancellation(): void
    {
        $travelRequest = TravelRequest::factory()->pendingCancellation()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson("/api/v1/admin/travel-requests/{$travelRequest->id}/approve-cancellation");

        $response->assertStatus(403);
    }

    public function test_admin_cannot_approve_non_pending_request(): void
    {
        $travelRequest = TravelRequest::factory()->approved()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->postJson("/api/v1/admin/travel-requests/{$travelRequest->id}/approve-cancellation");

        $response->assertStatus(403);
    }
}

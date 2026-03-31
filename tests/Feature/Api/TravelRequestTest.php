<?php

namespace Tests\Feature\Api;

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestTest extends TestCase
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

    // ── CREATE ──────────────────────────────────────────────

    public function test_user_can_create_travel_request(): void
    {
        $payload = [
            'destination' => 'São Paulo',
            'departure_date' => now()->addWeek()->format('Y-m-d'),
            'return_date' => now()->addWeeks(2)->format('Y-m-d'),
        ];

        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson('/api/v1/travel-requests', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'user_id', 'requester_name', 'destination', 'departure_date', 'return_date', 'status'],
            ])
            ->assertJsonPath('data.destination', 'São Paulo')
            ->assertJsonPath('data.status', 'requested');

        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $this->user->id,
            'destination' => 'São Paulo',
            'status' => 'requested',
        ]);
    }

    public function test_user_cannot_create_with_departure_in_past(): void
    {
        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson('/api/v1/travel-requests', [
                'destination' => 'Rio de Janeiro',
                'departure_date' => now()->subDay()->format('Y-m-d'),
                'return_date' => now()->addWeek()->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['departure_date']);
    }

    public function test_user_cannot_create_with_return_before_departure(): void
    {
        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson('/api/v1/travel-requests', [
                'destination' => 'Rio de Janeiro',
                'departure_date' => now()->addWeeks(2)->format('Y-m-d'),
                'return_date' => now()->addWeek()->format('Y-m-d'),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['return_date']);
    }

    public function test_user_cannot_create_with_missing_fields(): void
    {
        $response = $this->withHeaders($this->authAs($this->user))
            ->postJson('/api/v1/travel-requests', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination', 'departure_date', 'return_date']);
    }

    // ── LIST ────────────────────────────────────────────────

    public function test_user_can_list_own_travel_requests(): void
    {
        TravelRequest::factory()->count(3)->create(['user_id' => $this->user->id]);
        TravelRequest::factory()->count(2)->create(); // other user

        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson('/api/v1/travel-requests');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_list_all_travel_requests(): void
    {
        TravelRequest::factory()->count(3)->create(['user_id' => $this->user->id]);
        TravelRequest::factory()->count(2)->create();

        $response = $this->withHeaders($this->authAs($this->admin))
            ->getJson('/api/v1/travel-requests');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_empty_list_returns_200(): void
    {
        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson('/api/v1/travel-requests');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_filter_by_status(): void
    {
        TravelRequest::factory()->count(2)->create(['user_id' => $this->user->id]);
        TravelRequest::factory()->approved()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson('/api/v1/travel-requests?status=approved');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'approved');
    }

    public function test_filter_by_destination(): void
    {
        TravelRequest::factory()->create(['user_id' => $this->user->id, 'destination' => 'São Paulo']);
        TravelRequest::factory()->create(['user_id' => $this->user->id, 'destination' => 'Rio de Janeiro']);

        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson('/api/v1/travel-requests?destination=Paulo');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_filter_by_departure_date_range(): void
    {
        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => now()->addDays(5),
            'return_date' => now()->addDays(10),
        ]);
        TravelRequest::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => now()->addDays(30),
            'return_date' => now()->addDays(35),
        ]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson('/api/v1/travel-requests?' . http_build_query([
                'departure_date_start' => now()->addDays(1)->format('Y-m-d'),
                'departure_date_end' => now()->addDays(10)->format('Y-m-d'),
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_pagination_works(): void
    {
        TravelRequest::factory()->count(20)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson('/api/v1/travel-requests?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    // ── SHOW ────────────────────────────────────────────────

    public function test_user_can_view_own_request_detail(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson("/api/v1/travel-requests/{$travelRequest->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $travelRequest->id);
    }

    public function test_user_cannot_view_other_users_request(): void
    {
        $otherUser = User::factory()->create();
        $otherRequest = TravelRequest::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson("/api/v1/travel-requests/{$otherRequest->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_request(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->admin))
            ->getJson("/api/v1/travel-requests/{$travelRequest->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $travelRequest->id);
    }

    public function test_show_returns_404_for_nonexistent_request(): void
    {
        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson('/api/v1/travel-requests/99999');

        $response->assertStatus(404);
    }

    public function test_response_does_not_expose_sensitive_fields(): void
    {
        $travelRequest = TravelRequest::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authAs($this->user))
            ->getJson("/api/v1/travel-requests/{$travelRequest->id}");

        $response->assertOk();

        $data = $response->json('data');
        $this->assertArrayNotHasKey('deleted_at', $data);
        $this->assertArrayNotHasKey('password', $data);
    }
}

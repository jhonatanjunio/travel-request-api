<?php

namespace Tests\Unit\Models;

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_cancel_returns_true_for_requested_status(): void
    {
        $travelRequest = TravelRequest::factory()->create();

        $this->assertTrue($travelRequest->canCancel());
    }

    public function test_can_cancel_returns_false_for_approved_status(): void
    {
        $travelRequest = TravelRequest::factory()->approved()->create();

        $this->assertFalse($travelRequest->canCancel());
    }

    public function test_can_cancel_returns_false_for_canceled_status(): void
    {
        $travelRequest = TravelRequest::factory()->canceled()->create();

        $this->assertFalse($travelRequest->canCancel());
    }

    public function test_requester_name_accessor_returns_user_name(): void
    {
        $user = User::factory()->create(['name' => 'João Silva']);
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('João Silva', $travelRequest->requester_name);
    }

    public function test_user_relationship(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $travelRequest->user);
        $this->assertEquals($user->id, $travelRequest->user->id);
    }

    public function test_status_is_cast_to_enum(): void
    {
        $travelRequest = TravelRequest::factory()->create();

        $this->assertInstanceOf(TravelRequestStatus::class, $travelRequest->status);
        $this->assertEquals(TravelRequestStatus::Requested, $travelRequest->status);
    }

    public function test_dates_are_cast_to_carbon(): void
    {
        $travelRequest = TravelRequest::factory()->create();

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $travelRequest->departure_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $travelRequest->return_date);
    }
}

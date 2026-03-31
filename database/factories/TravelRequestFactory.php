<?php

namespace Database\Factories;

use App\Enums\TravelRequestStatus;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelRequestFactory extends Factory
{
    protected $model = TravelRequest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'destination' => fake()->city(),
            'departure_date' => fake()->dateTimeBetween('+1 week', '+2 weeks'),
            'return_date' => fake()->dateTimeBetween('+3 weeks', '+4 weeks'),
            'status' => TravelRequestStatus::Requested,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequestStatus::Approved,
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequestStatus::Canceled,
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}

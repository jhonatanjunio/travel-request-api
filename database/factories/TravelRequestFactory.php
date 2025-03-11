<?php

namespace Database\Factories;

use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelRequestFactory extends Factory
{
    protected $model = TravelRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'destination' => $this->faker->city,
            'departure_date' => $this->faker->dateTimeBetween('+1 week', '+2 weeks'),
            'return_date' => $this->faker->dateTimeBetween('+3 weeks', '+4 weeks'),
            'status' => 'requested',
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
            ];
        });
    }

    public function pendingCancellation()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending_cancellation',
                'cancellation_reason' => $this->faker->sentence,
                'cancellation_requested_at' => now(),
            ];
        });
    }
} 
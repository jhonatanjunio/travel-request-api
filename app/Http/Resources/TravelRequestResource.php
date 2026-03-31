<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'requester_name' => $this->requester_name,
            'destination' => $this->destination,
            'departure_date' => $this->departure_date?->format('Y-m-d'),
            'return_date' => $this->return_date?->format('Y-m-d'),
            'status' => $this->status->value,
            'cancellation_reason' => $this->cancellation_reason,
            'cancellation_requested_at' => $this->cancellation_requested_at?->format('Y-m-d H:i:s'),
            'cancellation_confirmed_at' => $this->cancellation_confirmed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

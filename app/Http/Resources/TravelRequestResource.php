<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TravelRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'requester_name' => $this->requester_name,
            'destination' => $this->destination,
            'departure_date' => !empty($this->departure_date) ? Carbon::parse($this->departure_date)->format('Y-m-d') : null,
            'return_date' => !empty($this->return_date) ? Carbon::parse($this->return_date)->format('Y-m-d') : null,
            'status' => $this->status,
            'cancellation_reason' => !empty($this->cancellation_reason) ? $this->cancellation_reason : null,
            'rejection_reason' => !empty($this->rejection_reason) ? $this->rejection_reason : null,
            'cancellation_requested_at' => !empty($this->cancellation_requested_at) ? Carbon::parse($this->cancellation_requested_at)->format('Y-m-d H:i:s') : null,
            'cancellation_confirmed_at' => !empty($this->cancellation_confirmed_at) ? Carbon::parse($this->cancellation_confirmed_at)->format('Y-m-d H:i:s') : null,
            'cancellation_rejected_at' => !empty($this->cancellation_rejected_at) ? Carbon::parse($this->cancellation_rejected_at)->format('Y-m-d H:i:s') : null,
            'cancellation_token' => !empty($this->cancellation_token) ? $this->cancellation_token : null,
            'created_at' => !empty($this->created_at) ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => !empty($this->updated_at) ? $this->updated_at->format('Y-m-d H:i:s') : null,
        ];
                
        if (isset($this->user_cancellation_stats)) {
            $data['user_cancellation_stats'] = $this->user_cancellation_stats;
        }
        
        return $data;
    }
}
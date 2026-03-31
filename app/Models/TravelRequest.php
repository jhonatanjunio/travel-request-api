<?php

namespace App\Models;

use App\Enums\TravelRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TravelRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'destination',
        'departure_date',
        'return_date',
        'status',
        'cancellation_reason',
        'cancellation_token',
        'cancellation_requested_at',
        'cancellation_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'status' => TravelRequestStatus::class,
            'cancellation_requested_at' => 'datetime',
            'cancellation_confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRequesterNameAttribute(): string
    {
        return $this->user->name;
    }

    /**
     * Can be directly canceled by the owner (status is still 'requested').
     */
    public function canCancel(): bool
    {
        return $this->status === TravelRequestStatus::Requested;
    }

    /**
     * Can request cancellation of an approved request (>2 days before departure).
     */
    public function canRequestCancellation(): bool
    {
        if ($this->status !== TravelRequestStatus::Approved) {
            return false;
        }

        return $this->departure_date->isFuture()
            && now()->diffInDays($this->departure_date) > 2;
    }

    public function isPendingCancellation(): bool
    {
        return $this->status === TravelRequestStatus::PendingCancellation;
    }

    public function generateCancellationToken(): string
    {
        $this->cancellation_token = Str::random(64);
        $this->save();

        return $this->cancellation_token;
    }
}

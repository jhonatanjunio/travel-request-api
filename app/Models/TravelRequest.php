<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'rejection_reason',
        'cancellation_requested_at',
        'cancellation_token',
        'cancellation_confirmed_at',
        'cancellation_rejected_at',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'cancellation_requested_at' => 'datetime',
        'cancellation_confirmed_at' => 'datetime',
        'cancellation_rejected_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'requested';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PENDING_CANCELLATION = 'pending_cancellation';
    public const STATUS_AWAITING_CANCELLATION_CONFIRMATION = 'awaiting_cancellation_confirmation';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRequesterNameAttribute()
    {
        return $this->user->name;
    }

    public function canCancelDirectly()
    {
        return $this->status === self::STATUS_PENDING && $this->departure_date->isFuture();
    }

    public function canShowAsOwner()
    {
        return $this->user_id === Auth::id();
    }

    public function canRequestCancellation()
    {        
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        $departureDate = \Carbon\Carbon::parse($this->departure_date);
        $now = \Carbon\Carbon::now();

        return !$departureDate->isPast() && $now->diffInDays($departureDate) > 2;
    }

    public function isPendingCancellation()
    {
        return $this->status === self::STATUS_PENDING_CANCELLATION;
    }

    public function generateCancellationToken()
    {
        $this->cancellation_token = md5($this->id . time() . uniqid());
        $this->save();
        
        return $this->cancellation_token;
    }
}
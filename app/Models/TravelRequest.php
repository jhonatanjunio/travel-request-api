<?php

namespace App\Models;

use App\Enums\TravelRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'status' => TravelRequestStatus::class,
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

    public function canCancel(): bool
    {
        return $this->status === TravelRequestStatus::Requested;
    }
}

<?php

namespace App\Repositories;

use App\DTOs\TravelRequestFilterDTO;
use App\Models\TravelRequest;
use App\Repositories\Interfaces\TravelRequestInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\TravelRequest\TravelRequestNotFoundException;
use Illuminate\Support\Collection;
use App\Exceptions\UnauthorizedActionException;

class TravelRequestRepository implements TravelRequestInterface
{
    protected const CACHE_PREFIX = 'travel_requests_';
    protected array $cacheKeys = [];    

    public function __construct()
    {
        $this->cacheKeys = [];
    }

    public function getAllWithFilters(TravelRequestFilterDTO $filters): LengthAwarePaginator
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();

        $filterHash = md5(serialize($filters));
        $cacheKey = self::CACHE_PREFIX . "list_user_{$userId}_admin_{$isAdmin}_filters_{$filterHash}";
        $this->cacheKeys[] = $cacheKey;

        return Cache::remember($cacheKey, env('CACHE_TTL', 600), function () use ($filters, $isAdmin) {
            $query = TravelRequest::query();

            if (!$isAdmin) {
                $query->where('user_id', Auth::id());
            }

            if ($filters->status) {
                $query->where('status', $filters->status);
            }

            if ($filters->destination) {
                $query->where('destination', 'like', "%{$filters->destination}%");
            }

            if ($filters->startDate && $filters->endDate) {
                $query->whereBetween('created_at', [$filters->startDate, $filters->endDate]);
            }

            if ($filters->departureDateStart && $filters->departureDateEnd) {
                $query->whereBetween('departure_date', [$filters->departureDateStart, $filters->departureDateEnd]);
            }

            return $query->latest()->paginate($filters->perPage ?? 15);
        });
    }

    public function create(array $data): TravelRequest
    {        
        $travelRequest = TravelRequest::create($data);
        $this->invalidateCache();
        return $travelRequest;
    }

    public function findById(int $id): TravelRequest
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->isAdmin();

        $cacheKey = self::CACHE_PREFIX . "id_{$id}_user_{$userId}_admin_{$isAdmin}";
        $this->cacheKeys[] = $cacheKey;

        $exists = TravelRequest::where('id', $id)->exists();
        if (!$exists) {
            throw new TravelRequestNotFoundException($id);
        }

        $travelRequest = Cache::remember($cacheKey, env('CACHE_TTL', 600), function () use ($id, $userId, $isAdmin) {
            $query = TravelRequest::query();
            
            if (!$isAdmin) {
                $query->where('user_id', $userId);
            }
            
            return $query->find($id);
        });

        if (!$travelRequest) {
            throw new UnauthorizedActionException('Você não tem permissão para acessar esta solicitação');
        }

        return $travelRequest;
    }

    public function update(int $id, array $data): TravelRequest
    {
        $travelRequest = $this->findById($id);
        $travelRequest->update($data);

        Cache::forget(self::CACHE_PREFIX . "id_{$id}");
        $this->invalidateCache();

        return $travelRequest->fresh();
    }



    public function findByStatus(string $status): Collection
    {
        $query = TravelRequest::where('status', $status);

        if($status === TravelRequest::STATUS_PENDING_CANCELLATION) {
            $query->where('cancellation_confirmed_at', null);
        }
        
        return $query->with('user')->get();
    }
    
    public function countUserCancellations(int $userId): int
    {
        return TravelRequest::where('user_id', $userId)
            ->where('status', TravelRequest::STATUS_CANCELED)
            ->count();
    }
    
    public function countUserPendingCancellations(int $userId): int
    {
        return TravelRequest::where('user_id', $userId)
            ->whereIn('status', [
                TravelRequest::STATUS_PENDING_CANCELLATION,
                TravelRequest::STATUS_AWAITING_CANCELLATION_CONFIRMATION
            ])
            ->count();
    }

    protected function invalidateCache(): void
    {
        foreach ($this->cacheKeys as $key) {
            Cache::forget($key);
        }

        $this->cacheKeys = [];
    }
}

<?php

namespace App\Repositories;

use App\DTOs\TravelRequestFilterDTO;
use App\Exceptions\TravelRequest\TravelRequestNotFoundException;
use App\Models\TravelRequest;
use App\Repositories\Interfaces\TravelRequestInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class TravelRequestRepository implements TravelRequestInterface
{
    protected const CACHE_PREFIX = 'travel_requests_';

    public function __construct(
        protected CacheRepository $cache,
    ) {}

    public function getAllWithFilters(TravelRequestFilterDTO $filters, int $userId, bool $isAdmin): LengthAwarePaginator
    {
        $filterHash = md5(serialize($filters));
        $cacheKey = self::CACHE_PREFIX . "list_user_{$userId}_admin_" . ($isAdmin ? '1' : '0') . "_f_{$filterHash}";

        return $this->cache->remember($cacheKey, $this->getCacheTtl(), function () use ($filters, $userId, $isAdmin) {
            $query = TravelRequest::query()->with('user');

            if (!$isAdmin) {
                $query->where('user_id', $userId);
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
        $this->invalidateListCache();

        return $travelRequest;
    }

    public function findById(int $id): TravelRequest
    {
        $travelRequest = TravelRequest::find($id);

        if (!$travelRequest) {
            throw new TravelRequestNotFoundException($id);
        }

        return $travelRequest;
    }

    public function update(TravelRequest $travelRequest, array $data): TravelRequest
    {
        $travelRequest->update($data);
        $this->invalidateListCache();

        return $travelRequest->fresh();
    }

    protected function getCacheTtl(): int
    {
        return (int) config('cache.ttl', 600);
    }

    protected function invalidateListCache(): void
    {
        $this->cache->flush();
    }
}

<?php

namespace App\Repositories\Interfaces;

use App\DTOs\TravelRequestFilterDTO;
use App\Models\TravelRequest;
use Illuminate\Pagination\LengthAwarePaginator;

interface TravelRequestInterface
{
    public function create(array $data): TravelRequest;

    public function update(TravelRequest $travelRequest, array $data): TravelRequest;

    public function findById(int $id): TravelRequest;

    public function getAllWithFilters(TravelRequestFilterDTO $filters, int $userId, bool $isAdmin): LengthAwarePaginator;
}

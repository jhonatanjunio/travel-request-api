<?php

namespace App\Repositories\Interfaces;

use App\DTOs\TravelRequestFilterDTO;
use App\Models\TravelRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exceptions\TravelRequest\TravelRequestNotFoundException;

interface TravelRequestInterface
{
    public function create(array $data): TravelRequest;
    public function update(int $id, array $data): TravelRequest;
    public function findById(int $id): TravelRequest | TravelRequestNotFoundException;
    public function getAllWithFilters(TravelRequestFilterDTO $filters): LengthAwarePaginator;
}

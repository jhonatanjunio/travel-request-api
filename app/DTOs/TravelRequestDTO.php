<?php

namespace App\DTOs;

class TravelRequestDTO
{
    public function __construct(
        public readonly ?string $requesterName,
        public readonly string $destination,
        public readonly string $departureDate,
        public readonly string $returnDate
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            requesterName: $data['requester_name'] ?? null,
            destination: $data['destination'],
            departureDate: $data['departure_date'],
            returnDate: $data['return_date']
        );
    }
}
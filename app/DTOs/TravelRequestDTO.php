<?php

namespace App\DTOs;

class TravelRequestDTO
{
    public function __construct(
        public readonly string $destination,
        public readonly string $departureDate,
        public readonly string $returnDate,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            destination: $data['destination'],
            departureDate: $data['departure_date'],
            returnDate: $data['return_date'],
        );
    }
}

<?php

namespace App\DTOs;

class TravelRequestFilterDTO
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly ?string $destination = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $departureDateStart = null,
        public readonly ?string $departureDateEnd = null,
        public readonly ?int $perPage = 15
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            status: $data['status'] ?? null,
            destination: $data['destination'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            departureDateStart: $data['departure_date_start'] ?? null,
            departureDateEnd: $data['departure_date_end'] ?? null,
            perPage: $data['per_page'] ?? 15
        );
    }
}

<?php

namespace App\Integration\DTO;

/**
 * DTO para representação de Rating da API TVMaze.
 * Equivalente ao RatingDTO.java do projeto Spring Boot.
 */
class RatingDTO
{
    public function __construct(
        public readonly ?float $average = null,
    ) {}

    public static function fromArray(?array $data): self
    {
        return new self(
            average: $data['average'] ?? null,
        );
    }
}
